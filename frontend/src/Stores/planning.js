import { defineStore } from 'pinia'

export const usePlanningStore = defineStore('planning', {
  state: () => ({
    plans: [],
    selectedDate: new Date().toISOString().split('T')[0],
    viewMode: 'day', // 'day' or 'week'
    statistics: {
      completionRate: 0,
      plannedHours: 0,
      actualHours: 0
    }
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
    async fetchPlans(date) {
      // Bypass API call - use mock data
      if (this.plans.length === 0) {
        this.loadMockData()
      }
      
      if (date) {
        this.selectedDate = date
      }
      
      return this.plans
    },

    loadMockData() {
      const today = new Date()
      const tomorrow = new Date(today)
      tomorrow.setDate(tomorrow.getDate() + 1)
      
      this.plans = [
        {
          id: 'plan_001',
          date: today.toISOString().split('T')[0],
          courseId: 'cs201',
          course: 'Data Structures & Algorithms',
          topic: 'Binary Search Trees',
          startTime: '09:00',
          duration: 120,
          priority: 'high',
          studyType: 'new-material',
          completed: true,
          actualTime: 115,
          createdAt: new Date().toISOString()
        },
        {
          id: 'plan_002',
          date: today.toISOString().split('T')[0],
          courseId: 'cs301',
          course: 'Database Systems',
          topic: 'SQL Queries Review',
          startTime: '14:00',
          duration: 90,
          priority: 'medium',
          studyType: 'review',
          completed: false,
          actualTime: null,
          createdAt: new Date().toISOString()
        },
        {
          id: 'plan_003',
          date: tomorrow.toISOString().split('T')[0],
          courseId: 'cs302',
          course: 'Computer Networks',
          topic: 'TCP/IP Protocol',
          startTime: '10:00',
          duration: 120,
          priority: 'high',
          studyType: 'new-material',
          completed: false,
          actualTime: null,
          createdAt: new Date().toISOString()
        }
      ]
      
      this.calculateStatistics()
    },

    async addPlan(planData) {
      const plan = {
        id: `plan_${Date.now()}`,
        ...planData,
        completed: false,
        actualTime: null,
        createdAt: new Date().toISOString()
      }
      this.plans.push(plan)
      this.calculateStatistics()
      return plan
    },

    async updatePlan(id, planData) {
      const index = this.plans.findIndex(p => p.id === id)
      if (index !== -1) {
        this.plans[index] = { ...this.plans[index], ...planData }
        this.calculateStatistics()
        return this.plans[index]
      }
      return null
    },

    async deletePlan(id) {
      this.plans = this.plans.filter(p => p.id !== id)
      this.calculateStatistics()
      return true
    },

    async markComplete(id, actualTime = null) {
      return this.updatePlan(id, {
        completed: true,
        actualTime: actualTime
      })
    },

    async carryOverPlans(fromDate, toDate) {
      const incomplete = this.plans.filter(
        p => p.date === fromDate && !p.completed
      )
      
      incomplete.forEach(plan => {
        plan.date = toDate
        plan.carriedOver = true
      })
      
      return incomplete.length
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

