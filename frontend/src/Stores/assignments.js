import { defineStore } from 'pinia'

export const useAssignmentsStore = defineStore('assignments', {
  state: () => ({
    assignments: [],
    filters: {
      course: null,
      status: null,
      dueDate: null
    }
  }),

  getters: {
    upcomingAssignments: (state) => {
      const today = new Date()
      today.setHours(0, 0, 0, 0)
      return state.assignments
        .filter(a => !a.completed && new Date(a.dueDate) >= today)
        .sort((a, b) => new Date(a.dueDate) - new Date(b.dueDate))
    },

    overdueAssignments: (state) => {
      const today = new Date()
      today.setHours(0, 0, 0, 0)
      return state.assignments
        .filter(a => !a.completed && new Date(a.dueDate) < today)
        .sort((a, b) => new Date(a.dueDate) - new Date(b.dueDate))
    },

    completedAssignments: (state) => {
      return state.assignments
        .filter(a => a.completed)
        .sort((a, b) => new Date(b.dueDate) - new Date(a.dueDate))
    },

    filteredAssignments: (state) => {
      let filtered = [...state.assignments]

      if (state.filters.course) {
        filtered = filtered.filter(a => a.courseId === state.filters.course)
      }

      if (state.filters.status === 'upcoming') {
        const today = new Date()
        today.setHours(0, 0, 0, 0)
        filtered = filtered.filter(a => !a.completed && new Date(a.dueDate) >= today)
      } else if (state.filters.status === 'overdue') {
        const today = new Date()
        today.setHours(0, 0, 0, 0)
        filtered = filtered.filter(a => !a.completed && new Date(a.dueDate) < today)
      } else if (state.filters.status === 'completed') {
        filtered = filtered.filter(a => a.completed)
      }

      if (state.filters.dueDate) {
        filtered = filtered.filter(a => a.dueDate === state.filters.dueDate)
      }

      return filtered.sort((a, b) => new Date(a.dueDate) - new Date(b.dueDate))
    }
  },

  actions: {
    async fetchAssignments() {
      // Bypass API call - use mock data
      if (this.assignments.length === 0) {
        this.loadMockData()
      }
      return this.assignments
    },

    loadMockData() {
      const today = new Date()
      const nextWeek = new Date(today)
      nextWeek.setDate(nextWeek.getDate() + 7)
      const lastWeek = new Date(today)
      lastWeek.setDate(lastWeek.getDate() - 7)

      this.assignments = [
        {
          id: 'assgn_001',
          title: 'Database Design Project',
          courseId: 'cs301',
          course: 'Database Systems',
          description: 'Design and implement a database schema for a library management system',
          dueDate: nextWeek.toISOString().split('T')[0],
          dueTime: '23:59',
          priority: 'high',
          completed: false,
          createdAt: today.toISOString()
        },
        {
          id: 'assgn_002',
          title: 'Algorithm Analysis Assignment',
          courseId: 'cs201',
          course: 'Data Structures & Algorithms',
          description: 'Analyze time complexity of sorting algorithms',
          dueDate: today.toISOString().split('T')[0],
          dueTime: '17:00',
          priority: 'high',
          completed: false,
          createdAt: lastWeek.toISOString()
        },
        {
          id: 'assgn_003',
          title: 'Network Protocols Quiz',
          courseId: 'cs302',
          course: 'Computer Networks',
          description: 'Online quiz on TCP/IP protocols',
          dueDate: nextWeek.toISOString().split('T')[0],
          dueTime: '14:00',
          priority: 'medium',
          completed: false,
          createdAt: today.toISOString()
        },
        {
          id: 'assgn_004',
          title: 'Software Requirements Document',
          courseId: 'cs303',
          course: 'Software Engineering',
          description: 'Write SRS document for assigned project',
          dueDate: lastWeek.toISOString().split('T')[0],
          dueTime: '23:59',
          priority: 'high',
          completed: true,
          createdAt: lastWeek.toISOString()
        }
      ]
    },

    async addAssignment(assignmentData) {
      const assignment = {
        id: `assgn_${Date.now()}`,
        ...assignmentData,
        completed: false,
        createdAt: new Date().toISOString()
      }
      this.assignments.push(assignment)
      return assignment
    },

    async updateAssignment(id, assignmentData) {
      const index = this.assignments.findIndex(a => a.id === id)
      if (index !== -1) {
        this.assignments[index] = { ...this.assignments[index], ...assignmentData }
        return this.assignments[index]
      }
      return null
    },

    async deleteAssignment(id) {
      this.assignments = this.assignments.filter(a => a.id !== id)
      return true
    },

    async toggleComplete(id) {
      const assignment = this.assignments.find(a => a.id === id)
      if (assignment) {
        assignment.completed = !assignment.completed
        return assignment
      }
      return null
    },

    updateFilters(newFilters) {
      this.filters = { ...this.filters, ...newFilters }
    }
  }
})

