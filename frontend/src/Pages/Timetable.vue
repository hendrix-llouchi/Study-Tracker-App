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
          :existing-classes="semesterClasses"
          :loading="saving"
          @submit="handleSaveClass"
          @cancel="handleCancelForm"
        />
      </BaseModal>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/Stores/auth'
import { useTimetableStore } from '@/Stores/timetable'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Button from '@/Components/Common/Button.vue'
import Select from '@/Components/Common/Select.vue'
import BaseModal from '@/Components/Modals/BaseModal.vue'
import WeeklyGridView from '@/Components/Timetable/WeeklyGridView.vue'
import ClassEntryForm from '@/Components/Timetable/ClassEntryForm.vue'
import ConflictWarning from '@/Components/Timetable/ConflictWarning.vue'
import StudySlotSuggestions from '@/Components/Timetable/StudySlotSuggestions.vue'
import { Plus } from 'lucide-vue-next'

const authStore = useAuthStore()
const timetableStore = useTimetableStore()

const user = computed(() => authStore.user)

const currentSemester = ref(timetableStore.currentSemester)
const showClassForm = ref(false)
const editingClass = ref(null)
const saving = ref(false)

const semesterOptions = computed(() => {
  return timetableStore.semesters.map(s => ({ value: s, label: s }))
})

const semesterClasses = computed(() => {
  return timetableStore.classes.filter(c => c.semester === currentSemester.value)
})

const conflicts = computed(() => timetableStore.conflictingClasses)
const studySlots = computed(() => timetableStore.studySlots)

onMounted(async () => {
  await timetableStore.fetchClasses(currentSemester.value)
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
  } finally {
    saving.value = false
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
