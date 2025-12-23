import { defineStore } from 'pinia'

export const useAICoachStore = defineStore('aiCoach', {
  state: () => ({
    messages: [],
    currentChatId: null,
    chatHistory: [],
    isLoading: false,
    context: {
      hasResults: true,
      hasPlans: true,
      hasTimetable: true
    }
  }),

  actions: {
    async sendMessage(message) {
      this.messages.push({
        id: `msg_${Date.now()}`,
        role: 'user',
        content: message,
        timestamp: new Date().toISOString()
      })

      this.isLoading = true

      // Simulate AI response (Phase 2 will use Gemini API)
      setTimeout(() => {
        const response = this.generateMockResponse(message)
        this.messages.push({
          id: `msg_${Date.now()}`,
          role: 'assistant',
          content: response,
          timestamp: new Date().toISOString()
        })
        this.isLoading = false
      }, 1000)
    },

    generateMockResponse(message) {
      // Mock responses based on keywords (Phase 2 will use Gemini)
      const lowerMessage = message.toLowerCase()
      
      if (lowerMessage.includes('gpa') || lowerMessage.includes('grade')) {
        return "Based on your current results, your GPA is showing an upward trend. I'd recommend focusing on your weaker subjects, particularly Computer Networks, to maintain this improvement."
      }
      
      if (lowerMessage.includes('study') || lowerMessage.includes('plan')) {
        return "I notice you've been consistent with your study planning. To improve further, try to increase your study hours gradually and maintain your current completion rate."
      }
      
      if (lowerMessage.includes('time') || lowerMessage.includes('schedule')) {
        return "Your timetable looks well-structured. I suggest using the suggested study slots feature to find optimal times between classes for focused studying."
      }
      
      return "I'm here to help you improve your academic performance. I have access to your grades, study plans, and timetable. Feel free to ask me about your performance trends, study strategies, or any specific challenges you're facing. In Phase 2, I'll be powered by Google Gemini for more intelligent responses."
    },

    async loadChatHistory() {
      // Bypass API call - use mock data
      this.chatHistory = [
        {
          id: 'chat_001',
          title: 'GPA Improvement Strategy',
          lastMessage: 'Based on your current results...',
          timestamp: new Date().toISOString()
        },
        {
          id: 'chat_002',
          title: 'Study Plan Optimization',
          lastMessage: 'I notice you\'ve been consistent...',
          timestamp: new Date(Date.now() - 86400000).toISOString()
        }
      ]
      return this.chatHistory
    },

    async startNewChat() {
      this.messages = []
      this.currentChatId = `chat_${Date.now()}`
    },

    async loadChat(chatId) {
      this.currentChatId = chatId
      // Load messages for this chat (mock for now)
      this.messages = []
    },

    exportChat() {
      const chatData = {
        chatId: this.currentChatId,
        messages: this.messages,
        exportedAt: new Date().toISOString()
      }
      
      const blob = new Blob([JSON.stringify(chatData, null, 2)], { type: 'application/json' })
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `ai-coach-chat-${this.currentChatId}.json`
      a.click()
      URL.revokeObjectURL(url)
    }
  }
})

