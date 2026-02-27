import { defineStore } from 'pinia'

export const useToastStore = defineStore('toast', {
  state: () => ({
    items: [],
  }),
  actions: {
    push(message, type = 'info') {
      const id = crypto.randomUUID()
      this.items.push({ id, message, type })

      setTimeout(() => {
        this.items = this.items.filter((item) => item.id !== id)
      }, 3500)
    },
    remove(id) {
      this.items = this.items.filter((item) => item.id !== id)
    },
  },
})
