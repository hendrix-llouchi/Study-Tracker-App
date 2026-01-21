<template>
  <div class="space-y-4">
    <div v-if="results.length === 0" class="liquid-glass p-20 text-center rounded-[2rem] border-dashed border-strap-indigo/20">
      <FileText class="w-16 h-16 text-strap-indigo/20 mx-auto mb-6 animate-pulse" />
      <h3 class="text-xl font-black text-text-main dark:text-white mb-2">No Records Found</h3>
      <p class="text-sm font-medium text-text-muted mb-8">Synchronize your transcripts or add manual markers to begin.</p>
      <Button variant="primary" size="md" class="strap-gradient-bg border-none px-8 rounded-xl" @click="$emit('add-result')">
        Initialize First Record
      </Button>
    </div>

    <div v-else class="space-y-3">
      <div 
        v-for="result in results" 
        :key="result.id"
        class="group liquid-glass p-5 rounded-[1.5rem] flex flex-wrap md:flex-nowrap items-center gap-6 border-white/40 dark:border-white/5 hover:border-strap-indigo/30 transition-all duration-500"
      >
        <!-- Course Meta -->
        <div class="flex items-center gap-4 flex-1 min-w-[200px]">
          <div :class="`w-12 h-12 rounded-2xl flex items-center justify-center font-black text-white shadow-lg ${getGradeBg(result.grade)}`">
            {{ result.grade }}
          </div>
          <div class="flex flex-col">
            <h4 class="text-base font-black text-text-main dark:text-white group-hover:text-strap-indigo transition-colors">{{ result.course }}</h4>
            <span class="text-[10px] font-black uppercase tracking-widest text-text-muted">{{ result.semester }}</span>
          </div>
        </div>

        <!-- Score & Type -->
        <div class="flex items-center gap-8 px-6 border-x border-white/20 dark:border-white/5">
          <div class="flex flex-col items-center">
            <span class="text-[9px] font-black uppercase tracking-widest text-text-muted mb-1">Score</span>
            <span class="text-sm font-black text-text-main dark:text-white">{{ result.score }} / {{ result.maxScore }}</span>
          </div>
          <div class="flex flex-col items-center">
            <span class="text-[9px] font-black uppercase tracking-widest text-text-muted mb-1">Type</span>
            <div class="px-3 py-1 rounded-full bg-strap-indigo/10 text-strap-indigo text-[10px] font-black uppercase tracking-widest">
              {{ result.assessmentType }}
            </div>
          </div>
        </div>

        <!-- Date & Actions -->
        <div class="flex items-center gap-6 ml-auto">
          <div class="hidden sm:flex flex-col items-end">
             <span class="text-[9px] font-black uppercase tracking-widest text-text-muted mb-1">Recorded</span>
             <span class="text-xs font-bold text-text-main dark:text-white">{{ formatDate(result.date) }}</span>
          </div>
          
          <div class="flex items-center gap-2">
            <button @click="$emit('edit-result', result)" class="p-2.5 rounded-xl hover:bg-strap-indigo/10 text-text-muted hover:text-strap-indigo transition-all">
              <Edit :size="18" />
            </button>
            <button @click="$emit('delete-result', result.id)" class="p-2.5 rounded-xl hover:bg-red-500/10 text-text-muted hover:text-red-500 transition-all">
              <Trash2 :size="18" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Button from '@/Components/Common/Button.vue'
import { FileText, Edit, Trash2, Plus } from 'lucide-vue-next'

defineProps({
  results: { type: Array, default: () => [] }
})

defineEmits(['add-result', 'edit-result', 'delete-result'])

const getGradeBg = (grade) => {
  if (['A+', 'A', 'A-'].includes(grade)) return 'strap-gradient-bg'
  if (['B+', 'B', 'B-'].includes(grade)) return 'bg-strap-indigo'
  if (['C+', 'C', 'C-'].includes(grade)) return 'bg-strap-amber text-strap-navy'
  return 'bg-red-500'
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>
