import axios from 'axios'

export const http = axios.create({
  baseURL: import.meta.env.VITE_BACKEND_URL || 'http://localhost',
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN',
})
