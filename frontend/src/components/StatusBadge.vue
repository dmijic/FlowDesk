<template>
  <span class="badge" :class="colorClass">{{ label }}</span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
})

const label = computed(() => props.status.replace('_', ' '))

const colorClass = computed(() => {
  const map = {
    draft: 'bg-slate-200 text-slate-700',
    submitted: 'bg-indigo-100 text-indigo-700',
    in_review: 'bg-amber-100 text-amber-700',
    approved: 'bg-emerald-100 text-emerald-700',
    rejected: 'bg-rose-100 text-rose-700',
    canceled: 'bg-slate-300 text-slate-800',
    pending: 'bg-amber-100 text-amber-700',
    skipped: 'bg-slate-200 text-slate-700',
  }

  return map[props.status] || 'bg-slate-200 text-slate-700'
})
</script>
