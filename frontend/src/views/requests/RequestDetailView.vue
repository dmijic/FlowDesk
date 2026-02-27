<template>
  <section class="space-y-4" v-if="item">
    <div class="card flex flex-wrap items-center justify-between gap-3">
      <div>
        <h2 class="text-lg font-semibold">Request #{{ item.id }} - {{ item.title }}</h2>
        <p class="text-sm text-slate-500">{{ item.type?.name }} / {{ item.department?.name }}</p>
      </div>
      <div class="flex items-center gap-2">
        <StatusBadge :status="item.status" />
        <button
          v-if="canSubmit"
          class="btn-primary"
          :disabled="loadingSubmit"
          @click="submitRequest"
        >
          {{ loadingSubmit ? 'Submitting...' : 'Submit' }}
        </button>
      </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
      <div class="card">
        <h3 class="font-semibold">Details</h3>
        <p class="mt-2 text-sm">{{ item.description || '-' }}</p>
        <p class="mt-2 text-xs text-slate-500">Priority: {{ item.priority }}</p>
        <p class="text-xs text-slate-500">Submitted: {{ date(item.submitted_at) }}</p>
        <p class="text-xs text-slate-500">Decided: {{ date(item.decided_at) }}</p>
      </div>

      <div class="card">
        <h3 class="font-semibold">Attachments</h3>
        <div class="mt-2 space-y-2 text-sm">
          <div v-for="attachment in item.attachments || []" :key="attachment.id" class="flex items-center justify-between">
            <span>{{ attachment.original_name }}</span>
            <button class="btn-secondary py-1" @click="download(attachment.id, attachment.original_name)">Download</button>
          </div>
          <p v-if="(item.attachments || []).length === 0" class="text-slate-500">No attachments.</p>
        </div>
      </div>
    </div>

    <div class="card">
      <h3 class="font-semibold">Approval Tasks</h3>
      <div class="mt-2 space-y-2 text-sm">
        <div
          v-for="task in item.approval_tasks || []"
          :key="task.id"
          class="rounded-lg border border-slate-200 px-3 py-2"
        >
          <div class="flex items-center justify-between">
            <span>{{ task.step_name }} / {{ task.assignee?.name || task.assigned_to }}</span>
            <StatusBadge :status="task.status" />
          </div>
          <p v-if="task.comment" class="mt-1 text-xs text-slate-500">Comment: {{ task.comment }}</p>
        </div>
      </div>
    </div>

    <div class="card">
      <h3 class="font-semibold">Timeline / Audit</h3>
      <div class="mt-2 space-y-2 text-sm">
        <div v-for="entry in item.timeline || []" :key="entry.id" class="rounded-lg bg-slate-50 px-3 py-2">
          <p class="font-semibold">{{ entry.action }}</p>
          <p class="text-xs text-slate-500">{{ entry.actor?.name || 'System' }} / {{ date(entry.created_at) }}</p>
        </div>
        <p v-if="(item.timeline || []).length === 0" class="text-slate-500">No timeline entries.</p>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import StatusBadge from '../../components/StatusBadge.vue'
import { api } from '../../services/api'
import { http } from '../../services/http'
import { useAuthStore } from '../../stores/auth'
import { useToastStore } from '../../stores/toast'

const route = useRoute()
const auth = useAuthStore()
const toast = useToastStore()

const item = ref(null)
const loadingSubmit = ref(false)

const fetchData = async () => {
  const response = await api(`/requests/${route.params.id}`)
  item.value = response.data
}

const canSubmit = computed(
  () => item.value?.status === 'draft' && item.value?.created_by === auth.user?.id && auth.hasPermission('create_requests'),
)

const submitRequest = async () => {
  loadingSubmit.value = true
  try {
    await api(`/requests/${route.params.id}/submit`, { method: 'POST' })
    toast.push('Request submitted.', 'success')
    await fetchData()
  } catch (error) {
    toast.push(error.message, 'error')
  } finally {
    loadingSubmit.value = false
  }
}

const download = async (attachmentId, filename) => {
  try {
    const response = await http.get(`/api/attachments/${attachmentId}/download`, {
      responseType: 'blob',
    })
    const url = URL.createObjectURL(response.data)
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    link.click()
    URL.revokeObjectURL(url)
  } catch (error) {
    toast.push(error.message, 'error')
  }
}

const date = (value) => (value ? new Date(value).toLocaleString() : '-')

onMounted(async () => {
  try {
    await fetchData()
  } catch (error) {
    toast.push(error.message, 'error')
  }
})
</script>
