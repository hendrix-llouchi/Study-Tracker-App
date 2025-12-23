<template>
  <Card padding="lg">
    <h3 class="text-h3 text-text-primary mb-5">Profile Information</h3>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div class="flex items-center gap-6 mb-6">
        <div class="relative">
          <div class="w-24 h-24 rounded-full bg-neutral-gray200 flex items-center justify-center overflow-hidden">
            <img
              v-if="form.avatar"
              :src="form.avatar"
              alt="Profile"
              class="w-full h-full object-cover"
            />
            <User v-else :size="32" class="text-text-tertiary" />
          </div>
          <button
            type="button"
            class="absolute bottom-0 right-0 w-8 h-8 bg-primary-green rounded-full flex items-center justify-center text-white hover:bg-primary-green-hover transition-colors"
            @click="$refs.avatarInput.click()"
          >
            <Camera :size="16" />
          </button>
          <input
            ref="avatarInput"
            type="file"
            accept="image/*"
            class="hidden"
            @change="handleAvatarChange"
          />
        </div>
        <div>
          <p class="text-body font-medium text-text-primary mb-1">Profile Picture</p>
          <p class="text-body-small text-text-secondary">JPG, PNG or GIF. Max size 2MB</p>
        </div>
      </div>

      <Input
        v-model="form.name"
        type="text"
        label="Full Name"
        required
        :error="errors.name"
      />

      <Input
        v-model="form.email"
        type="email"
        label="Email Address"
        required
        :error="errors.email"
        disabled
      />

      <Input
        v-model="form.university"
        type="text"
        label="University"
        required
        :error="errors.university"
      />

      <div class="flex flex-col-reverse sm:flex-row gap-3 justify-end pt-4">
        <Button variant="ghost" type="button" @click="$emit('cancel')">
          Cancel
        </Button>
        <Button variant="primary" type="submit" :loading="loading">
          Save Changes
        </Button>
      </div>
    </form>
  </Card>
</template>

<script setup>
import { ref } from 'vue'
import Card from '@/Components/Common/Card.vue'
import Input from '@/Components/Common/Input.vue'
import Button from '@/Components/Common/Button.vue'
import { User, Camera } from 'lucide-vue-next'

const props = defineProps({
  profile: {
    type: Object,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['submit', 'cancel'])

const form = ref({
  name: props.profile.name || '',
  email: props.profile.email || '',
  university: props.profile.university || '',
  avatar: props.profile.avatar || null
})

const errors = ref({})

const handleAvatarChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 2 * 1024 * 1024) {
      alert('File size must be less than 2MB')
      return
    }
    const reader = new FileReader()
    reader.onload = (e) => {
      form.value.avatar = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const handleSubmit = () => {
  errors.value = {}
  
  if (!form.value.name) errors.value.name = 'Name is required'
  if (!form.value.email) errors.value.email = 'Email is required'
  if (!form.value.university) errors.value.university = 'University is required'
  
  if (Object.keys(errors.value).length > 0) {
    return
  }
  
  emit('submit', form.value)
}
</script>

