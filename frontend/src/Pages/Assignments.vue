<template>
  <AppLayout :user="user" :unread-count="0">
    <div class="space-y-4 lg:space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-h1 text-text-primary mb-2">Assignments</h1>
          <p class="text-body text-text-secondary">Track your assignments and deadlines</p>
        </div>
        <Button
          variant="primary"
          size="lg"
          @click="showAssignmentModal = true"
        >
          <Plus :size="20" class="mr-2" />
          Add Assignment
        </Button>
      </div>

      <!-- Filters -->
      <AssignmentFilters
        :courses="courses"
        @filter-change="handleFilterChange"
      />

      <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 lg:gap-6">
        <!-- Upcoming Deadlines -->
        <div class="lg:col-span-1">
          <UpcomingDeadlines
            :assignments="assignments"
            @select-assignment="handleSelectAssignment"
          />
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-3">
          <div class="flex items-center gap-2 mb-4">
            <Button
              :variant="viewMode === 'list' ? 'primary' : 'ghost'"
              size="md"
              @click="viewMode = 'list'"
            >
              <List :size="16" class="mr-2" />
              List
            </Button>
            <Button
              :variant="viewMode === 'calendar' ? 'primary' : 'ghost'"
              size="md"
              @click="viewMode = 'calendar'"
            >
              <Calendar :size="16" class="mr-2" />
              Calendar
            </Button>
          </div>

          <AssignmentsList
            v-if="viewMode === 'list'"
            :assignments="filteredAssignments"
            @edit-assignment="handleEditAssignment"
            @toggle-complete="handleToggleComplete"
            @delete-assignment="handleDeleteAssignment"
          />

          <AssignmentsCalendar
            v-else
            :assignments="assignments"
            :selected-date="selectedDate"
            @date-selected="handleDateSelected"
          />
        </div>
      </div>

      <!-- Assignment Modal -->
      <AssignmentModal
        v-model="showAssignmentModal"
        :assignment="editingAssignment"
        @save="handleSaveAssignment"
        @close="handleCloseModal"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/Stores/auth'
import { useAssignmentsStore } from '@/Stores/assignments'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Button from '@/Components/Common/Button.vue'
import AssignmentsList from '@/Components/Assignments/AssignmentsList.vue'
import AssignmentsCalendar from '@/Components/Assignments/AssignmentsCalendar.vue'
import AssignmentFilters from '@/Components/Assignments/AssignmentFilters.vue'
import UpcomingDeadlines from '@/Components/Assignments/UpcomingDeadlines.vue'
import AssignmentModal from '@/Components/Modals/AssignmentModal.vue'
import { Plus, List, Calendar } from 'lucide-vue-next'

const authStore = useAuthStore()
const assignmentsStore = useAssignmentsStore()

const user = computed(() => authStore.user)

const viewMode = ref('list')
const selectedDate = ref(null)
const showAssignmentModal = ref(false)
const editingAssignment = ref(null)

const assignments = computed(() => assignmentsStore.assignments)
const filteredAssignments = computed(() => assignmentsStore.filteredAssignments)

const courses = ref([
  { id: 'cs201', name: 'Data Structures & Algorithms' },
  { id: 'cs301', name: 'Database Systems' },
  { id: 'cs302', name: 'Computer Networks' },
  { id: 'cs303', name: 'Software Engineering' }
])

onMounted(async () => {
  await assignmentsStore.fetchAssignments()
})

const handleFilterChange = (filters) => {
  assignmentsStore.updateFilters(filters)
}

const handleEditAssignment = (assignment) => {
  editingAssignment.value = assignment
  showAssignmentModal.value = true
}

const handleSelectAssignment = (assignment) => {
  handleEditAssignment(assignment)
}

const handleDateSelected = (date) => {
  selectedDate.value = date
  assignmentsStore.updateFilters({ dueDate: date })
  viewMode.value = 'list'
}

const handleToggleComplete = async (id) => {
  await assignmentsStore.toggleComplete(id)
}

const handleDeleteAssignment = async (id) => {
  await assignmentsStore.deleteAssignment(id)
}

const handleSaveAssignment = async (assignmentData) => {
  if (editingAssignment.value) {
    await assignmentsStore.updateAssignment(editingAssignment.value.id, assignmentData)
  } else {
    await assignmentsStore.addAssignment(assignmentData)
  }
  handleCloseModal()
}

const handleCloseModal = () => {
  showAssignmentModal.value = false
  editingAssignment.value = null
}
</script>
