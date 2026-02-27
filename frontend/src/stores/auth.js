import { defineStore } from 'pinia'
import { authService } from '../services/auth'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    permissions: [],
    initialized: false,
  }),
  getters: {
    isAuthenticated: (state) => Boolean(state.user),
  },
  actions: {
    async login(payload) {
      await authService.login(payload.email, payload.password)
      const data = await authService.me()

      this.user = data.user.data
      this.permissions = data.permissions || []
      this.initialized = true
    },

    async fetchMe() {
      try {
        const data = await authService.me()
        this.user = data.user.data
        this.permissions = data.permissions || []
      } catch {
        this.user = null
        this.permissions = []
      } finally {
        this.initialized = true
      }
    },

    hasPermission(permission) {
      return this.permissions.includes(permission)
    },

    async logout() {
      try {
        await authService.logout()
      } finally {
        this.logoutLocal()
      }
    },

    logoutLocal() {
      this.user = null
      this.permissions = []
      this.initialized = true
    },
  },
})
