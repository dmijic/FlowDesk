import { http } from './http'

function normalizePath(path) {
  if (path.startsWith('/api') || path.startsWith('/auth') || path.startsWith('/sanctum') || path === '/me') {
    return path
  }

  return `/api${path}`
}

export async function api(path, options = {}) {
  try {
    const method = (options.method || 'GET').toLowerCase()
    const url = normalizePath(path)

    const config = {
      url,
      method,
      headers: {
        ...(options.headers || {}),
      },
    }

    if (Object.prototype.hasOwnProperty.call(options, 'body')) {
      config.data = options.body
    }

    const response = await http.request(config)
    return response.data ?? null
  } catch (error) {
    const status = error.response?.status
    const payload = error.response?.data
    const message = payload?.message || error.message || 'API request failed'

    const wrapped = new Error(message)
    wrapped.status = status
    wrapped.payload = payload
    throw wrapped
  }
}

export function fileDownloadUrl(attachmentId) {
  const base = (import.meta.env.VITE_BACKEND_URL || 'http://localhost').replace(/\/$/, '')
  return `${base}/api/attachments/${attachmentId}/download`
}
