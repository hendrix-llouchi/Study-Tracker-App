<template>
  <AppLayout :user="user">
    <div class="h-[calc(100vh-140px)] flex flex-col space-y-6">
      
      <!-- AI Header -->
      <section class="flex flex-col md:flex-row md:items-end justify-between gap-4 px-4 sm:px-0">
        <div class="space-y-1">
          <div class="flex items-center gap-2 text-brand-secondary font-black uppercase tracking-[0.2em] text-[10px]">
            <Sparkles :size="14" />
            Vibrant Tutor
          </div>
          <h1 class="text-3xl font-black text-text-primary dark:text-white tracking-tight">AI Study Coach</h1>
          <p class="text-xs text-text-secondary dark:text-gray-400">
            Powered by Gemini • <span class="text-brand-primary font-bold">Balanced Mentor Persona</span>
          </p>
        </div>
        
        <div class="flex items-center gap-3">
          <Button variant="secondary" size="sm" class="glass dark:glass-dark border-none" @click="handleNewChat">
            <Plus :size="18" class="mr-2" />
            New Session
          </Button>
          <Button variant="secondary" size="sm" class="glass dark:glass-dark border-none" @click="handleExport">
            <Download :size="18" class="mr-2" />
            Archive
          </Button>
        </div>
      </section>

      <div class="flex-1 grid grid-cols-1 lg:grid-cols-4 gap-6 overflow-hidden pb-4">
        <!-- Chat History Sidebar -->
        <div class="lg:col-span-1 hidden lg:flex flex-col overflow-hidden space-y-4">
          <ChatHistory
            :history="chatHistory"
            :selected-chat-id="currentChatId"
            @new-chat="handleNewChat"
            @select-chat="handleSelectChat"
            class="flex-1"
          />
        </div>

        <!-- Main Chat Area -->
        <div class="lg:col-span-2 flex flex-col glass dark:glass-dark rounded-[2rem] overflow-hidden border-none shadow-2xl shadow-brand-primary/5">
          <div class="premium-gradient h-1.5 w-full"></div>
          <ChatInterface
            :messages="messages"
            :is-loading="isLoading"
            @send-message="handleSendMessage"
            class="flex-1"
          />
        </div>

        <!-- Right Insights Sidebar -->
        <div class="lg:col-span-1 flex flex-col space-y-4 overflow-y-auto pr-2 scrollbar-hide">
          <ContextIndicator :context="context" />
          
          <Card class="glass dark:glass-dark border-none">
            <div class="p-6 space-y-6">
              <h3 class="text-sm font-black text-text-primary dark:text-white uppercase tracking-widest flex items-center gap-2">
                <Zap :size="16" class="text-brand-accent" />
                Quick Prompts
              </h3>
              
              <div class="space-y-3">
                <button
                  v-for="prompt in quickPrompts"
                  :key="prompt"
                  @click="sendQuickMessage(prompt)"
                  class="w-full text-left p-4 rounded-2xl bg-white dark:bg-brand-surface border border-neutral-gray100 dark:border-white/5 hover:border-brand-primary/50 hover:shadow-lg hover:shadow-brand-primary/5 transition-all text-xs font-bold text-text-secondary dark:text-gray-300 hover:text-brand-primary"
                >
                  {{ prompt }}
                </button>
              </div>
            </div>
          </Card>

          <!-- AI Persona Info -->
          <div class="p-6 rounded-[2rem] bg-gradient-to-br from-brand-primary/10 to-brand-secondary/10 border border-brand-primary/10">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-10 h-10 rounded-full bg-brand-primary/20 flex items-center justify-center">
                <BrainCircuit class="text-brand-primary" :size="20" />
              </div>
              <span class="text-xs font-black text-brand-primary uppercase tracking-widest">Persona Profile</span>
            </div>
            <p class="text-[11px] leading-relaxed text-brand-primary/80 font-medium">
              Your coach is currently in <strong>Balanced Mentor</strong> mode. Expect structured, disciplined advice mixed with empathetic peer support.
            </p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/Stores/auth'
import { useAICoachStore } from '@/Stores/aiCoach'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import ChatInterface from '@/Components/AICoach/ChatInterface.vue'
import ChatHistory from '@/Components/AICoach/ChatHistory.vue'
import ContextIndicator from '@/Components/AICoach/ContextIndicator.vue'
import { Download, Sparkles, Plus, Zap, BrainCircuit } from 'lucide-vue-next'

const authStore = useAuthStore()
const aiCoachStore = useAICoachStore()

const user = computed(() => authStore.user)
const messages = computed(() => aiCoachStore.messages)
const isLoading = computed(() => aiCoachStore.isLoading)
const chatHistory = computed(() => aiCoachStore.chatHistory)
const currentChatId = computed(() => aiCoachStore.currentChatId)
const context = computed(() => aiCoachStore.context)

const quickPrompts = [
  'How can I improve my GPA?',
  'What should I focus on this week?',
  'Analyze my study consistency',
  'Suggest a disciplined schedule'
]

onMounted(async () => {
  await aiCoachStore.loadChatHistory()
  if (!currentChatId.value) {
    await aiCoachStore.startNewChat()
  }
})

const handleSendMessage = async (message) => {
  await aiCoachStore.sendMessage(message)
}

const handleNewChat = async () => {
  await aiCoachStore.startNewChat()
}

const handleSelectChat = async (chatId) => {
  await aiCoachStore.loadChat(chatId)
}

const sendQuickMessage = (message) => {
  handleSendMessage(message)
}

const handleExport = () => {
  aiCoachStore.exportChat()
}
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
