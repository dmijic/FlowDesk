<template>
  <section class="space-y-4">
    <div class="card">
      <h2 class="text-lg font-semibold">Workflow Definitions</h2>
      <p class="text-sm text-slate-500">JSON editor + step preview.</p>
    </div>

    <div class="card space-y-3">
      <div class="grid gap-3 md:grid-cols-2">
        <select v-model="form.request_type_id" class="input">
          <option :value="null">Select request type</option>
          <option v-for="type in requestTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
        </select>
        <input v-model="form.name" class="input" placeholder="Workflow name" />
        <input v-model.number="form.version" class="input" min="1" type="number" placeholder="Version" />
        <label class="flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-sm">
          <input v-model="form.is_active" type="checkbox" /> Active
        </label>
      </div>

      <textarea v-model="definitionText" class="input min-h-48 font-mono text-xs"></textarea>

      <div class="rounded-lg bg-slate-50 p-3 text-xs">
        <p class="font-semibold text-slate-700">Preview Steps</p>
        <ul class="mt-2 space-y-1 text-slate-600">
          <li v-for="step in parsedSteps" :key="step.step_key">
            {{ step.step_key }} / {{ step.step_name }} / rule={{ step.rule }} / parallel={{ step.parallel }}
          </li>
        </ul>
      </div>

      <div class="flex gap-2">
        <button class="btn-primary" @click="save">{{ form.id ? 'Update' : 'Create' }}</button>
        <button v-if="form.id" class="btn-secondary" @click="reset">Cancel</button>
      </div>
    </div>

    <div class="card overflow-auto">
      <table class="w-full text-left text-sm">
        <thead>
          <tr class="border-b border-slate-200 text-xs uppercase text-slate-500">
            <th class="pb-2">Type</th>
            <th class="pb-2">Name</th>
            <th class="pb-2">Version</th>
            <th class="pb-2">Active</th>
            <th class="pb-2"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="workflow in items" :key="workflow.id" class="border-b border-slate-100">
            <td class="py-2">{{ workflow.request_type?.name }}</td>
            <td class="py-2">{{ workflow.name }}</td>
            <td class="py-2">v{{ workflow.version }}</td>
            <td class="py-2">{{ workflow.is_active ? 'Yes' : 'No' }}</td>
            <td class="py-2 text-right">
              <button class="btn-secondary py-1" @click="edit(workflow)">Edit</button>
              <button class="ml-2 rounded-lg bg-rose-600 px-3 py-1 text-xs font-semibold text-white" @click="remove(workflow.id)">
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
import { computed, onMounted, reactive, ref } from 'vue'
import { api } from '../../services/api'
import { useToastStore } from '../../stores/toast'

const toast = useToastStore()
const items = ref([])
const requestTypes = ref([])

const defaultDefinition = {
  steps: [
    {
      step_key: 'manager-review',
      step_name: 'Manager Review',
      approvers: [{ by_role: 'Approver' }],
      parallel: true,
      rule: 'any',
    },
    {
      step_key: 'owner-signoff',
      step_name: 'Process Owner Signoff',
      approvers: [{ by_role: 'ProcessOwner' }],
      parallel: false,
      rule: 'all',
    },
  ],
}

const form = reactive({
  id: null,
  request_type_id: null,
  name: '',
  version: 1,
  is_active: true,
})

const definitionText = ref(JSON.stringify(defaultDefinition, null, 2))

const parsedSteps = computed(() => {
  try {
    const parsed = JSON.parse(definitionText.value)
    return parsed.steps || []
  } catch {
    return []
  }
})

const load = async () => {
  const [workflowResponse, typeResponse] = await Promise.all([api('/workflows'), api('/request-types')])
  items.value = workflowResponse.data || []
  requestTypes.value = typeResponse.data || []
}

const save = async () => {
  let parsed

  try {
    parsed = JSON.parse(definitionText.value)
  } catch {
    toast.push('Invalid JSON definition.', 'error')
    return
  }

  const payload = {
    ...form,
    definition_json: parsed,
  }

  try {
    if (form.id) {
      await api(`/workflows/${form.id}`, {
        method: 'PUT',
        body: payload,
      })
      toast.push('Workflow updated.', 'success')
    } else {
      await api('/workflows', {
        method: 'POST',
        body: payload,
      })
      toast.push('Workflow created.', 'success')
    }

    reset()
    await load()
  } catch (error) {
    toast.push(error.message, 'error')
  }
}

const edit = (workflow) => {
  form.id = workflow.id
  form.request_type_id = workflow.request_type_id
  form.name = workflow.name
  form.version = workflow.version
  form.is_active = workflow.is_active
  definitionText.value = JSON.stringify(workflow.definition_json, null, 2)
}

const remove = async (id) => {
  if (!window.confirm('Delete workflow?')) return

  try {
    await api(`/workflows/${id}`, { method: 'DELETE' })
    toast.push('Workflow deleted.', 'success')
    await load()
  } catch (error) {
    toast.push(error.message, 'error')
  }
}

const reset = () => {
  form.id = null
  form.request_type_id = null
  form.name = ''
  form.version = 1
  form.is_active = true
  definitionText.value = JSON.stringify(defaultDefinition, null, 2)
}

onMounted(async () => {
  try {
    await load()
  } catch (error) {
    toast.push(error.message, 'error')
  }
})
</script>
