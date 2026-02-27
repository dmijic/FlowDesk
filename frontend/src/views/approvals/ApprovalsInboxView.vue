<template>
  <section class="space-y-4">
    <div class="card flex items-center justify-between">
      <div>
        <h2 class="text-lg font-semibold">Approvals Inbox</h2>
        <p class="text-sm text-slate-500">Pending tasks assigned to you.</p>
      </div>
      <button class="btn-secondary" @click="load">Refresh</button>
    </div>

    <div class="card overflow-auto">
      <table class="w-full text-left text-sm">
        <thead>
          <tr class="border-b border-slate-200 text-xs uppercase text-slate-500">
            <th class="pb-2">Task</th>
            <th class="pb-2">Request</th>
            <th class="pb-2">Rule</th>
            <th class="pb-2">Status</th>
            <th class="pb-2"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="task in tasks" :key="task.id" class="border-b border-slate-100">
            <td class="py-2">{{ task.step_name }}</td>
            <td class="py-2">#{{ task.request_id }} / {{ task.request?.title }}</td>
            <td class="py-2">{{ task.rule }}</td>
            <td class="py-2"><StatusBadge :status="task.status" /></td>
            <td class="py-2 text-right">
              <button class="btn-secondary py-1" @click="decide(task.id, 'approve')">Approve</button>
              <button class="ml-2 rounded-lg bg-rose-600 px-3 py-1 text-xs font-semibold text-white" @click="decide(task.id, 'reject')">
                Reject
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-if="tasks.length === 0" class="py-4 text-center text-sm text-slate-500">No pending tasks.</p>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import StatusBadge from '../../components/StatusBadge.vue'
import { api } from '../../services/api'
import { useToastStore } from '../../stores/toast'

const toast = useToastStore()
const tasks = ref([])

const load = async () => {
  const response = await api('/approvals/inbox')
  tasks.value = response.data || []
}

const decide = async (taskId, action) => {
  const comment = window.prompt(`${action === 'approve' ? 'Approve' : 'Reject'} comment`, '') || ''

  try {
    await api(`/approvals/tasks/${taskId}/${action}`, {
      method: 'POST',
      body: { comment },
    })
    toast.push(`Task ${action}d.`, 'success')
    await load()
  } catch (error) {
    toast.push(error.message, 'error')
  }
}

onMounted(async () => {
  try {
    await load()
  } catch (error) {
    toast.push(error.message, 'error')
  }
})
</script>
