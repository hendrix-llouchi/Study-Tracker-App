import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAssignmentsStore = defineStore('assignments', {
  state: () => ({
    assignments: [],
    filters: {
      course: null,
      status: null,
      dueDate: null
    },
    loading: false,
    error: null
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
    /**
     * Transform backend assignment data to frontend format
     */
    transformAssignment(assignment) {
      const dueDate = assignment.due_date || assignment.dueDate
      const dueDateStr = dueDate instanceof Date 
        ? dueDate.toISOString().split('T')[0] 
        : (typeof dueDate === 'string' ? dueDate.split('T')[0] : dueDate)
      
      return {
        id: assignment.id,
        courseId: assignment.course_id,
        course: assignment.course?.name || 'Unknown Course',
        title: assignment.title,
        description: assignment.description,
        dueDate: dueDateStr,
        dueTime: assignment.due_date ? (new Date(assignment.due_date).toTimeString().slice(0, 5)) : null,
        priority: assignment.priority || 'medium',
        status: assignment.status || 'pending',
        completed: assignment.status === 'completed',
        completedAt: assignment.completed_at,
        daysUntilDue: assignment.days_until_due,
        notes: assignment.notes,
        reminderSent: assignment.reminder_sent,
        createdAt: assignment.created_at
      }
    },

    /**
     * Transform frontend assignment data to backend format
     */
    transformAssignmentForBackend(assignmentData) {
      const backendData = {
        course_id: assignmentData.courseId || assignmentData.course_id,
        title: assignmentData.title,
        description: assignmentData.description || null,
        priority: assignmentData.priority || 'medium',
        notes: assignmentData.notes || null
      }

      // Handle due_date
      if (assignmentData.dueDate) {
        if (assignmentData.dueTime) {
          backendData.due_date = `${assignmentData.dueDate} ${assignmentData.dueTime}:00`
        } else {
          backendData.due_date = `${assignmentData.dueDate} 23:59:59`
        }
      }

      // Handle status
      if (assignmentData.status) {
        backendData.status = assignmentData.status
      } else if (assignmentData.completed !== undefined) {
        backendData.status = assignmentData.completed ? 'completed' : 'pending'
      }

      return backendData
    },

    async fetchAssignments() {
      this.loading = true
      this.error = null
      
      try {
        const params = new URLSearchParams()
        
        if (this.filters.status) {
          params.append('status', this.filters.status)
        }
        
        if (this.filters.course) {
          params.append('course_id', this.filters.course)
        }
        
        if (this.filters.status === 'upcoming') {
          params.append('upcoming', 'true')
        }
        
        const queryString = params.toString()
        const url = `/assignments${queryString ? `?${queryString}` : ''}`
        const response = await api.get(url)
        
        if (response.success && response.data?.assignments) {
          this.assignments = response.data.assignments.map(assignment => 
            this.transformAssignment(assignment)
          )
        }
        
        return this.assignments
      } catch (error) {
        console.error('Failed to fetch assignments:', error)
        this.error = error.message || 'Failed to load assignments'
        this.assignments = []
        throw error
      } finally {
        this.loading = false
      }
    },

    async addAssignment(assignmentData) {
      this.loading = true
      this.error = null
      
      try {
        const backendData = this.transformAssignmentForBackend(assignmentData)
        const response = await api.post('/assignments', backendData)
        
        if (response.success && response.data?.assignment) {
          const transformedAssignment = this.transformAssignment(response.data.assignment)
          this.assignments.push(transformedAssignment)
          return transformedAssignment
        }
      } catch (error) {
        console.error('Failed to add assignment:', error)
        this.error = error.message || 'Failed to create assignment'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateAssignment(id, assignmentData) {
      this.loading = true
      this.error = null
      
      try {
        const backendData = this.transformAssignmentForBackend(assignmentData)
        const response = await api.put(`/assignments/${id}`, backendData)
        
        if (response.success && response.data?.assignment) {
          const transformedAssignment = this.transformAssignment(response.data.assignment)
          const index = this.assignments.findIndex(a => a.id === id)
          if (index !== -1) {
            this.assignments[index] = transformedAssignment
            return transformedAssignment
          }
        }
      } catch (error) {
        console.error('Failed to update assignment:', error)
        this.error = error.message || 'Failed to update assignment'
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteAssignment(id) {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.delete(`/assignments/${id}`)
        
        if (response.success) {
          this.assignments = this.assignments.filter(a => a.id !== id)
          return true
        }
      } catch (error) {
        console.error('Failed to delete assignment:', error)
        this.error = error.message || 'Failed to delete assignment'
        throw error
      } finally {
        this.loading = false
      }
    },

    async toggleComplete(id) {
      this.loading = true
      this.error = null
      
      try {
        const assignment = this.assignments.find(a => a.id === id)
        if (!assignment) {
          throw new Error('Assignment not found')
        }
        
        const response = await api.patch(`/assignments/${id}/complete`)
        
        if (response.success && response.data?.assignment) {
          const transformedAssignment = this.transformAssignment(response.data.assignment)
          const index = this.assignments.findIndex(a => a.id === id)
          if (index !== -1) {
            this.assignments[index] = transformedAssignment
            return transformedAssignment
          }
        }
      } catch (error) {
        console.error('Failed to toggle assignment completion:', error)
        this.error = error.message || 'Failed to update assignment status'
        throw error
      } finally {
        this.loading = false
      }
    },

    updateFilters(newFilters) {
      this.filters = { ...this.filters, ...newFilters }
      // Optionally refetch with new filters
      // this.fetchAssignments()
    }
  }
})

