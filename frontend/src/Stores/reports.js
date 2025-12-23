import { defineStore } from 'pinia'

export const useReportsStore = defineStore('reports', {
  state: () => ({
    reports: [],
    currentReport: null
  }),

  actions: {
    async fetchReports() {
      // Bypass API call - use mock data
      if (this.reports.length === 0) {
        this.loadMockData()
      }
      return this.reports
    },

    loadMockData() {
      const today = new Date()
      const reports = []
      
      // Generate reports for last 8 weeks
      for (let i = 8; i >= 0; i--) {
        const reportDate = new Date(today)
        reportDate.setDate(reportDate.getDate() - (i * 7))
        
        reports.push({
          id: `report_${i}`,
          week: reportDate.toISOString().split('T')[0],
          weekNumber: this.getWeekNumber(reportDate),
          totalStudyHours: {
            planned: 20 + Math.floor(Math.random() * 10),
            actual: 18 + Math.floor(Math.random() * 10)
          },
          completionRate: 70 + Math.floor(Math.random() * 20),
          mostStudiedSubject: 'Data Structures & Algorithms',
          leastStudiedSubject: 'Computer Networks',
          performanceTrend: i % 2 === 0 ? 'improving' : 'stable',
          weekOverWeekComparison: i > 0 ? {
            studyHours: (Math.random() - 0.5) * 5,
            completionRate: (Math.random() - 0.5) * 10,
            gpa: (Math.random() - 0.5) * 0.2
          } : null,
          aiSuggestions: [
            'Focus more on Computer Networks this week',
            'Your completion rate is improving, keep it up!',
            'Consider reviewing previous material for Database Systems'
          ],
          createdAt: reportDate.toISOString()
        })
      }
      
      this.reports = reports.sort((a, b) => new Date(b.week) - new Date(a.week))
    },

    getWeekNumber(date) {
      const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()))
      const dayNum = d.getUTCDay() || 7
      d.setUTCDate(d.getUTCDate() + 4 - dayNum)
      const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1))
      return Math.ceil((((d - yearStart) / 86400000) + 1) / 7)
    },

    async fetchReport(id) {
      const report = this.reports.find(r => r.id === id)
      this.currentReport = report
      return report
    }
  }
})

