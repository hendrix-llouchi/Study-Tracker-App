<template>
  <AppLayout :user="user" :unread-count="0">
    <div class="space-y-4 lg:space-y-6">
      <div>
        <h1 class="text-h1 text-text-primary mb-2">Weekly Progress Reports</h1>
        <p class="text-body text-text-secondary">View your weekly study performance summaries</p>
      </div>

      <ReportsArchive
        :reports="reports"
        @view-report="handleViewReport"
      />

      <!-- Report Viewer Modal -->
      <BaseModal
        v-model="showReportViewer"
        :title="selectedReport ? `Week ${selectedReport.weekNumber} Report` : ''"
        class="max-w-4xl"
      >
        <ReportViewer
          v-if="selectedReport"
          :report="selectedReport"
          @share="handleShare"
          @download="handleDownload"
        />
      </BaseModal>

      <!-- Share Modal -->
      <ShareReportModal
        v-model="showShareModal"
        :report-id="selectedReport?.id"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/Stores/auth'
import { useReportsStore } from '@/Stores/reports'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import BaseModal from '@/Components/Modals/BaseModal.vue'
import ReportsArchive from '@/Components/Reports/ReportsArchive.vue'
import ReportViewer from '@/Components/Reports/ReportViewer.vue'
import ShareReportModal from '@/Components/Reports/ShareReportModal.vue'

const authStore = useAuthStore()
const reportsStore = useReportsStore()

const user = computed(() => authStore.user)
const reports = computed(() => reportsStore.reports)

const showReportViewer = ref(false)
const showShareModal = ref(false)
const selectedReport = ref(null)

onMounted(async () => {
  await reportsStore.fetchReports()
})

const handleViewReport = async (report) => {
  selectedReport.value = report
  showReportViewer.value = true
  await reportsStore.fetchReport(report.id)
}

const handleShare = () => {
  showShareModal.value = true
}

const handleDownload = () => {
  console.log('Download report as PDF')
  alert('PDF download will be available in Phase 2')
}
</script>

