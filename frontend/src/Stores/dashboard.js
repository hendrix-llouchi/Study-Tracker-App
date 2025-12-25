import { defineStore } from 'pinia'
import { getErrorMessage, formatErrorForLog } from '@/utils/errorHandler'
import api from '@/services/api'

export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    stats: null,
    upcomingClasses: [],
    recentActivities: [],
    studyStreak: null,
    weeklyProgress: [],
    filters: {
      course: null,
      semester: null,
      period: null
    },
    gpaTrend: [],
    subjectPerformance: [],
    plannedVsCompleted: [],
    heatmapData: [],
    loading: false,
    error: null
  }),

  actions: {
    async fetchDashboardData() {
      this.loading = true
      this.error = null
      
      try {
        // Fetch all dashboard data in parallel
        const [statsResponse, classesResponse, streakResponse, activitiesResponse] = await Promise.all([
          api.get('/dashboard/stats'),
          api.get('/dashboard/upcoming-classes'),
          api.get('/dashboard/study-streak'),
          api.get('/dashboard/activities')
        ])

        // Transform and set stats
        if (statsResponse.success && statsResponse.data) {
          this.stats = {
            overallPerformance: statsResponse.data.overall_performance || 0,
            coursesEnrolled: statsResponse.data.courses_enrolled || 0,
            hoursStudied: statsResponse.data.hours_studied || 0,
            completionRate: statsResponse.data.completion_rate || 0,
            assignmentsDue: statsResponse.data.assignments_due || 0
          }
        }

        // Transform and set upcoming classes
        if (classesResponse.success && classesResponse.data?.classes) {
          this.upcomingClasses = classesResponse.data.classes.map(cls => ({
            id: cls.id,
            courseName: cls.course_name || cls.course_code || 'Unknown Course',
            instructor: cls.instructor || 'TBA',
            time: `${cls.start_time || ''} - ${cls.end_time || ''}`,
            date: cls.date,
            location: cls.location || 'TBA',
            isLive: false // Can be calculated based on current time
          }))
        }

        // Transform and set study streak
        if (streakResponse.success && streakResponse.data) {
          this.studyStreak = {
            current: streakResponse.data.current_streak || 0,
            longest: streakResponse.data.longest_streak || 0,
            days: this.transformStreakDays(streakResponse.data.days || [])
          }
        }

        // Transform and set recent activities
        if (activitiesResponse.success && activitiesResponse.data?.activities) {
          this.recentActivities = activitiesResponse.data.activities.map(activity => ({
            id: activity.id,
            type: activity.type,
            description: activity.description,
            timestamp: activity.timestamp,
            metadata: activity.metadata || {}
          }))
        }

        // Fetch chart data
        await Promise.all([
          this.fetchGPATrend(),
          this.fetchSubjectPerformance(),
          this.fetchPlannedVsCompleted(),
          this.fetchHeatmapData(),
          this.fetchWeeklyProgress()
        ])

        return {
          stats: this.stats,
          upcomingClasses: this.upcomingClasses,
          recentActivities: this.recentActivities,
          studyStreak: this.studyStreak,
          weeklyProgress: this.weeklyProgress
        }
      } catch (error) {
        const errorMessage = getErrorMessage(error)
        console.error(formatErrorForLog('Failed to fetch dashboard data', error))
        this.error = errorMessage
        // Fallback to empty data instead of mock data
        this.setEmptyData()
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchGPATrend() {
      try {
        const period = this.filters.period || 'all'
        const response = await api.get(`/performance/gpa-trend?period=${period}`)
        
        if (response.success && response.data?.trend) {
          this.gpaTrend = response.data.trend.map(item => ({
            date: this.formatPeriodLabel(item.period),
            gpa: item.gpa || 0
          }))
        }
      } catch (error) {
        console.error(formatErrorForLog('Failed to fetch GPA trend', error))
        this.gpaTrend = []
      }
    },

    async fetchSubjectPerformance() {
      try {
        const semester = this.filters.semester || null
        const params = semester ? `?semester=${semester}` : ''
        const response = await api.get(`/performance/subjects${params}`)
        
        if (response.success && response.data?.subjects) {
          // Fetch study hours from study plans for each course
          const courseHoursMap = {}
          
          try {
            // Get study plans for the last 30 days to calculate hours per course
            const toDate = new Date()
            const fromDate = new Date()
            fromDate.setDate(fromDate.getDate() - 30)
            
            const plansResponse = await api.get(`/planning/plans?from_date=${fromDate.toISOString().split('T')[0]}&to_date=${toDate.toISOString().split('T')[0]}`)
            
            if (plansResponse.success && plansResponse.data?.plans) {
              plansResponse.data.plans.forEach(plan => {
                const courseId = plan.course_id
                if (!courseHoursMap[courseId]) {
                  courseHoursMap[courseId] = 0
                }
                // Use actual duration if completed, otherwise planned duration
                const duration = plan.status === 'completed'
                  ? (plan.actual_duration || plan.planned_duration || 0)
                  : (plan.planned_duration || 0)
                courseHoursMap[courseId] += duration / 60 // Convert to hours
              })
            }
          } catch (error) {
            console.warn(formatErrorForLog('Failed to fetch study hours for subjects', error))
          }
          
          this.subjectPerformance = response.data.subjects.map(subject => ({
            subject: subject.course_name || 'Unknown',
            gpa: (subject.average_score || 0) / 20, // Convert percentage to GPA
            score: subject.average_score || 0,
            hours: Math.round((courseHoursMap[subject.course_id] || 0) * 10) / 10 // Round to 1 decimal
          }))
        }
      } catch (error) {
        console.error(formatErrorForLog('Failed to fetch subject performance', error))
        this.subjectPerformance = []
      }
    },

    async fetchPlannedVsCompleted() {
      try {
        // Fetch study plans for last 5 weeks to calculate planned vs completed
        const toDate = new Date()
        const fromDate = new Date()
        fromDate.setDate(fromDate.getDate() - 35) // ~5 weeks
        
        const response = await api.get(`/planning/plans?from_date=${fromDate.toISOString().split('T')[0]}&to_date=${toDate.toISOString().split('T')[0]}`)
        
        if (response.success && response.data?.plans) {
          const plans = response.data.plans
          
          // Group by week
          const weeklyData = {}
          
          plans.forEach(plan => {
            const planDate = new Date(plan.date)
            const weekStart = this.getWeekStart(planDate)
            const weekKey = weekStart.toISOString().split('T')[0]
            
            if (!weeklyData[weekKey]) {
              weeklyData[weekKey] = {
                week: this.formatWeekLabel(weekStart),
                planned: 0,
                completed: 0
              }
            }
            
            const hours = (plan.planned_duration || 0) / 60
            weeklyData[weekKey].planned += hours
            
            if (plan.status === 'completed') {
              weeklyData[weekKey].completed += hours
            }
          })
          
          // Convert to array and sort by week key (date)
          this.plannedVsCompleted = Object.entries(weeklyData)
            .sort(([dateA], [dateB]) => new Date(dateA) - new Date(dateB))
            .slice(-5) // Last 5 weeks
            .map(([, data]) => data)
        }
      } catch (error) {
        console.error(formatErrorForLog('Failed to fetch planned vs completed', error))
        this.plannedVsCompleted = []
      }
    },

    async fetchHeatmapData() {
      try {
        // Fetch study plans for last year to generate heatmap
        const toDate = new Date()
        const fromDate = new Date()
        fromDate.setFullYear(fromDate.getFullYear() - 1)
        
        const response = await api.get(`/planning/plans?from_date=${fromDate.toISOString().split('T')[0]}&to_date=${toDate.toISOString().split('T')[0]}`)
        
        if (response.success && response.data?.plans) {
          const plans = response.data.plans
          const heatmapMap = {}
          
          plans.forEach(plan => {
            const date = plan.date
            if (!heatmapMap[date]) {
              heatmapMap[date] = 0
            }
            
            // Use actual duration if completed, otherwise planned duration
            const duration = plan.status === 'completed' 
              ? (plan.actual_duration || plan.planned_duration || 0)
              : (plan.planned_duration || 0)
            
            heatmapMap[date] += duration / 60 // Convert to hours
          })
          
          // Convert to array format expected by heatmap component
          this.heatmapData = Object.entries(heatmapMap).map(([date, value]) => ({
            date,
            value: Math.round(value * 10) / 10 // Round to 1 decimal
          }))
        }
      } catch (error) {
        console.error(formatErrorForLog('Failed to fetch heatmap data', error))
        this.heatmapData = []
      }
    },

    async fetchWeeklyProgress() {
      try {
        // Fetch study plans for last 4 weeks to calculate weekly progress
        const toDate = new Date()
        const fromDate = new Date()
        fromDate.setDate(fromDate.getDate() - 28) // ~4 weeks
        
        const response = await api.get(`/planning/plans?from_date=${fromDate.toISOString().split('T')[0]}&to_date=${toDate.toISOString().split('T')[0]}`)
        
        if (response.success && response.data?.plans) {
          const plans = response.data.plans
          
          // Group by week
          const weeklyData = {}
          
          plans.forEach(plan => {
            const planDate = new Date(plan.date)
            const weekStart = this.getWeekStart(planDate)
            const weekKey = weekStart.toISOString().split('T')[0]
            
            if (!weeklyData[weekKey]) {
              weeklyData[weekKey] = {
                week: this.formatWeekLabel(weekStart),
                completed: 0,
                planned: 0,
                missed: 0
              }
            }
            
            const hours = (plan.planned_duration || 0) / 60
            weeklyData[weekKey].planned += hours
            
            if (plan.status === 'completed') {
              weeklyData[weekKey].completed += hours
            } else if (plan.status === 'missed' || (plan.status !== 'pending' && new Date(plan.date) < new Date())) {
              weeklyData[weekKey].missed += hours
            }
          })
          
          // Convert to array and sort by date
          this.weeklyProgress = Object.entries(weeklyData)
            .sort(([dateA], [dateB]) => new Date(dateA) - new Date(dateB))
            .slice(-4) // Last 4 weeks
            .map(([, data]) => data)
        }
      } catch (error) {
        console.error(formatErrorForLog('Failed to fetch weekly progress', error))
        this.weeklyProgress = []
      }
    },

    transformStreakDays(days) {
      // Backend returns days with 'day' (e.g., 'Mon') and 'completed' boolean
      // Frontend expects 'day', 'active', and 'completed'
      return days.map(day => ({
        day: day.day || day,
        active: day.completed || false,
        completed: day.completed || false
      }))
    },

    formatPeriodLabel(period) {
      // Format period string (e.g., "2024-01" or "Fall 2024") to short label
      if (period.includes('-')) {
        const [year, month] = period.split('-')
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
        return monthNames[parseInt(month) - 1] || period
      }
      return period.length > 10 ? period.substring(0, 10) : period
    },

    formatWeekLabel(date) {
      const now = new Date()
      const startOfYear = new Date(now.getFullYear(), 0, 1)
      const days = Math.floor((date - startOfYear) / (24 * 60 * 60 * 1000))
      const weekNumber = Math.ceil((days + startOfYear.getDay() + 1) / 7)
      return `Week ${weekNumber}`
    },

    getWeekStart(date) {
      const d = new Date(date)
      const day = d.getDay()
      const diff = d.getDate() - day + (day === 0 ? -6 : 1) // Adjust when day is Sunday
      return new Date(d.setDate(diff))
    },

    setEmptyData() {
      this.stats = {
        overallPerformance: 0,
        coursesEnrolled: 0,
        hoursStudied: 0,
        completionRate: 0,
        assignmentsDue: 0
      }
      this.upcomingClasses = []
      this.recentActivities = []
      this.studyStreak = {
        current: 0,
        longest: 0,
        days: []
      }
      this.weeklyProgress = []
      this.gpaTrend = []
      this.subjectPerformance = []
      this.plannedVsCompleted = []
      this.heatmapData = []
    },

    async updateFilters(newFilters) {
      this.filters = { ...this.filters, ...newFilters }
      // Refetch chart data that depends on filters
      await Promise.all([
        this.fetchGPATrend(),
        this.fetchSubjectPerformance()
      ])
      // Note: We don't refetch all dashboard data to avoid unnecessary API calls
      // Only chart data that depends on filters is refreshed
    },

    async refreshGPATrend(period) {
      if (period) {
        this.filters.period = period
      }
      await this.fetchGPATrend()
    },

    refreshStats() {
      return this.fetchDashboardData()
    }
  }
})

