import { defineStore } from 'pinia'

export const useTimetableStore = defineStore('timetable', {
  state: () => ({
    classes: [],
    currentSemester: 'Fall 2024',
    semesters: ['Fall 2024', 'Spring 2024', 'Fall 2023'],
    studySlots: []
  }),

  getters: {
    weeklySchedule: (state) => {
      const weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
      return weekDays.map(day => ({
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
            class1.semester === class2.semester &&
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
    async fetchClasses(semester) {
      // Bypass API call - use mock data
      if (this.classes.length === 0) {
        this.loadMockData()
      }
      
      if (semester) {
        this.currentSemester = semester
      }
      
      return this.classes.filter(c => c.semester === this.currentSemester)
    },

    loadMockData() {
      this.classes = [
        {
          id: 'class_001',
          course: 'Data Structures & Algorithms',
          code: 'CS201',
          day: 'Monday',
          startTime: '09:00',
          endTime: '10:30',
          location: 'Room 301',
          instructor: 'Dr. Sarah Johnson',
          semester: 'Fall 2024',
          color: 'orange'
        },
        {
          id: 'class_002',
          course: 'Data Structures & Algorithms',
          code: 'CS201',
          day: 'Wednesday',
          startTime: '09:00',
          endTime: '10:30',
          location: 'Room 301',
          instructor: 'Dr. Sarah Johnson',
          semester: 'Fall 2024',
          color: 'orange'
        },
        {
          id: 'class_003',
          course: 'Database Systems',
          code: 'CS301',
          day: 'Tuesday',
          startTime: '14:00',
          endTime: '15:30',
          location: 'Room 205',
          instructor: 'Prof. Michael Chen',
          semester: 'Fall 2024',
          color: 'blue'
        },
        {
          id: 'class_004',
          course: 'Database Systems',
          code: 'CS301',
          day: 'Thursday',
          startTime: '14:00',
          endTime: '15:30',
          location: 'Room 205',
          instructor: 'Prof. Michael Chen',
          semester: 'Fall 2024',
          color: 'blue'
        },
        {
          id: 'class_005',
          course: 'Computer Networks',
          code: 'CS302',
          day: 'Monday',
          startTime: '11:00',
          endTime: '12:30',
          location: 'Room 102',
          instructor: 'Dr. Emily Rodriguez',
          semester: 'Fall 2024',
          color: 'green'
        }
      ]
    },

    async addClass(classData) {
      const newClass = {
        id: `class_${Date.now()}`,
        ...classData,
        semester: this.currentSemester
      }
      this.classes.push(newClass)
      this.calculateStudySlots()
      return newClass
    },

    async updateClass(id, classData) {
      const index = this.classes.findIndex(c => c.id === id)
      if (index !== -1) {
        this.classes[index] = { ...this.classes[index], ...classData }
        this.calculateStudySlots()
        return this.classes[index]
      }
      return null
    },

    async deleteClass(id) {
      this.classes = this.classes.filter(c => c.id !== id)
      this.calculateStudySlots()
      return true
    },

    calculateStudySlots() {
      const weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']
      const slots = []
      
      weekDays.forEach(day => {
        const dayClasses = this.classes.filter(
          c => c.day === day && c.semester === this.currentSemester
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

