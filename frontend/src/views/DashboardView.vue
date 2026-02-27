<template>
  <section class="space-y-4">
    <div class="card">
      <h2 class="text-lg font-semibold">Dashboard</h2>
      <p class="text-sm text-slate-500">Pregled statusa zahtjeva i osnovne metrike.</p>
    </div>

    <div v-if="loading" class="card text-sm text-slate-500">Loading...</div>

    <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <div class="card" v-for="item in countCards" :key="item.label">
        <p class="text-xs uppercase text-slate-500">{{ item.label }}</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ item.value }}</p>
      </div>
    </div>

    <div class="card" v-if="summary">
      <h3 class="text-sm font-semibold">Top Request Types</h3>
      <div class="mt-3 space-y-2 text-sm">
        <div class="flex items-center justify-between" v-for="row in summary.top_request_types" :key="row.name">
          <span>{{ row.name }}</span>
          <span class="font-semibold">{{ row.total }}</span>
        </div>
      </div>
      <p class="mt-4 text-xs text-slate-500">
        Average decision time: {{ summary.average_decision_time_hours }}h
      </p>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { api } from '../services/api'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const loading = ref(true)
const summary = ref(null)

const countCards = computed(() => {
  if (!summary.value) return []

  const counts = summary.value.counts_by_status || {}

  return [
    { label: 'Draft', value: counts.draft || 0 },
    { label: 'In Review', value: counts.in_review || 0 },
    { label: 'Approved', value: counts.approved || 0 },
    { label: 'Rejected', value: counts.rejected || 0 },
  ]
})

onMounted(async () => {
  try {
    if (auth.hasPermission('view_reports')) {
      summary.value = await api('/reports/summary')
    } else {
      const response = await api('/requests')
      const list = response.data || []
      const counts = list.reduce((acc, item) => {
        acc[item.status] = (acc[item.status] || 0) + 1
        return acc
      }, {})
      summary.value = {
        counts_by_status: counts,
        average_decision_time_hours: 0,
        top_request_types: [],
      }
    }
  } finally {
    loading.value = false
  }
})
</script>
