import { defineStore } from 'pinia'
import api from '@/services/api'

export const usePlanningStore = defineStore('planning', {
  state: () => ({
    plans: [],
    selectedDate: new Date().toISOString().split('T')[0],
    viewMode: 'day', // 'day' or 'week'
    statistics: {
      completionRate: 0,
      plannedHours: 0,
      actualHours: 0
    },
    loading: false,
    error: null
  }),

  getters: {
    dailyPlans: (state) => {
      return state.plans.filter(plan => plan.date === state.selectedDate)
    },

    weeklyPlans: (state) => {
      const startDate = new Date(state.selectedDate)
      const day = startDate.getDay()
      const diff = startDate.getDate() - day
      const weekStart = new Date(startDate.setDate(diff))
      weekStart.setHours(0, 0, 0, 0)
      
      const weekEnd = new Date(weekStart)
      weekEnd.setDate(weekEnd.getDate() + 6)
      weekEnd.setHours(23, 59, 59, 999)
      
      return state.plans.filter(plan => {
        const planDate = new Date(plan.date)
        return planDate >= weekStart && planDate <= weekEnd
      })
    },

    completedPlans: (state) => {
      return state.plans.filter(plan => plan.completed === true)
    },

    incompletePlans: (state) => {
      return state.plans.filter(plan => plan.completed === false && new Date(plan.date) < new Date())
    }
  },

  actions: {
    /**
     * Transform backend plan data to frontend format
     */
    transformPlan(plan) {
      return {
        id: plan.id,
        date: plan.date,
        courseId: plan.course_id,
        course: plan.course?.name || 'Unknown Course',
        topic: plan.topic,
        description: plan.description,
        startTime: plan.start_time,
        duration: plan.planned_duration,
        actualTime: plan.actual_duration,
        priority: plan.priority,
        studyType: plan.study_type,
        completed: plan.status === 'completed',
        status: plan.status,
        completedAt: plan.completed_at,
        notes: plan.notes,
        createdAt: plan.created_at
      }
    },

    /**
     * Transform frontend plan data to backend format
     */
    transformPlanForBackend(planData) {
      const backendData = {
        topic: planData.topic,
        description: planData.description || null,
        date: planData.date,
        start_time: planData.startTime,
        planned_duration: planData.duration || planData.planned_duration,
        priority: planData.priority,
        study_type: planData.studyType || planData.study_type
      }

      if (planData.courseId) {
        backendData.course_id = planData.courseId
      }

      return backendData
    },

    async fetchPlans(date) {
      this.loading = true
      this.error = null
      
      if (date) {
        this.selectedDate = date
      }
      
      try {
        const response = await api.get(`/planning/plans?date=${this.selectedDate}`)
        
        if (response.success && response.data?.plans) {
          this.plans = response.data.plans.map(plan => this.transformPlan(plan))
          this.calculateStatistics()
        }
        
        return this.plans
      } catch (error) {
        console.error('Failed to fetch plans:', error)
        this.error = error.message || 'Failed to load study plans'
        this.plans = []
        throw error
      } finally {
        this.loading = false
      }
    },

    async addPlan(planData) {
      this.loading = true
      this.error = null
      
      try {
        const backendData = this.transformPlanForBackend(planData)
        const response = await api.post('/planning/plans', backendData)
        
        if (response.success && response.data?.plan) {
          const transformedPlan = this.transformPlan(response.data.plan)
          this.plans.push(transformedPlan)
          this.calculateStatistics()
          return transformedPlan
        }
      } catch (error) {
        console.error('Failed to create plan:', error)
        this.error = error.message || 'Failed to create study plan'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updatePlan(id, planData) {
      this.loading = true
      this.error = null
      
      try {
        const backendData = this.transformPlanForBackend(planData)
        const response = await api.put(`/planning/plans/${id}`, backendData)
        
        if (response.success && response.data?.plan) {
          const transformedPlan = this.transformPlan(response.data.plan)
          const index = this.plans.findIndex(p => p.id === id)
          if (index !== -1) {
            this.plans[index] = transformedPlan
            this.calculateStatistics()
            return transformedPlan
          }
        }
      } catch (error) {
        console.error('Failed to update plan:', error)
        this.error = error.message || 'Failed to update study plan'
        throw error
      } finally {
        this.loading = false
      }
    },

    async deletePlan(id) {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.delete(`/planning/plans/${id}`)
        
        if (response.success) {
          this.plans = this.plans.filter(p => p.id !== id)
          this.calculateStatistics()
          return true
        }
      } catch (error) {
        console.error('Failed to delete plan:', error)
        this.error = error.message || 'Failed to delete study plan'
        throw error
      } finally {
        this.loading = false
      }
    },

    async markComplete(id, actualTime = null) {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.patch(`/planning/plans/${id}/complete`, {
          actual_duration: actualTime
        })
        
        if (response.success && response.data?.plan) {
          const transformedPlan = this.transformPlan(response.data.plan)
          const index = this.plans.findIndex(p => p.id === id)
          if (index !== -1) {
            this.plans[index] = transformedPlan
            this.calculateStatistics()
            return transformedPlan
          }
        }
      } catch (error) {
        console.error('Failed to mark plan as complete:', error)
        this.error = error.message || 'Failed to mark plan as complete'
        throw error
      } finally {
        this.loading = false
      }
    },

    async carryOverPlans(fromDate, toDate) {
      // Get incomplete plans from the fromDate
      const incomplete = this.plans.filter(
        p => p.date === fromDate && !p.completed
      )
      
      // Update each plan's date
      for (const plan of incomplete) {
        try {
          await this.updatePlan(plan.id, {
            ...plan,
            date: toDate,
            startTime: plan.startTime,
            duration: plan.duration
          })
        } catch (error) {
          console.error(`Failed to carry over plan ${plan.id}:`, error)
        }
      }
      
      // Refresh plans for the new date
      await this.fetchPlans(toDate)
      
      return incomplete.length
    },

    async checkConflicts(date, startTime, duration) {
      try {
        const response = await api.post('/planning/plans/check-conflicts', {
          date,
          start_time: startTime,
          duration
        })
        
        if (response.success && response.data) {
          return response.data
        }
        return { has_conflicts: false, conflicts: [] }
      } catch (error) {
        console.error('Failed to check conflicts:', error)
        return { has_conflicts: false, conflicts: [] }
      }
    },

    calculateStatistics() {
      const today = new Date().toISOString().split('T')[0]
      const todayPlans = this.plans.filter(p => p.date === today)
      
      if (todayPlans.length === 0) {
        this.statistics = {
          completionRate: 0,
          plannedHours: 0,
          actualHours: 0
        }
        return
      }
      
      const completed = todayPlans.filter(p => p.completed).length
      const plannedHours = todayPlans.reduce((sum, p) => sum + (p.duration || 0), 0) / 60
      const actualHours = todayPlans
        .filter(p => p.completed && p.actualTime)
        .reduce((sum, p) => sum + (p.actualTime || 0), 0) / 60
      
      this.statistics = {
        completionRate: Math.round((completed / todayPlans.length) * 100),
        plannedHours: Math.round(plannedHours * 10) / 10,
        actualHours: Math.round(actualHours * 10) / 10
      }
    }
  }
})

