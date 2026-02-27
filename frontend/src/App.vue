<template>
  <AppLayout v-if="auth.isAuthenticated && route.name !== 'login'">
    <RouterView />
  </AppLayout>
  <RouterView v-else />
  <ToastStack />
</template>

<script setup>
import { useRoute } from 'vue-router'
import AppLayout from './layouts/AppLayout.vue'
import ToastStack from './components/ToastStack.vue'
import { useAuthStore } from './stores/auth'

const route = useRoute()
const auth = useAuthStore()
</script>
