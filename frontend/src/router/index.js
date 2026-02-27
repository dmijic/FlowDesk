import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/LoginView.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/',
    name: 'dashboard',
    component: () => import('../views/DashboardView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/requests',
    name: 'requests',
    component: () => import('../views/requests/RequestsListView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/requests/create',
    name: 'requests-create',
    component: () => import('../views/requests/RequestCreateView.vue'),
    meta: { requiresAuth: true, permission: 'create_requests' },
  },
  {
    path: '/requests/:id',
    name: 'requests-detail',
    component: () => import('../views/requests/RequestDetailView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/approvals',
    name: 'approvals',
    component: () => import('../views/approvals/ApprovalsInboxView.vue'),
    meta: { requiresAuth: true, permission: 'approve_requests' },
  },
  {
    path: '/admin/users',
    name: 'admin-users',
    component: () => import('../views/admin/UsersView.vue'),
    meta: { requiresAuth: true, permission: 'manage_users' },
  },
  {
    path: '/admin/request-types',
    name: 'admin-request-types',
    component: () => import('../views/admin/RequestTypesView.vue'),
    meta: { requiresAuth: true, permission: 'manage_workflows' },
  },
  {
    path: '/admin/workflows',
    name: 'admin-workflows',
    component: () => import('../views/admin/WorkflowsView.vue'),
    meta: { requiresAuth: true, permission: 'manage_workflows' },
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('../views/NotFoundView.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  const toast = useToastStore()

  if (!auth.initialized) {
    await auth.fetchMe()
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login' }
  }

  if (to.meta.guestOnly && auth.isAuthenticated) {
    return { name: 'dashboard' }
  }

  if (to.meta.permission && !auth.hasPermission(to.meta.permission)) {
    toast.push('Nemaš dozvolu za ovu stranicu.', 'error')
    return { name: 'dashboard' }
  }

  return true
})

export default router
