import { ref } from 'vue'
import axios from 'axios'
import { getSafeErrorMessage } from '../lib/safeErrorMessage'
import { useAuthStore } from '../stores/auth'

const API_BASE_URL = 'http://127.0.0.1:8000/api'

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

let isRedirectingToLogin = false

// Injects the bearer token on authenticated requests.
api.interceptors.request.use((config) => {
  if (config?.skipAuth === true) {
    return config
  }

  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Centralized 401 handling to keep auth state and navigation consistent.
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error?.response?.status
    const requestUrl = error?.config?.url || ''
    const isLoginRequest = requestUrl.includes('/login')
    const skipAuth = error?.config?.skipAuth === true

    if (status === 401 && !isLoginRequest && !skipAuth) {
      // Token is no longer valid for protected resources.
      localStorage.removeItem('auth_token')

      // Mirror the token cleanup in Pinia to prevent guard/interceptor loops.
      try {
        const authStore = useAuthStore()
        authStore.logout()
      } catch (storeError) {
        console.warn('Nao foi possivel limpar o estado de autenticacao no store:', storeError)
      }

      // Avoid duplicate redirects while forcing a clean transition to login.
      if (typeof window !== 'undefined' && !isRedirectingToLogin && window.location.pathname !== '/login') {
        isRedirectingToLogin = true
        window.location.replace('/login')

        setTimeout(() => {
          isRedirectingToLogin = false
        }, 1500)
      }
    }
    return Promise.reject(error)
  }
)

export function useApi() {
  const loading = ref(false)
  const error = ref(null)

  const request = async (method, url, data = null) => {
    loading.value = true
    error.value = null

    try {
      const response = await api[method](url, data)
      return response.data
    } catch (err) {
      error.value = getSafeErrorMessage(err, 'Nao foi possivel concluir a requisicao.')
      throw err
    } finally {
      loading.value = false
    }
  }

  const get = (url) => request('get', url)
  const post = (url, data) => request('post', url, data)
  const put = (url, data) => request('put', url, data)
  const del = (url) => request('delete', url)

  return {
    loading,
    error,
    get,
    post,
    put,
    del,
    api
  }
}

export default api;

// Development-only fallback dataset.
export const mockData = {
  products: [
    { id: 1, name: 'Notebook Dell Inspiron', code: 'NB-001', price: 3500.00, quantity: 15, minStock: 5 },
    { id: 2, name: 'Mouse Logitech MX Master', code: 'MS-002', price: 450.00, quantity: 32, minStock: 10 },
    { id: 3, name: 'Teclado Mecânico RGB', code: 'TC-003', price: 280.00, quantity: 3, minStock: 8 },
    { id: 4, name: 'Monitor LG 27"', code: 'MT-004', price: 1200.00, quantity: 8, minStock: 5 },
    { id: 5, name: 'Webcam Logitech C920', code: 'WC-005', price: 380.00, quantity: 2, minStock: 5 },
    { id: 6, name: 'Headset HyperX Cloud', code: 'HS-006', price: 520.00, quantity: 18, minStock: 8 },
    { id: 7, name: 'SSD Samsung 1TB', code: 'SD-007', price: 650.00, quantity: 25, minStock: 10 },
    { id: 8, name: 'Memória RAM 16GB', code: 'MR-008', price: 320.00, quantity: 40, minStock: 15 },
  ],
  suppliers: [
    { id: 1, name: 'Tech Distribuidora', email: 'contato@techdist.com', phone: '(11) 98765-4321', address: 'São Paulo, SP' },
    { id: 2, name: 'Info Import', email: 'vendas@infoimport.com', phone: '(21) 97654-3210', address: 'Rio de Janeiro, RJ' },
    { id: 3, name: 'Digital Supply', email: 'comercial@digitalsupply.com', phone: '(31) 96543-2109', address: 'Belo Horizonte, MG' },
  ],
  entries: [
    { id: 1, productId: 1, productName: 'Notebook Dell Inspiron', supplierId: 1, supplierName: 'Tech Distribuidora', quantity: 10, userId: 1, userName: 'Admin', date: '2026-03-14' },
    { id: 2, productId: 2, productName: 'Mouse Logitech MX Master', supplierId: 2, supplierName: 'Info Import', quantity: 20, userId: 1, userName: 'Admin', date: '2026-03-13' },
    { id: 3, productId: 7, productName: 'SSD Samsung 1TB', supplierId: 1, supplierName: 'Tech Distribuidora', quantity: 15, userId: 2, userName: 'João', date: '2026-03-12' },
  ],
  exits: [
    { id: 1, productId: 1, productName: 'Notebook Dell Inspiron', quantity: 3, userId: 1, userName: 'Admin', date: '2026-03-14' },
    { id: 2, productId: 3, productName: 'Teclado Mecânico RGB', quantity: 5, userId: 2, userName: 'João', date: '2026-03-13' },
    { id: 3, productId: 5, productName: 'Webcam Logitech C920', quantity: 2, userId: 1, userName: 'Admin', date: '2026-03-12' },
  ],
  users: [
    { id: 1, name: 'Admin', email: 'admin@email.com', role: 'Administrador', status: 'Ativo' },
    { id: 2, name: 'João Silva', email: 'joao@email.com', role: 'Operador', status: 'Ativo' },
    { id: 3, name: 'Maria Santos', email: 'maria@email.com', role: 'Operador', status: 'Inativo' },
  ]
}
