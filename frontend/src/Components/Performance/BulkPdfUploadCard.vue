<template>
  <Card class="liquid-glass border-none overflow-hidden h-full p-8 space-y-8">
    <div class="flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-strap-indigo/10 flex items-center justify-center border border-strap-indigo/20 shadow-lg">
        <FileText class="text-strap-indigo" :size="24" />
      </div>
      <div>
        <h3 class="text-xl font-black text-text-main dark:text-white tracking-tight">Bulk Transcript Feed</h3>
        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-strap-indigo">Neural Extraction Active</p>
      </div>
    </div>
    
    <div
      @drop.prevent="handleDrop"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      :class="[
        isDragging ? 'border-strap-indigo bg-strap-indigo/5 scale-[0.98]' : 'border-neutral-gray200 dark:border-white/10 hover:border-strap-indigo/40',
        uploading ? 'pointer-events-none opacity-50' : ''
      ]"
      class="border-2 border-dashed rounded-[2rem] p-10 text-center cursor-pointer transition-all duration-500 group relative overflow-hidden"
      @click="$refs.fileInput.click()"
    >
      <!-- Animated Background Glow -->
      <div class="absolute inset-0 bg-gradient-to-br from-strap-indigo/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>

      <div class="relative z-10 space-y-2">
        <Upload :size="48" class="mx-auto mb-4 text-neutral-gray300 dark:text-gray-600 group-hover:text-strap-indigo group-hover:animate-bounce transition-all" />
        <p class="text-lg font-black text-text-main dark:text-white">
          Drop PDF Records
        </p>
        <p class="text-xs font-medium text-text-muted">
          Max 5MB per academic transcript
        </p>
      </div>
      
      <input ref="fileInput" type="file" accept=".pdf" multiple class="hidden" @change="handleFileSelect" />
      
      <Button variant="secondary" size="sm" class="mt-6 liquid-glass border-none hover:bg-neutral-gray100 dark:hover:bg-white/10 px-6 py-2 rounded-xl text-xs font-black uppercase tracking-widest dark:text-white">
        Select Files
      </Button>
    </div>

    <!-- Processing List -->
    <TransitionGroup name="list" tag="div" class="space-y-3">
      <div
        v-for="(file, index) in selectedFiles"
        :key="file.name + file.size"
        class="p-4 bg-white/40 dark:bg-white/5 rounded-2xl flex items-center justify-between group/item border border-transparent hover:border-strap-indigo/20 transition-all"
      >
        <div class="flex items-center gap-4 flex-1 min-w-0">
          <div class="w-10 h-10 rounded-xl bg-white dark:bg-strap-navy flex items-center justify-center shadow-sm">
            <FileText :size="20" class="text-strap-indigo" />
          </div>
          <div class="flex flex-col min-w-0">
            <span class="text-sm font-black text-text-main dark:text-white truncate">{{ file.name }}</span>
            <span class="text-[10px] text-text-muted uppercase font-black tracking-widest">
              {{ formatFileSize(file.size) }}
            </span>
          </div>
        </div>
        <button 
          @click.stop="removeFile(index)" 
          class="p-2 rounded-xl hover:bg-red-500/10 text-neutral-gray400 hover:text-red-500 transition-all opacity-0 group-hover/item:opacity-100"
          :disabled="uploading"
        >
          <X :size="16" />
        </button>
      </div>
    </TransitionGroup>

    <!-- Upload Progress -->
    <div v-if="uploading" class="space-y-4 animate-in fade-in duration-300">
      <div class="flex flex-col space-y-2">
        <div class="flex justify-between items-end">
          <span class="text-[10px] font-black uppercase tracking-[0.2em] text-strap-indigo flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-strap-indigo animate-pulse"></div>
            {{ currentStatus }}
          </span>
          <span class="text-2xl font-black text-strap-indigo tracking-tighter">{{ uploadProgress }}%</span>
        </div>
        <div class="w-full bg-neutral-gray100 dark:bg-white/5 rounded-full h-3 overflow-hidden shadow-inner">
          <div
            class="strap-gradient-bg h-full transition-all duration-500 shadow-[0_0_15px_rgba(99,102,241,0.5)]"
            :style="{ width: uploadProgress + '%' }"
          ></div>
        </div>
      </div>
    </div>

    <!-- Execute Action -->
    <div v-if="selectedFiles.length > 0 && !uploading" class="animate-in fade-in slide-in-from-top-4 duration-500">
      <Button
        variant="primary"
        size="lg"
        class="w-full strap-gradient-bg border-none shadow-2xl shadow-strap-indigo/30 py-6 rounded-[1.5rem] font-black tracking-tight"
        @click="handleUpload"
      >
        Synchronize {{ selectedFiles.length }} File{{ selectedFiles.length > 1 ? 's' : '' }}
      </Button>
    </div>

    <!-- Status Alerts -->
    <Transition name="fade">
      <div v-if="uploadComplete && !uploading" 
        :class="uploadErrors.length > 0 ? 'bg-amber-500/10 border-amber-500/20' : 'bg-emerald-500/10 border-emerald-500/20'"
        class="p-5 rounded-3xl border flex items-start gap-4 animate-in zoom-in-95 duration-300"
      >
        <CheckCircle2 v-if="uploadErrors.length === 0" class="text-emerald-500 w-6 h-6 flex-shrink-0" />
        <AlertCircle v-else class="text-amber-500 w-6 h-6 flex-shrink-0" />
        <div>
          <p class="text-sm font-black" :class="uploadErrors.length === 0 ? 'text-emerald-500' : 'text-amber-500'">
            {{ uploadErrors.length === 0 ? 'Synthesis Complete' : 'Partial Synchronization' }}
          </p>
          <p class="text-xs font-medium text-text-muted mt-1 leading-relaxed">
            Standardized {{ uploadedCount }} academic records to your profile.
          </p>
        </div>
      </div>
    </Transition>
  </Card>
</template>

<script setup>
import { ref } from 'vue'
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import { Upload, FileText, X, CheckCircle2, AlertCircle, AlertTriangle } from 'lucide-vue-next'

const emit = defineEmits(['upload'])

const fileInput = ref(null)
const selectedFiles = ref([])
const uploading = ref(false)
const isDragging = ref(false)
const uploadProgress = ref(0)
const uploadedCount = ref(0)
const totalFiles = ref(0)
const currentStatus = ref('')
const error = ref('')
const uploadComplete = ref(false)
const uploadErrors = ref([])

const handleDrop = (event) => {
  isDragging.value = false
  const files = Array.from(event.dataTransfer.files)
  addFiles(files)
}

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files)
  addFiles(files)
}

const addFiles = (files) => {
  error.value = ''
  uploadComplete.value = false
  uploadErrors.value = []

  const pdfFiles = files.filter(file => {
    const fileName = file.name.toLowerCase()
    const isValidPdf = fileName.endsWith('.pdf')
    if (!isValidPdf) return false
    if (file.size > 5 * 1024 * 1024) return false
    return true
  })

  pdfFiles.forEach(file => {
    const exists = selectedFiles.value.some(f => f.name === file.name && f.size === file.size)
    if (!exists) selectedFiles.value.push(file)
  })
}

const removeFile = (index) => {
  selectedFiles.value.splice(index, 1)
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 10) / 10 + ' ' + sizes[i]
}

const handleUpload = async () => {
  if (selectedFiles.value.length === 0) return
  uploading.value = true
  uploadProgress.value = 0
  uploadedCount.value = 0
  totalFiles.value = selectedFiles.value.length
  currentStatus.value = 'Analyzing Transcript Structure...'
  
  try {
    emit('upload', selectedFiles.value, {
      onProgress: (progress, current, total, status) => {
        uploadProgress.value = progress
        uploadedCount.value = current
        currentStatus.value = status || 'Parsing Data Objects...'
      },
      onComplete: (successCount, errors) => {
        uploadComplete.value = true
        uploadedCount.value = successCount
        uploadErrors.value = errors || []
        uploading.value = false
        if (!errors?.length) setTimeout(() => selectedFiles.value = [], 5000)
      },
      onError: (msg) => {
        error.value = msg
        uploading.value = false
      }
    })
  } catch (err) {
    uploading.value = false
  }
}
</script>

<style scoped>
.list-enter-active, .list-leave-active { transition: all 0.4s ease; }
.list-enter-from { opacity: 0; transform: translateX(-30px); }
.list-leave-to { opacity: 0; transform: translateX(30px); }
</style>
