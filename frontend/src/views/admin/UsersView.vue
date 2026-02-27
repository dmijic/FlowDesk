<template>
  <section class="space-y-4">
    <div class="card">
      <h2 class="text-lg font-semibold">Users Management</h2>
      <p class="text-sm text-slate-500">CRUD users + roles + departments.</p>
    </div>

    <div class="card grid gap-3 md:grid-cols-2">
      <input v-model="form.name" class="input" placeholder="Name" />
      <input v-model="form.email" class="input" placeholder="Email" />
      <input v-model="form.password" class="input" placeholder="Password (optional on edit)" />
      <select v-model="form.department_id" class="input">
        <option :value="null">No department</option>
        <option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</option>
      </select>
      <div class="md:col-span-2">
        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">Roles</label>
        <div class="flex flex-wrap gap-2">
          <label v-for="role in roles" :key="role" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">
            <input v-model="form.roles" :value="role" type="checkbox" class="mr-1" /> {{ role }}
          </label>
        </div>
      </div>
      <div class="md:col-span-2 flex gap-2">
        <button class="btn-primary" @click="save">{{ form.id ? 'Update' : 'Create' }}</button>
        <button v-if="form.id" class="btn-secondary" @click="reset">Cancel edit</button>
      </div>
    </div>

    <div class="card overflow-auto">
      <table class="w-full text-left text-sm">
        <thead>
          <tr class="border-b border-slate-200 text-xs uppercase text-slate-500">
            <th class="pb-2">Name</th>
            <th class="pb-2">Email</th>
            <th class="pb-2">Department</th>
            <th class="pb-2">Roles</th>
            <th class="pb-2"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id" class="border-b border-slate-100">
            <td class="py-2">{{ user.name }}</td>
            <td class="py-2">{{ user.email }}</td>
            <td class="py-2">{{ user.department?.name || '-' }}</td>
            <td class="py-2">{{ (user.roles || []).map((role) => role.slug).join(', ') }}</td>
            <td class="py-2 text-right">
              <button class="btn-secondary py-1" @click="edit(user)">Edit</button>
              <button class="ml-2 rounded-lg bg-rose-600 px-3 py-1 text-xs font-semibold text-white" @click="remove(user.id)">
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { api } from '../../services/api'
import { useToastStore } from '../../stores/toast'

const toast = useToastStore()
const users = ref([])
const departments = ref([])
const roles = ['admin', 'process-owner', 'approver', 'requester']

const form = reactive({
  id: null,
  name: '',
  email: '',
  password: '',
  department_id: null,
  roles: [],
})

const load = async () => {
  const [usersResponse, departmentsResponse] = await Promise.all([api('/users'), api('/departments')])
  users.value = usersResponse.data || []
  departments.value = departmentsResponse.data || []
}

const save = async () => {
  try {
    if (form.id) {
      await api(`/users/${form.id}`, {
        method: 'PUT',
        body: {
          ...form,
          password: form.password || undefined,
        },
      })
      toast.push('User updated.', 'success')
    } else {
      await api('/users', {
        method: 'POST',
        body: form,
      })
      toast.push('User created.', 'success')
    }

    reset()
    await load()
  } catch (error) {
    toast.push(error.message, 'error')
  }
}

const edit = (user) => {
  form.id = user.id
  form.name = user.name
  form.email = user.email
  form.password = ''
  form.department_id = user.department?.id || null
  form.roles = (user.roles || []).map((role) => role.slug)
}

const remove = async (id) => {
  if (!window.confirm('Delete user?')) return

  try {
    await api(`/users/${id}`, { method: 'DELETE' })
    toast.push('User deleted.', 'success')
    await load()
  } catch (error) {
    toast.push(error.message, 'error')
  }
}

const reset = () => {
  form.id = null
  form.name = ''
  form.email = ''
  form.password = ''
  form.department_id = null
  form.roles = []
}

onMounted(async () => {
  try {
    await load()
  } catch (error) {
    toast.push(error.message, 'error')
  }
})
</script>
