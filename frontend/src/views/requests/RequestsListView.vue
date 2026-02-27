<template>
  <section class="space-y-4">
    <div class="card flex items-center justify-between">
      <div>
        <h2 class="text-lg font-semibold">Requests</h2>
        <p class="text-sm text-slate-500">Moji zahtjevi i dodijeljeni approval tokovi.</p>
      </div>
      <RouterLink v-if="auth.hasPermission('create_requests')" class="btn-primary" to="/requests/create">
        New Request
      </RouterLink>
    </div>

    <div class="card overflow-auto">
      <table class="w-full text-left text-sm">
        <thead>
          <tr class="border-b border-slate-200 text-xs uppercase text-slate-500">
            <th class="pb-2">ID</th>
            <th class="pb-2">Title</th>
            <th class="pb-2">Type</th>
            <th class="pb-2">Priority</th>
            <th class="pb-2">Status</th>
            <th class="pb-2"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id" class="border-b border-slate-100">
            <td class="py-2">#{{ item.id }}</td>
            <td class="py-2">{{ item.title }}</td>
            <td class="py-2">{{ item.type?.name }}</td>
            <td class="py-2">{{ item.priority }}</td>
            <td class="py-2"><StatusBadge :status="item.status" /></td>
            <td class="py-2 text-right">
              <RouterLink class="text-brand-600" :to="`/requests/${item.id}`">Open</RouterLink>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-if="items.length === 0" class="py-6 text-center text-sm text-slate-500">No requests yet.</p>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import StatusBadge from '../../components/StatusBadge.vue'
import { api } from '../../services/api'
import { useAuthStore } from '../../stores/auth'
import { useToastStore } from '../../stores/toast'

const auth = useAuthStore()
const toast = useToastStore()
const items = ref([])

onMounted(async () => {
  try {
    const response = await api('/requests')
    items.value = response.data || []
  } catch (error) {
    toast.push(error.message, 'error')
  }
})
</script>
