<template>
  <section class="space-y-4">
    <div class="card">
      <h2 class="text-lg font-semibold">Create Request Wizard</h2>
      <p class="text-sm text-slate-500">type -> details -> attachments -> review -> submit</p>
    </div>

    <div class="card">
      <div class="mb-4 flex flex-wrap gap-2 text-xs">
        <span v-for="s in steps" :key="s" class="rounded-full px-3 py-1" :class="stepClass(s)">Step {{ s }}</span>
      </div>

      <div v-if="step === 1" class="space-y-3">
        <label class="text-sm font-semibold">Request Type</label>
        <select v-model="form.type_id" class="input">
          <option :value="null">Select type</option>
          <option v-for="type in requestTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
        </select>
      </div>

      <div v-if="step === 2" class="grid gap-3 md:grid-cols-2">
        <div class="md:col-span-2">
          <label class="text-sm font-semibold">Title</label>
          <input v-model="form.title" class="input" />
        </div>
        <div class="md:col-span-2">
          <label class="text-sm font-semibold">Description</label>
          <textarea v-model="form.description" class="input min-h-32"></textarea>
        </div>
        <div>
          <label class="text-sm font-semibold">Department</label>
          <select v-model="form.department_id" class="input">
            <option :value="null">Select department</option>
            <option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</option>
          </select>
        </div>
        <div>
          <label class="text-sm font-semibold">Priority</label>
          <select v-model="form.priority" class="input">
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
          </select>
        </div>
      </div>

      <div v-if="step === 3" class="space-y-3">
        <label class="text-sm font-semibold">Attachments</label>
        <input type="file" multiple @change="setFiles" />
        <ul class="space-y-1 text-sm text-slate-600">
          <li v-for="file in files" :key="file.name + file.size">{{ file.name }} ({{ Math.round(file.size / 1024) }} KB)</li>
        </ul>
      </div>

      <div v-if="step === 4" class="space-y-2 text-sm">
        <p><strong>Type:</strong> {{ selectedType?.name }}</p>
        <p><strong>Title:</strong> {{ form.title }}</p>
        <p><strong>Department:</strong> {{ selectedDepartment?.name }}</p>
        <p><strong>Priority:</strong> {{ form.priority }}</p>
        <p><strong>Attachments:</strong> {{ files.length }}</p>
      </div>

      <div class="mt-4 flex items-center justify-between">
        <button class="btn-secondary" :disabled="step === 1" @click="step--">Back</button>

        <div class="flex gap-2">
          <button v-if="step < 4" class="btn-primary" @click="step++">Next</button>
          <button v-if="step === 4" class="btn-secondary" :disabled="loading" @click="save(false)">
            Save Draft
          </button>
          <button v-if="step === 4" class="btn-primary" :disabled="loading" @click="save(true)">
            {{ loading ? 'Submitting...' : 'Submit' }}
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '../../services/api'
import { useAuthStore } from '../../stores/auth'
import { useToastStore } from '../../stores/toast'

const router = useRouter()
const auth = useAuthStore()
const toast = useToastStore()

const steps = [1, 2, 3, 4]
const step = ref(1)
const loading = ref(false)
const requestTypes = ref([])
const departments = ref([])
const files = ref([])

const form = reactive({
  type_id: null,
  title: '',
  description: '',
  department_id: auth.user?.department?.id || null,
  priority: 'medium',
})

const selectedType = computed(() => requestTypes.value.find((type) => type.id === form.type_id))
const selectedDepartment = computed(() => departments.value.find((department) => department.id === form.department_id))

const stepClass = (index) => (step.value >= index ? 'bg-brand-600 text-white' : 'bg-slate-200 text-slate-600')

const setFiles = (event) => {
  files.value = Array.from(event.target.files || [])
}

const save = async (submitAfterCreate) => {
  loading.value = true

  try {
    const created = await api('/requests', {
      method: 'POST',
      body: form,
    })

    const id = created.data.id

    for (const file of files.value) {
      const payload = new FormData()
      payload.append('file', file)
      await api(`/requests/${id}/attachments`, {
        method: 'POST',
        body: payload,
      })
    }

    if (submitAfterCreate) {
      await api(`/requests/${id}/submit`, { method: 'POST' })
    }

    toast.push(submitAfterCreate ? 'Request submitted.' : 'Draft saved.', 'success')
    router.push(`/requests/${id}`)
  } catch (error) {
    toast.push(error.message, 'error')
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  const [typesResponse, departmentsResponse] = await Promise.all([api('/request-types'), api('/departments')])
  requestTypes.value = typesResponse.data || []
  departments.value = departmentsResponse.data || []
})
</script>
