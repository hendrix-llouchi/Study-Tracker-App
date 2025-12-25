<template>
  <AppLayout :user="user" :unread-count="0">
    <div class="space-y-4 lg:space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-h1 text-text-primary mb-2">Timetable</h1>
          <p class="text-body text-text-secondary">View and manage your class schedule</p>
        </div>
        <div class="flex items-center gap-2">
          <Select
            v-model="currentSemester"
            :options="semesterOptions"
            @update:model-value="handleSemesterChange"
          />
          <Button
            variant="secondary"
            size="lg"
            @click="showUploadModal = true"
          >
            <Upload :size="20" class="mr-2" />
            Upload
          </Button>
          <Button
            variant="primary"
            size="lg"
            @click="showClassForm = true"
          >
            <Plus :size="20" class="mr-2" />
            Add Class
          </Button>
        </div>
      </div>

      <!-- Conflict Warning -->
      <ConflictWarning
        v-if="conflicts.length > 0"
        :conflicts="conflicts"
        @resolve="handleResolveConflicts"
      />

      <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 lg:gap-6">
        <!-- Weekly Grid View -->
        <div class="lg:col-span-3">
          <WeeklyGridView
            :classes="semesterClasses"
            @edit-class="handleEditClass"
          />
        </div>

        <!-- Study Slot Suggestions -->
        <div class="lg:col-span-1">
          <StudySlotSuggestions
            :slots="studySlots"
            @select-slot="handleSelectSlot"
            @refresh="refreshStudySlots"
          />
        </div>
      </div>

      <!-- Class Entry Form Modal -->
      <BaseModal
        v-model="showClassForm"
        :title="editingClass ? 'Edit Class' : 'Add New Class'"
      >
        <ClassEntryForm
          :class-item="editingClass"
          :courses="courses"
          :existing-classes="semesterClasses"
          :loading="saving"
          @submit="handleSaveClass"
          @cancel="handleCancelForm"
        />
      </BaseModal>

      <!-- Upload Modal -->
      <BaseModal
        v-model="showUploadModal"
        title="Upload Timetable"
      >
        <div class="space-y-4">
          <p class="text-body text-text-secondary">
            Upload an image or PDF of your timetable. We'll extract the schedule automatically.
          </p>
          <input
            :ref="el => fileInput = el"
            type="file"
            accept="image/*,.pdf"
            class="hidden"
            @change="handleFileUpload"
          />
          <div
            @click="fileInput?.click()"
            class="border-2 border-dashed border-border-medium rounded-lg p-8 text-center cursor-pointer hover:border-primary-green transition-colors"
          >
            <Upload :size="48" class="mx-auto mb-2 text-text-tertiary" />
            <p class="text-body text-text-primary mb-1">Click to select file</p>
            <p class="text-body-small text-text-secondary">Supports JPG, PNG, PDF (Max 10MB)</p>
          </div>
          <div v-if="uploading" class="text-center py-4">
            <p class="text-body text-text-secondary">Processing timetable...</p>
          </div>
        </div>
        <template #footer>
          <div class="flex justify-end gap-3">
            <Button variant="ghost" @click="showUploadModal = false">Cancel</Button>
          </div>
        </template>
      </BaseModal>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/Stores/auth'
import { useTimetableStore } from '@/Stores/timetable'
import api from '@/services/api'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Button from '@/Components/Common/Button.vue'
import Select from '@/Components/Common/Select.vue'
import BaseModal from '@/Components/Modals/BaseModal.vue'
import WeeklyGridView from '@/Components/Timetable/WeeklyGridView.vue'
import ClassEntryForm from '@/Components/Timetable/ClassEntryForm.vue'
import ConflictWarning from '@/Components/Timetable/ConflictWarning.vue'
import StudySlotSuggestions from '@/Components/Timetable/StudySlotSuggestions.vue'
import { Plus, Upload } from 'lucide-vue-next'

const authStore = useAuthStore()
const timetableStore = useTimetableStore()

const user = computed(() => authStore.user)

const currentSemester = ref(timetableStore.currentSemester || 'Fall 2024')
const showClassForm = ref(false)
const editingClass = ref(null)
const saving = ref(false)
const courses = ref([])
const showUploadModal = ref(false)
const uploading = ref(false)
const fileInput = ref(null)

const semesterOptions = computed(() => {
  return timetableStore.semesters.map(s => ({ value: s, label: s }))
})

const semesterClasses = computed(() => {
  return timetableStore.classes.filter(c => c.semester === currentSemester.value)
})

const conflicts = computed(() => timetableStore.conflictingClasses)
const studySlots = computed(() => timetableStore.studySlots)

const fetchCourses = async () => {
  try {
    const response = await api.get('/courses')
    if (response.success && response.data) {
      courses.value = Array.isArray(response.data) 
        ? response.data 
        : (response.data.courses || response.data.data || [])
    }
  } catch (error) {
    console.error('Failed to fetch courses:', error)
  }
}

onMounted(async () => {
  await Promise.all([
    timetableStore.fetchClasses(currentSemester.value),
    fetchCourses()
  ])
})

const handleSemesterChange = async (semester) => {
  currentSemester.value = semester
  timetableStore.currentSemester = semester
  await timetableStore.fetchClasses(semester)
}

const handleEditClass = (classItem) => {
  editingClass.value = classItem
  showClassForm.value = true
}

const handleSaveClass = async (classData) => {
  saving.value = true
  try {
    if (editingClass.value) {
      await timetableStore.updateClass(editingClass.value.id, classData)
    } else {
      await timetableStore.addClass(classData)
    }
    handleCancelForm()
    await timetableStore.fetchClasses(currentSemester.value)
  } catch (error) {
    console.error('Failed to save class:', error)
    alert(error.message || 'Failed to save class')
  } finally {
    saving.value = false
  }
}

const handleFileUpload = async (event) => {
  const file = event.target.files?.[0]
  if (!file) return
  
  if (file.size > 10 * 1024 * 1024) {
    alert('File size must be less than 10MB')
    return
  }
  
  uploading.value = true
  try {
    const result = await timetableStore.uploadTimetable(file, currentSemester.value)
    
    if (result.requires_manual_review && result.parsed_classes) {
      // Show parsed classes for review
      alert(`Timetable processed! Found ${result.parsed_classes.length} classes. Please review and confirm.`)
      // TODO: Show review modal with parsed classes
    }
    
    // Refresh timetable
    await timetableStore.fetchClasses(currentSemester.value)
    showUploadModal.value = false
  } catch (error) {
    console.error('Failed to upload timetable:', error)
    alert(error.message || 'Failed to upload timetable')
  } finally {
    uploading.value = false
    if (event.target) {
      event.target.value = ''
    }
  }
}

const handleCancelForm = () => {
  showClassForm.value = false
  editingClass.value = null
}

const handleSelectSlot = (slot) => {
  // Navigate to planning page with pre-filled time
  console.log('Selected slot:', slot)
  // This could emit an event or navigate to planning page
}

const refreshStudySlots = () => {
  timetableStore.calculateStudySlots()
}

const handleResolveConflicts = () => {
  // Show modal or navigate to conflict resolution
  alert('Conflict resolution feature - click on conflicting classes to edit them')
}
</script>
