<template>
  <div class="min-h-screen bg-slate-100">
    <header class="border-b border-slate-200 bg-white">
      <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
        <div>
          <h1 class="text-xl font-bold text-slate-900">FlowDesk</h1>
          <p class="text-xs text-slate-500">Internal Requests & Approvals</p>
        </div>
        <div class="flex items-center gap-3">
          <NotificationBell />
          <div class="text-right text-xs">
            <p class="font-semibold text-slate-700">{{ auth.user?.name }}</p>
            <p class="text-slate-500">{{ auth.user?.email }}</p>
          </div>
          <button class="btn-secondary" @click="signOut">Logout</button>
        </div>
      </div>
    </header>

    <div class="mx-auto grid max-w-7xl grid-cols-1 gap-4 px-4 py-4 lg:grid-cols-[240px,1fr]">
      <aside class="card h-fit">
        <nav class="space-y-1 text-sm">
          <RouterLink class="block rounded-lg px-3 py-2 hover:bg-slate-100" to="/">Dashboard</RouterLink>
          <RouterLink class="block rounded-lg px-3 py-2 hover:bg-slate-100" to="/requests">Requests</RouterLink>
          <RouterLink
            v-if="auth.hasPermission('create_requests')"
            class="block rounded-lg px-3 py-2 hover:bg-slate-100"
            to="/requests/create"
          >
            New Request
          </RouterLink>
          <RouterLink
            v-if="auth.hasPermission('approve_requests')"
            class="block rounded-lg px-3 py-2 hover:bg-slate-100"
            to="/approvals"
          >
            Approvals Inbox
          </RouterLink>
          <RouterLink
            v-if="auth.hasPermission('manage_users')"
            class="block rounded-lg px-3 py-2 hover:bg-slate-100"
            to="/admin/users"
          >
            Users
          </RouterLink>
          <RouterLink
            v-if="auth.hasPermission('manage_workflows')"
            class="block rounded-lg px-3 py-2 hover:bg-slate-100"
            to="/admin/request-types"
          >
            Request Types
          </RouterLink>
          <RouterLink
            v-if="auth.hasPermission('manage_workflows')"
            class="block rounded-lg px-3 py-2 hover:bg-slate-100"
            to="/admin/workflows"
          >
            Workflows
          </RouterLink>
        </nav>
      </aside>

      <main>
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import NotificationBell from '../components/NotificationBell.vue'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()

const signOut = async () => {
  await auth.logout()
  router.push('/login')
}
</script>
