<template>
  <AppLayout :user="user" :unread-count="0">
    <div class="h-[calc(100vh-120px)] flex flex-col">
      <div class="mb-4">
        <h1 class="text-h1 text-text-primary mb-2">AI Study Coach</h1>
        <p class="text-body text-text-secondary">Get personalized study advice and strategies</p>
      </div>

      <div class="flex-1 grid grid-cols-1 lg:grid-cols-4 gap-4 lg:gap-6 overflow-hidden">
        <!-- Chat History Sidebar -->
        <div class="lg:col-span-1 hidden lg:block overflow-y-auto">
          <ChatHistory
            :history="chatHistory"
            :selected-chat-id="currentChatId"
            @new-chat="handleNewChat"
            @select-chat="handleSelectChat"
          />
        </div>

        <!-- Main Chat Area -->
        <div class="lg:col-span-2 flex flex-col bg-neutral-white border border-border-default rounded-lg overflow-hidden">
          <ChatInterface
            :messages="messages"
            :is-loading="isLoading"
            @send-message="handleSendMessage"
          />
        </div>

        <!-- Context Indicator -->
        <div class="lg:col-span-1 overflow-y-auto">
          <ContextIndicator :context="context" />
          
          <div class="mt-4">
            <Card padding="default">
              <h3 class="text-h3 text-text-primary mb-4">Quick Actions</h3>
              <div class="space-y-2">
                <Button
                  variant="secondary"
                  size="md"
                  class="w-full justify-start"
                  @click="sendQuickMessage('How can I improve my GPA?')"
                >
                  Improve GPA
                </Button>
                <Button
                  variant="secondary"
                  size="md"
                  class="w-full justify-start"
                  @click="sendQuickMessage('What should I focus on this week?')"
                >
                  Weekly Focus
                </Button>
                <Button
                  variant="secondary"
                  size="md"
                  class="w-full justify-start"
                  @click="sendQuickMessage('Suggest a study schedule')"
                >
                  Study Schedule
                </Button>
                <Button
                  variant="ghost"
                  size="md"
                  class="w-full justify-start"
                  @click="handleExport"
                >
                  <Download :size="16" class="mr-2" />
                  Export Chat
                </Button>
              </div>
            </Card>
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
import { Download } from 'lucide-vue-next'

const authStore = useAuthStore()
const aiCoachStore = useAICoachStore()

const user = computed(() => authStore.user)

const messages = computed(() => aiCoachStore.messages)
const isLoading = computed(() => aiCoachStore.isLoading)
const chatHistory = computed(() => aiCoachStore.chatHistory)
const currentChatId = computed(() => aiCoachStore.currentChatId)
const context = computed(() => aiCoachStore.context)

onMounted(async () => {
  await aiCoachStore.loadChatHistory()
  await aiCoachStore.startNewChat()
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

