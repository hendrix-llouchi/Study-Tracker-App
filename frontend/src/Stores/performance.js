import { defineStore } from 'pinia'
import api from '@/services/api'
import { getErrorMessage, formatErrorForLog } from '@/utils/errorHandler'

export const usePerformanceStore = defineStore('performance', {
  state: () => ({
    results: [],
    gpaTrend: [],
    subjectPerformance: [],
    filters: {
      semester: 'all',
      dateRange: null
    },
    loading: false,
    error: null
  }),

  getters: {
    currentGPA: (state) => {
      if (state.results.length === 0) return 0
      
      // Calculate GPA from results
      let totalPoints = 0
      let totalCredits = 0
      
      state.results.forEach(result => {
        const credits = result.creditHours || result.course?.credits || 3
        const percentage = result.percentage || (result.score && result.maxScore ? (result.score / result.maxScore) * 100 : 0)
        const gpaPoints = (percentage / 20) * credits // Convert percentage to 4.0 scale
        totalPoints += gpaPoints
        totalCredits += credits
      })
      
      return totalCredits > 0 ? round(totalPoints / totalCredits, 2) : 0
    }
  },

  actions: {
    /**
     * Transform backend result data to frontend format
     */
    transformResult(result) {
      return {
        id: result.id,
        courseId: result.course_id,
        course: result.course?.name || 'Unknown Course',
        score: parseFloat(result.score) || 0,
        maxScore: parseFloat(result.max_score) || 100,
        percentage: parseFloat(result.percentage) || 0,
        grade: result.grade,
        creditHours: result.course?.credits || 3,
        semester: result.semester,
        assessmentType: result.assessment_type,
        assessmentName: result.assessment_name,
        weight: result.weight,
        date: result.date,
        notes: result.notes,
        createdAt: result.created_at
      }
    },

    /**
     * Transform frontend result data to backend format
     */
    transformResultForBackend(resultData) {
      return {
        course_id: resultData.courseId || resultData.course_id,
        assessment_type: resultData.assessmentType || resultData.assessment_type,
        assessment_name: resultData.assessmentName || resultData.assessment_name || null,
        score: parseFloat(resultData.score),
        max_score: parseFloat(resultData.maxScore || resultData.max_score),
        grade: resultData.grade || null,
        weight: resultData.weight || null,
        semester: resultData.semester,
        date: resultData.date,
        notes: resultData.notes || null
      }
    },

    async fetchResults() {
      this.loading = true
      this.error = null
      
      try {
        const params = new URLSearchParams()
        
        if (this.filters.semester && this.filters.semester !== 'all') {
          params.append('semester', this.filters.semester)
        }
        
        if (this.filters.dateRange) {
          if (this.filters.dateRange.from) {
            params.append('from_date', this.filters.dateRange.from)
          }
          if (this.filters.dateRange.to) {
            params.append('to_date', this.filters.dateRange.to)
          }
        }
        
        const queryString = params.toString()
        const url = `/performance/results${queryString ? `?${queryString}` : ''}`
        const response = await api.get(url)
        
        if (response.success && response.data?.results) {
          this.results = response.data.results.map(result => this.transformResult(result))
        }
        
        // Fetch GPA trend and subject performance
        await Promise.all([
          this.fetchGPATrend(),
          this.fetchSubjectPerformance()
        ])
        
        return {
          results: this.results,
          gpaTrend: this.gpaTrend,
          subjectPerformance: this.subjectPerformance
        }
      } catch (error) {
        const errorMessage = getErrorMessage(error)
        console.error(formatErrorForLog('Failed to fetch results', error))
        this.error = errorMessage
        this.results = []
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchGPATrend(period = 'all') {
      try {
        const response = await api.get(`/performance/gpa-trend?period=${period}`)
        
        if (response.success && response.data?.trend) {
          this.gpaTrend = response.data.trend.map(item => ({
            period: item.period,
            gpa: item.gpa || 0,
            credits: item.credits || 0
          }))
        }
      } catch (error) {
        console.error(formatErrorForLog('Failed to fetch GPA trend', error))
        this.gpaTrend = []
      }
    },

    async fetchSubjectPerformance(semester = null) {
      try {
        const params = semester ? `?semester=${semester}` : ''
        const response = await api.get(`/performance/subjects${params}`)
        
        if (response.success && response.data?.subjects) {
          this.subjectPerformance = response.data.subjects.map(subject => ({
            courseId: subject.course_id,
            courseName: subject.course_name,
            averageScore: subject.average_score || 0,
            totalAssessments: subject.total_assessments || 0,
            trend: subject.trend || 'stable'
          }))
        }
      } catch (error) {
        console.error(formatErrorForLog('Failed to fetch subject performance', error))
        this.subjectPerformance = []
      }
    },

    async addResult(data) {
      this.loading = true
      this.error = null
      
      try {
        const backendData = this.transformResultForBackend(data)
        const response = await api.post('/performance/results', backendData)
        
        if (response.success && response.data?.result) {
          const transformedResult = this.transformResult(response.data.result)
          this.results.push(transformedResult)
          // Refresh GPA trend and subject performance
          await Promise.all([
            this.fetchGPATrend(),
            this.fetchSubjectPerformance()
          ])
          return transformedResult
        }
      } catch (error) {
        const errorMessage = getErrorMessage(error)
        console.error(formatErrorForLog('Failed to add result', error))
        this.error = errorMessage
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateResult(id, data) {
      this.loading = true
      this.error = null
      
      try {
        const backendData = this.transformResultForBackend(data)
        const response = await api.put(`/performance/results/${id}`, backendData)
        
        if (response.success && response.data?.result) {
          const transformedResult = this.transformResult(response.data.result)
          const index = this.results.findIndex(r => r.id === id)
          if (index !== -1) {
            this.results[index] = transformedResult
            // Refresh GPA trend and subject performance
            await Promise.all([
              this.fetchGPATrend(),
              this.fetchSubjectPerformance()
            ])
            return transformedResult
          }
        }
      } catch (error) {
        const errorMessage = getErrorMessage(error)
        console.error(formatErrorForLog('Failed to update result', error))
        this.error = errorMessage
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteResult(id) {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.delete(`/performance/results/${id}`)
        
        if (response.success) {
          this.results = this.results.filter(r => r.id !== id)
          // Refresh GPA trend and subject performance
          await Promise.all([
            this.fetchGPATrend(),
            this.fetchSubjectPerformance()
          ])
          return true
        }
      } catch (error) {
        const errorMessage = getErrorMessage(error)
        console.error(formatErrorForLog('Failed to delete result', error))
        this.error = errorMessage
        throw error
      } finally {
        this.loading = false
      }
    },

    async bulkUpload(file) {
      this.loading = true
      this.error = null
      
      try {
        const formData = new FormData()
        formData.append('file', file)
        
        const response = await api.post('/performance/results/bulk', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        
        if (response.success) {
          // Refresh results after bulk upload
          await this.fetchResults()
          return response.data
        }
      } catch (error) {
        const errorMessage = getErrorMessage(error)
        console.error(formatErrorForLog('Failed to bulk upload results', error))
        this.error = errorMessage
        throw error
      } finally {
        this.loading = false
      }
    },

    calculateGPA() {
      return this.currentGPA
    },

    async updateFilters(newFilters) {
      this.filters = { ...this.filters, ...newFilters }
      await this.fetchResults()
    }
  }
})

function round(value, decimals) {
  return Number(Math.round(value + 'e' + decimals) + 'e-' + decimals)
}

