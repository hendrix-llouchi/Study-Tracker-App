import { defineStore } from 'pinia'
import api from '@/services/api'

const DAY_NAMES = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']

export const useTimetableStore = defineStore('timetable', {
  state: () => ({
    timetable: null,
    classes: [],
    currentSemester: null,
    semesters: [],
    studySlots: [],
    loading: false,
    error: null
  }),

  getters: {
    weeklySchedule: (state) => {
      return DAY_NAMES.map(day => ({
        day,
        classes: state.classes.filter(c => c.day === day)
      }))
    },

    conflictingClasses: (state) => {
      const conflicts = []
      state.classes.forEach((class1, i) => {
        state.classes.slice(i + 1).forEach(class2 => {
          if (
            class1.day === class2.day &&
            hasTimeConflict(class1, class2)
          ) {
            conflicts.push({ class1, class2 })
          }
        })
      })
      return conflicts
    }
  },

  actions: {
    /**
     * Map day_of_week (0-6) to day name
     */
    dayOfWeekToName(dayOfWeek) {
      return DAY_NAMES[dayOfWeek] || 'Unknown'
    },

    /**
     * Map day name to day_of_week (0-6)
     */
    dayNameToOfWeek(dayName) {
      return DAY_NAMES.indexOf(dayName)
    },

    /**
     * Transform backend class data to frontend format
     */
    transformClass(classData) {
      return {
        id: classData.id,
        courseId: classData.course_id,
        course: classData.course?.name || 'Unknown Course',
        code: classData.course?.code || '',
        day: this.dayOfWeekToName(classData.day_of_week),
        dayOfWeek: classData.day_of_week,
        startTime: classData.start_time,
        endTime: classData.end_time,
        location: classData.location || 'TBA',
        instructor: classData.instructor || 'TBA',
        classType: classData.class_type || 'lecture',
        notes: classData.notes
      }
    },

    /**
     * Transform frontend class data to backend format
     */
    transformClassForBackend(classData) {
      return {
        course_id: classData.courseId || classData.course_id,
        day_of_week: classData.dayOfWeek !== undefined 
          ? classData.dayOfWeek 
          : this.dayNameToOfWeek(classData.day || classData.dayName),
        start_time: classData.startTime || classData.start_time,
        end_time: classData.endTime || classData.end_time,
        location: classData.location || null,
        instructor: classData.instructor || null,
        class_type: classData.classType || classData.class_type || 'lecture'
      }
    },

    async fetchClasses(semester) {
      this.loading = true
      this.error = null
      
      if (semester) {
        this.currentSemester = semester
      }
      
      try {
        const params = this.currentSemester ? `?semester=${encodeURIComponent(this.currentSemester)}` : ''
        const response = await api.get(`/timetable${params}`)
        
        if (response.success && response.data?.timetable) {
          this.timetable = response.data.timetable
          this.currentSemester = this.timetable.semester
          this.classes = (this.timetable.classes || []).map(cls => this.transformClass(cls))
          this.calculateStudySlots()
        } else {
          this.timetable = null
          this.classes = []
          this.studySlots = []
        }
        
        return this.classes
      } catch (error) {
        console.error('Failed to fetch timetable:', error)
        this.error = error.message || 'Failed to load timetable'
        this.classes = []
        throw error
      } finally {
        this.loading = false
      }
    },

    async createTimetable(timetableData) {
      this.loading = true
      this.error = null
      
      try {
        const backendData = {
          semester: timetableData.semester,
          academic_year: timetableData.academicYear || timetableData.academic_year || null,
          classes: timetableData.classes.map(cls => this.transformClassForBackend(cls))
        }
        
        const response = await api.post('/timetable', backendData)
        
        if (response.success && response.data?.timetable) {
          this.timetable = response.data.timetable
          this.classes = (this.timetable.classes || []).map(cls => this.transformClass(cls))
          this.currentSemester = this.timetable.semester
          this.calculateStudySlots()
          return this.timetable
        }
      } catch (error) {
        console.error('Failed to create timetable:', error)
        this.error = error.message || 'Failed to create timetable'
        throw error
      } finally {
        this.loading = false
      }
    },

    async uploadTimetable(file, semester) {
      this.loading = true
      this.error = null
      
      try {
        const formData = new FormData()
        formData.append('file', file)
        formData.append('semester', semester)
        
        const response = await api.post('/timetable/upload', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        
        if (response.success && response.data) {
          return response.data
        }
      } catch (error) {
        console.error('Failed to upload timetable:', error)
        this.error = error.message || 'Failed to upload timetable'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateClass(id, classData) {
      this.loading = true
      this.error = null
      
      try {
        const backendData = {
          location: classData.location,
          instructor: classData.instructor,
          start_time: classData.startTime || classData.start_time,
          end_time: classData.endTime || classData.end_time
        }
        
        // Remove null/undefined values
        Object.keys(backendData).forEach(key => {
          if (backendData[key] === undefined || backendData[key] === null) {
            delete backendData[key]
          }
        })
        
        const response = await api.put(`/timetable/classes/${id}`, backendData)
        
        if (response.success && response.data?.class) {
          const transformedClass = this.transformClass(response.data.class)
          const index = this.classes.findIndex(c => c.id === id)
          if (index !== -1) {
            this.classes[index] = transformedClass
            this.calculateStudySlots()
            return transformedClass
          }
        }
      } catch (error) {
        console.error('Failed to update class:', error)
        this.error = error.message || 'Failed to update class'
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteClass(id) {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.delete(`/timetable/classes/${id}`)
        
        if (response.success) {
          this.classes = this.classes.filter(c => c.id !== id)
          this.calculateStudySlots()
          return true
        }
      } catch (error) {
        console.error('Failed to delete class:', error)
        this.error = error.message || 'Failed to delete class'
        throw error
      } finally {
        this.loading = false
      }
    },

    calculateStudySlots() {
      const weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']
      const slots = []
      
      weekDays.forEach(day => {
        const dayClasses = this.classes.filter(
          c => c.day === day
        ).sort((a, b) => a.startTime.localeCompare(b.startTime))
        
        // Find gaps between classes
        let currentTime = '08:00'
        
        dayClasses.forEach(classItem => {
          if (timeToMinutes(currentTime) < timeToMinutes(classItem.startTime)) {
            const gap = timeToMinutes(classItem.startTime) - timeToMinutes(currentTime)
            if (gap >= 60) { // At least 1 hour gap
              slots.push({
                day,
                startTime: currentTime,
                endTime: classItem.startTime,
                duration: gap,
                suggested: true
              })
            }
          }
          currentTime = classItem.endTime
        })
        
        // Check gap after last class
        if (timeToMinutes(currentTime) < timeToMinutes('18:00')) {
          const gap = timeToMinutes('18:00') - timeToMinutes(currentTime)
          if (gap >= 60) {
            slots.push({
              day,
              startTime: currentTime,
              endTime: '18:00',
              duration: gap,
              suggested: true
            })
          }
        }
      })
      
      this.studySlots = slots
    }
  }
})

function timeToMinutes(time) {
  const [hours, minutes] = time.split(':').map(Number)
  return hours * 60 + minutes
}

function hasTimeConflict(class1, class2) {
  const start1 = timeToMinutes(class1.startTime)
  const end1 = timeToMinutes(class1.endTime)
  const start2 = timeToMinutes(class2.startTime)
  const end2 = timeToMinutes(class2.endTime)
  
  return (start1 < end2 && end1 > start2)
}

