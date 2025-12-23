<template>
  <Card padding="lg">
    <h3 class="text-h3 text-text-primary mb-5">Account Settings</h3>

    <div class="space-y-6">
      <!-- Change Password -->
      <div class="p-4 border border-border-default rounded-lg">
        <h4 class="text-body font-semibold text-text-primary mb-3">Change Password</h4>
        <form @submit.prevent="handlePasswordChange" class="space-y-4">
          <Input
            v-model="passwordForm.currentPassword"
            type="password"
            label="Current Password"
            required
            :error="passwordErrors.currentPassword"
          />
          <Input
            v-model="passwordForm.newPassword"
            type="password"
            label="New Password"
            required
            :error="passwordErrors.newPassword"
          />
          <Input
            v-model="passwordForm.confirmPassword"
            type="password"
            label="Confirm New Password"
            required
            :error="passwordErrors.confirmPassword"
          />
          <Button variant="primary" size="md" type="submit" :loading="changingPassword">
            Change Password
          </Button>
        </form>
      </div>

      <!-- Data Export -->
      <div class="p-4 border border-border-default rounded-lg">
        <h4 class="text-body font-semibold text-text-primary mb-2">Data Export</h4>
        <p class="text-body-small text-text-secondary mb-4">
          Download all your data in JSON or CSV format
        </p>
        <div class="flex flex-wrap gap-2">
          <Button variant="secondary" size="md" @click="exportData('json')">
            <Download :size="16" class="mr-2" />
            Export as JSON
          </Button>
          <Button variant="secondary" size="md" @click="exportData('csv')">
            <Download :size="16" class="mr-2" />
            Export as CSV
          </Button>
        </div>
      </div>

      <!-- Account Deletion -->
      <div class="p-4 border border-accent-red rounded-lg bg-red-50">
        <h4 class="text-body font-semibold text-accent-red mb-2">Delete Account</h4>
        <p class="text-body-small text-text-secondary mb-4">
          Permanently delete your account and all associated data. This action cannot be undone.
        </p>
        <Button
          variant="ghost"
          size="md"
          class="text-accent-red hover:bg-red-100"
          @click="showDeleteConfirm = true"
        >
          Delete Account
        </Button>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      v-model="showDeleteConfirm"
      title="Delete Account"
      message="Are you sure you want to delete your account? This action cannot be undone and all your data will be permanently removed."
      confirm-text="Delete Account"
      variant="danger"
      @confirm="handleDeleteAccount"
    />
  </Card>
</template>

<script setup>
import { ref } from 'vue'
import Card from '@/Components/Common/Card.vue'
import Input from '@/Components/Common/Input.vue'
import Button from '@/Components/Common/Button.vue'
import ConfirmModal from '@/Components/Modals/ConfirmModal.vue'
import { Download } from 'lucide-vue-next'

const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

const passwordErrors = ref({})
const changingPassword = ref(false)
const showDeleteConfirm = ref(false)

const handlePasswordChange = async () => {
  passwordErrors.value = {}
  
  if (!passwordForm.value.currentPassword) {
    passwordErrors.value.currentPassword = 'Current password is required'
    return
  }
  
  if (!passwordForm.value.newPassword) {
    passwordErrors.value.newPassword = 'New password is required'
    return
  }
  
  if (passwordForm.value.newPassword.length < 8) {
    passwordErrors.value.newPassword = 'Password must be at least 8 characters'
    return
  }
  
  if (passwordForm.value.newPassword !== passwordForm.value.confirmPassword) {
    passwordErrors.value.confirmPassword = 'Passwords do not match'
    return
  }
  
  changingPassword.value = true
  try {
    // Bypass API call for now
    console.log('Password change:', passwordForm.value)
    alert('Password changed successfully')
    passwordForm.value = {
      currentPassword: '',
      newPassword: '',
      confirmPassword: ''
    }
  } catch (error) {
    passwordErrors.value.currentPassword = 'Incorrect current password'
  } finally {
    changingPassword.value = false
  }
}

const exportData = async (format) => {
  // Bypass API call - simulate data export
  console.log(`Exporting data as ${format}`)
  alert(`Data export as ${format.toUpperCase()} will be available in Phase 2`)
}

const handleDeleteAccount = async () => {
  // Bypass API call for now
  console.log('Account deletion requested')
  alert('Account deletion will be available in Phase 2')
  showDeleteConfirm.value = false
}
</script>

