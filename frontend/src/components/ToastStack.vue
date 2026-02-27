<template>
  <div class="fixed bottom-4 right-4 z-50 flex w-80 flex-col gap-2">
    <div
      v-for="item in toast.items"
      :key="item.id"
      class="rounded-lg px-4 py-3 text-sm shadow-lg"
      :class="tone(item.type)"
    >
      <div class="flex items-center justify-between gap-3">
        <span>{{ item.message }}</span>
        <button class="text-xs opacity-70 hover:opacity-100" @click="toast.remove(item.id)">x</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useToastStore } from '../stores/toast'

const toast = useToastStore()

const tone = (type) => {
  if (type === 'success') return 'bg-emerald-600 text-white'
  if (type === 'error') return 'bg-rose-600 text-white'
  return 'bg-slate-900 text-white'
}
</script>
