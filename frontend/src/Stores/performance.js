import { defineStore } from 'pinia'

export const usePerformanceStore = defineStore('performance', {
  state: () => ({
    results: [],
    gpaTrend: [],
    subjectPerformance: [],
    filters: {
      semester: 'all',
      dateRange: null
    }
  }),

  getters: {
    currentGPA: (state) => {
      if (state.results.length === 0) return 0
      // Calculate GPA logic here
      return 3.5
    }
  },

  actions: {
    async fetchResults() {
      // Bypass API call - use mock data for now
      if (this.results.length === 0) {
        this.loadMockData()
      }
      return {
        results: this.results,
        gpaTrend: this.gpaTrend,
        subjectPerformance: this.subjectPerformance
      }
    },

    loadMockData() {
      this.results = [
        {
          id: 'res_001',
          course: 'Data Structures & Algorithms',
          score: 85,
          maxScore: 100,
          grade: 'B+',
          creditHours: 3,
          semester: 'Fall 2024',
          assessmentType: 'final',
          date: '2024-12-15'
        },
        {
          id: 'res_002',
          course: 'Database Systems',
          score: 92,
          maxScore: 100,
          grade: 'A',
          creditHours: 3,
          semester: 'Fall 2024',
          assessmentType: 'midterm',
          date: '2024-11-20'
        },
        {
          id: 'res_003',
          course: 'Computer Networks',
          score: 78,
          maxScore: 100,
          grade: 'C+',
          creditHours: 3,
          semester: 'Fall 2024',
          assessmentType: 'final',
          date: '2024-12-18'
        },
        {
          id: 'res_004',
          course: 'Software Engineering',
          score: 94,
          maxScore: 100,
          grade: 'A',
          creditHours: 3,
          semester: 'Fall 2024',
          assessmentType: 'project',
          date: '2024-12-10'
        }
      ]
    },

    async addResult(data) {
      // Bypass API call - just add to local state
      const result = {
        id: `res_${Date.now()}`,
        ...data,
        createdAt: new Date().toISOString()
      }
      this.results.push(result)
      return result
    },

    async updateResult(id, data) {
      // Bypass API call - just update local state
      const index = this.results.findIndex(r => r.id === id)
      if (index !== -1) {
        this.results[index] = { ...this.results[index], ...data }
        return this.results[index]
      }
      return null
    },

    async deleteResult(id) {
      // Bypass API call - just remove from local state
      this.results = this.results.filter(r => r.id !== id)
      return true
    },

    calculateGPA() {
      // GPA calculation logic
      return this.currentGPA
    },

    updateFilters(newFilters) {
      this.filters = { ...this.filters, ...newFilters }
      // In real implementation, this would trigger API call with filters
      this.fetchResults()
    }
  }
})

