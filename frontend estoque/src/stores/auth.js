import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import authAPI from '../Services/authService'
import { getSafeErrorMessage } from '../lib/safeErrorMessage'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token') || null)
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!token.value)

  const setToken = (newToken) => {
    token.value = newToken
    if (newToken) {
      localStorage.setItem('auth_token', newToken)
    } else {
      localStorage.removeItem('auth_token')
    }
  }

  const setUser = (newUser) => {
    user.value = newUser
  }

  const login = async (email, password) => {
    loading.value = true
    error.value = null
    setToken(null)
    setUser(null)

    try {
      const response = await authAPI.login(email, password)

      const receivedToken = response?.token

      if (!receivedToken) {
        throw new Error('Nenhum token retornado pelo servidor.')
      }

      setToken(receivedToken)
      setUser(response?.user || { email })

      return true
    } catch (err) {
      console.error('Erro ao fazer login:', err)

      const status = err?.response?.status
      const backendMessage = err?.response?.data?.message || ''
      const emailErrors = err?.response?.data?.errors?.email
      const firstEmailError = Array.isArray(emailErrors) ? emailErrors[0] : ''
      const credentialsErrorText = `${backendMessage} ${firstEmailError}`.toLowerCase()

      if (status === 401) {
        error.value = 'Credenciais invalidas. Tente novamente.'
      } else if (status === 422 && credentialsErrorText.includes('credenciais')) {
        error.value = 'Credenciais invalidas. Verifique email e senha.'
      } else if (status === 422) {
        error.value = firstEmailError || backendMessage || 'Dados de login invalidos. Verifique os campos e tente novamente.'
      } else {
        error.value = getSafeErrorMessage(err, 'Falha ao fazer login. Tente novamente.')
      }

      return false
    } finally {
      loading.value = false
    }
  }

  const logout = () => {
    setToken(null)
    setUser(null)
    error.value = null
  }

  const clearError = () => {
    error.value = null
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    setToken,
    setUser,
    login,
    logout,
    clearError
  }
})
