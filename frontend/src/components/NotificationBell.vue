<template>
  <div class="relative">
    <button class="relative rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="open = !open">
      Notifications
      <span
        v-if="store.unreadCount > 0"
        class="ml-2 rounded-full bg-rose-600 px-2 py-0.5 text-xs font-semibold text-white"
      >
        {{ store.unreadCount }}
      </span>
    </button>

    <div
      v-if="open"
      class="absolute right-0 z-20 mt-2 w-80 rounded-xl border border-slate-200 bg-white p-2 shadow-xl"
    >
      <div class="mb-2 flex items-center justify-between px-2">
        <h4 class="text-sm font-semibold">In-app Notifications</h4>
        <button class="text-xs text-brand-600" @click="store.fetch">Refresh</button>
      </div>
      <div class="max-h-80 overflow-y-auto">
        <button
          v-for="item in store.items"
          :key="item.id"
          class="mb-1 w-full rounded-lg px-3 py-2 text-left text-xs transition hover:bg-slate-100"
          @click="handleClick(item.id)"
        >
          <div class="font-semibold" :class="item.read_at ? 'text-slate-500' : 'text-slate-800'">
            {{ item.data.message || item.type }}
          </div>
          <div class="text-[11px] text-slate-500">{{ new Date(item.created_at).toLocaleString() }}</div>
        </button>
        <p v-if="store.items.length === 0" class="px-3 py-4 text-xs text-slate-500">No notifications.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useNotificationStore } from '../stores/notifications'

const store = useNotificationStore()
const open = ref(false)

onMounted(() => {
  store.fetch()
})

const handleClick = async (id) => {
  await store.markRead(id)
}
</script>
