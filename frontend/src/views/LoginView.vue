<template>
  <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-900 via-brand-900 to-slate-800 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
      <h1 class="text-2xl font-bold text-slate-900">FlowDesk Login</h1>
      <p class="mt-1 text-sm text-slate-500">Prijava u interni sustav zahtjeva.</p>

      <form class="mt-6 space-y-3" @submit.prevent="submit">
        <input v-model="form.email" class="input" type="email" placeholder="Email" required />
        <input v-model="form.password" class="input" type="password" placeholder="Password" required />
        <button class="btn-primary w-full" :disabled="loading">{{ loading ? 'Signing in...' : 'Sign in' }}</button>
      </form>
      <button class="mt-3 text-xs text-brand-600" :disabled="loading" @click="sendReset">
        Forgot password? Send reset link
      </button>

      <div class="mt-4 rounded-lg bg-slate-100 p-3 text-xs text-slate-600">
        <p><strong>Demo:</strong> admin@flowdesk.local / Password123!</p>
        <p>owner1@flowdesk.local / Password123!</p>
        <p>approver1@flowdesk.local / Password123!</p>
        <p>requester1@flowdesk.local / Password123!</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/auth'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'

const auth = useAuthStore()
const toast = useToastStore()
const router = useRouter()

const loading = ref(false)
const form = reactive({
  email: 'admin@flowdesk.local',
  password: 'Password123!',
})

const submit = async () => {
  loading.value = true

  try {
    await auth.login(form)
    toast.push('Uspješna prijava.', 'success')
    router.push('/')
  } catch (error) {
    toast.push(error.message || 'Login failed', 'error')
  } finally {
    loading.value = false
  }
}

const sendReset = async () => {
  try {
    await authService.forgotPassword(form.email)
    toast.push('Reset link sent (check Mailhog).', 'success')
  } catch {
    toast.push('Could not send reset email.', 'error')
  }
}
</script>
