import { defineStore } from 'pinia'
import api from '@/services/api'
import { getErrorMessage, formatErrorForLog } from '@/utils/errorHandler'

export const useReportsStore = defineStore('reports', {
  state: () => ({
    reports: [],
    currentReport: null,
    loading: false,
    error: null
  }),

  actions: {
    /**
     * Get start of week date (Monday)
     */
    getWeekStart(date = new Date()) {
      const d = new Date(date)
      const day = d.getDay()
      const diff = d.getDate() - day + (day === 0 ? -6 : 1) // Adjust when day is Sunday
      return new Date(d.setDate(diff)).toISOString().split('T')[0]
    },

    /**
     * Transform backend report data to frontend format
     */
    transformReport(report) {
      return {
        id: report.id,
        week: report.week_start_date,
        weekStart: report.week_start_date,
        weekEnd: report.week_end_date,
        weekNumber: this.getWeekNumber(new Date(report.week_start_date)),
        totalStudyHours: {
          planned: report.planned_hours || 0,
          actual: report.total_study_hours || 0
        },
        completionRate: report.completion_rate || 0,
        mostStudiedSubject: report.most_studied_course?.name || null,
        leastStudiedSubject: report.least_studied_course?.name || null,
        performanceTrend: report.performance_trend || 'stable',
        dailyBreakdown: report.daily_breakdown || [],
        aiInsights: report.ai_insights || [],
        aiSuggestions: report.ai_insights ? [report.ai_insights] : [],
        generatedAt: report.generated_at,
        createdAt: report.generated_at
      }
    },

    getWeekNumber(date) {
      const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()))
      const dayNum = d.getUTCDay() || 7
      d.setUTCDate(d.getUTCDate() + 4 - dayNum)
      const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1))
      return Math.ceil((((d - yearStart) / 86400000) + 1) / 7)
    },

    async fetchReport(weekStart = null) {
      this.loading = true
      this.error = null
      
      try {
        const weekStartDate = weekStart || this.getWeekStart()
        const response = await api.get(`/progress/weekly?week_start=${weekStartDate}`)
        
        if (response.success && response.data?.report) {
          const transformedReport = this.transformReport(response.data.report)
          this.currentReport = transformedReport
          
          // Add to reports list if not already there
          const existingIndex = this.reports.findIndex(r => r.id === transformedReport.id)
          if (existingIndex !== -1) {
            this.reports[existingIndex] = transformedReport
          } else {
            this.reports.push(transformedReport)
            this.reports.sort((a, b) => new Date(b.week) - new Date(a.week))
          }
          
          return transformedReport
        }
      } catch (error) {
        const errorMessage = getErrorMessage(error)
        console.error(formatErrorForLog('Failed to fetch weekly report', error))
        this.error = errorMessage
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchReports(weeks = 8) {
      this.loading = true
      this.error = null
      
      try {
        const today = new Date()
        const reports = []
        
        // Fetch reports for the last N weeks
        for (let i = 0; i < weeks; i++) {
          const weekDate = new Date(today)
          weekDate.setDate(weekDate.getDate() - (i * 7))
          const weekStart = this.getWeekStart(weekDate)
          
          try {
            const response = await api.get(`/progress/weekly?week_start=${weekStart}`)
            if (response.success && response.data?.report) {
              const transformedReport = this.transformReport(response.data.report)
              reports.push(transformedReport)
            }
          } catch (error) {
            // If a week doesn't have a report, skip it
            console.warn(formatErrorForLog(`No report found for week ${weekStart}`, error))
          }
        }
        
        this.reports = reports.sort((a, b) => new Date(b.week) - new Date(a.week))
        return this.reports
      } catch (error) {
        const errorMessage = getErrorMessage(error)
        console.error(formatErrorForLog('Failed to fetch reports', error))
        this.error = errorMessage
        throw error
      } finally {
        this.loading = false
      }
    },

    async generateReport(weekStart) {
      this.loading = true
      this.error = null
      
      try {
        const weekStartDate = weekStart || this.getWeekStart()
        const response = await api.post('/progress/weekly/generate', {
          week_start: weekStartDate
        })
        
        if (response.success && response.data?.report) {
          const transformedReport = this.transformReport(response.data.report)
          this.currentReport = transformedReport
          
          // Add to reports list
          const existingIndex = this.reports.findIndex(r => r.id === transformedReport.id)
          if (existingIndex !== -1) {
            this.reports[existingIndex] = transformedReport
          } else {
            this.reports.push(transformedReport)
            this.reports.sort((a, b) => new Date(b.week) - new Date(a.week))
          }
          
          return transformedReport
        }
      } catch (error) {
        const errorMessage = getErrorMessage(error)
        console.error(formatErrorForLog('Failed to generate weekly report', error))
        this.error = errorMessage
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchAnalytics(fromDate = null, toDate = null, groupBy = 'week') {
      this.loading = true
      this.error = null
      
      try {
        const params = new URLSearchParams()
        if (fromDate) params.append('from_date', fromDate)
        if (toDate) params.append('to_date', toDate)
        params.append('group_by', groupBy)
        
        const response = await api.get(`/progress/analytics?${params.toString()}`)
        
        if (response.success && response.data?.analytics) {
          return response.data.analytics
        }
      } catch (error) {
        const errorMessage = getErrorMessage(error)
        console.error(formatErrorForLog('Failed to fetch analytics', error))
        this.error = errorMessage
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})

