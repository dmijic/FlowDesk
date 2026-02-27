<template>
  <section class="space-y-4">
    <div class="card">
      <h2 class="text-lg font-semibold">Request Types</h2>
      <p class="text-sm text-slate-500">Basic CRUD for internal request categories.</p>
    </div>

    <div class="card grid gap-3 md:grid-cols-2">
      <input v-model="form.name" class="input" placeholder="Name" />
      <input v-model="form.description" class="input" placeholder="Description" />
      <div class="md:col-span-2 flex gap-2">
        <button class="btn-primary" @click="save">{{ form.id ? 'Update' : 'Create' }}</button>
        <button v-if="form.id" class="btn-secondary" @click="reset">Cancel</button>
      </div>
    </div>

    <div class="card overflow-auto">
      <table class="w-full text-left text-sm">
        <thead>
          <tr class="border-b border-slate-200 text-xs uppercase text-slate-500">
            <th class="pb-2">Name</th>
            <th class="pb-2">Description</th>
            <th class="pb-2"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="type in items" :key="type.id" class="border-b border-slate-100">
            <td class="py-2">{{ type.name }}</td>
            <td class="py-2">{{ type.description || '-' }}</td>
            <td class="py-2 text-right">
              <button class="btn-secondary py-1" @click="edit(type)">Edit</button>
              <button class="ml-2 rounded-lg bg-rose-600 px-3 py-1 text-xs font-semibold text-white" @click="remove(type.id)">
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
const items = ref([])
const form = reactive({
  id: null,
  name: '',
  description: '',
})

const load = async () => {
  const response = await api('/request-types')
  items.value = response.data || []
}

const save = async () => {
  try {
    if (form.id) {
      await api(`/request-types/${form.id}`, {
        method: 'PUT',
        body: form,
      })
      toast.push('Request type updated.', 'success')
    } else {
      await api('/request-types', {
        method: 'POST',
        body: form,
      })
      toast.push('Request type created.', 'success')
    }

    reset()
    await load()
  } catch (error) {
    toast.push(error.message, 'error')
  }
}

const edit = (type) => {
  form.id = type.id
  form.name = type.name
  form.description = type.description || ''
}

const remove = async (id) => {
  if (!window.confirm('Delete request type?')) return

  try {
    await api(`/request-types/${id}`, { method: 'DELETE' })
    toast.push('Request type deleted.', 'success')
    await load()
  } catch (error) {
    toast.push(error.message, 'error')
  }
}

const reset = () => {
  form.id = null
  form.name = ''
  form.description = ''
}

onMounted(async () => {
  try {
    await load()
  } catch (error) {
    toast.push(error.message, 'error')
  }
})
</script>
