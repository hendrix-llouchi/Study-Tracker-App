import { defineStore } from 'pinia'

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
    heatmapData: []
  }),

  actions: {
    async fetchDashboardData() {
      // Bypass API call - always use mock data
      this.loadMockData()
      return {
        stats: this.stats,
        upcomingClasses: this.upcomingClasses,
        recentActivities: this.recentActivities,
        studyStreak: this.studyStreak,
        weeklyProgress: this.weeklyProgress
      }
    },

    loadMockData() {
      this.stats = {
        overallPerformance: 85,
        coursesEnrolled: 4,
        hoursStudied: 156,
        completionRate: 78,
        assignmentsDue: 2
      }
      this.upcomingClasses = [
        {
          id: 'cls_001',
          courseName: 'Data Structures & Algorithms',
          instructor: 'Dr. Sarah Johnson',
          time: '10:00 - 11:30',
          date: '2024-12-23',
          location: 'Room 301',
          isLive: false
        }
      ]
      this.studyStreak = {
        current: 5,
        longest: 12,
        days: [
          { day: 'Mon', active: true, completed: true },
          { day: 'Tue', active: true, completed: true },
          { day: 'Wed', active: true, completed: true },
          { day: 'Thu', active: true, completed: true },
          { day: 'Fri', active: true, completed: true },
          { day: 'Sat', active: false, completed: false },
          { day: 'Sun', active: false, completed: false }
        ]
      }
      this.weeklyProgress = [
        { week: 'Week 1', completed: 12, planned: 15, missed: 3 },
        { week: 'Week 2', completed: 14, planned: 16, missed: 2 }
      ]
      this.gpaTrend = [
        { date: 'Jan', gpa: 3.2 },
        { date: 'Feb', gpa: 3.4 },
        { date: 'Mar', gpa: 3.5 },
        { date: 'Apr', gpa: 3.6 },
        { date: 'May', gpa: 3.7 },
        { date: 'Jun', gpa: 3.8 }
      ]
      this.subjectPerformance = [
        { subject: 'Data Structures', gpa: 3.8, score: 92, hours: 45 },
        { subject: 'Database Systems', gpa: 3.6, score: 88, hours: 38 },
        { subject: 'Computer Networks', gpa: 3.5, score: 85, hours: 42 },
        { subject: 'Software Engineering', gpa: 3.9, score: 94, hours: 40 }
      ]
      this.plannedVsCompleted = [
        { week: 'Week 1', planned: 20, completed: 18 },
        { week: 'Week 2', planned: 22, completed: 20 },
        { week: 'Week 3', planned: 18, completed: 15 },
        { week: 'Week 4', planned: 24, completed: 22 },
        { week: 'Week 5', planned: 20, completed: 19 }
      ]
    },

    updateFilters(newFilters) {
      this.filters = { ...this.filters, ...newFilters }
      // Trigger data refetch with filters (would call API in real implementation)
      this.fetchDashboardData()
    },

    refreshStats() {
      return this.fetchDashboardData()
    }
  }
})

