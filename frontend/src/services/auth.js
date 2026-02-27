import { http } from './http'

export const authService = {
  async csrf() {
    await http.get('/sanctum/csrf-cookie')
  },

  async login(email, password) {
    await authService.csrf()
    const { data } = await http.post('/auth/login', { email, password })
    return data
  },

  async me() {
    const { data } = await http.get('/me')
    return data
  },

  async logout() {
    await authService.csrf()
    await http.post('/auth/logout')
  },

  async forgotPassword(email) {
    await authService.csrf()
    const { data } = await http.post('/auth/forgot-password', { email })
    return data
  },
}
