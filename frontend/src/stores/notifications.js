import { defineStore } from 'pinia'
import { api } from '../services/api'

export const useNotificationStore = defineStore('notifications', {
  state: () => ({
    unreadCount: 0,
    items: [],
    loading: false,
  }),
  actions: {
    async fetch() {
      this.loading = true

      try {
        const data = await api('/notifications')
        this.unreadCount = data.unread_count || 0
        this.items = (data.notifications?.data || []).slice(0, 10)
      } finally {
        this.loading = false
      }
    },

    async markRead(id) {
      await api(`/notifications/${id}/read`, { method: 'POST' })
      this.items = this.items.map((item) => (item.id === id ? { ...item, read_at: new Date().toISOString() } : item))
      this.unreadCount = this.items.filter((item) => !item.read_at).length
    },
  },
})
