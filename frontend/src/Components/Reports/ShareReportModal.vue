<template>
  <BaseModal
    :model-value="modelValue"
    title="Share Report"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <div class="space-y-4">
      <Input
        v-model="shareLink"
        type="text"
        label="Share Link"
        readonly
        class="bg-neutral-gray50"
      />
      <div class="flex items-center gap-2">
        <Button variant="secondary" size="md" @click="copyLink" class="flex-1">
          <Copy :size="16" class="mr-2" />
          Copy Link
        </Button>
        <Button variant="secondary" size="md" @click="shareOnSocial('twitter')" class="flex-1">
          <Share2 :size="16" class="mr-2" />
          Twitter
        </Button>
      </div>
      <div v-if="copied" class="p-3 bg-primary-green-bg border border-primary-green-light rounded-lg">
        <p class="text-body-small text-text-success">Link copied to clipboard!</p>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import { ref } from 'vue'
import BaseModal from '@/Components/Modals/BaseModal.vue'
import Input from '@/Components/Common/Input.vue'
import Button from '@/Components/Common/Button.vue'
import { Copy, Share2 } from 'lucide-vue-next'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  reportId: {
    type: String,
    default: ''
  }
})

defineEmits(['update:modelValue'])

const shareLink = ref(`${window.location.origin}/reports/${props.reportId}`)
const copied = ref(false)

const copyLink = () => {
  navigator.clipboard.writeText(shareLink.value)
  copied.value = true
  setTimeout(() => {
    copied.value = false
  }, 3000)
}

const shareOnSocial = (platform) => {
  const url = encodeURIComponent(shareLink.value)
  const text = encodeURIComponent('Check out my weekly study progress report!')
  
  if (platform === 'twitter') {
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank')
  }
}
</script>

