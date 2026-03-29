import api from '../composables/useApi'

const extractAuthPayload = (response = {}) => {
  const token =
    response?.token ||
    response?.access_token ||
    response?.data?.token ||
    response?.data?.access_token ||
    null

  const user = response?.user || response?.data?.user || null

  return { token, user }
}

export const authAPI = {
  login: async (email, password) => {
    const response = await api.post('/login', {
      email,
      password
    }, {
      skipAuth: true
    })

    return extractAuthPayload(response)
  },

  register: async (name, email, password, passwordConfirmation) => {
    const payload = {
      name,
      email,
      password,
      password_confirmation: passwordConfirmation || password
    }

    try {
      return await api.post('/register', payload, {
        skipAuth: true
      })
    } catch (err) {
      const status = err?.response?.status

      // Fallback for backends that expose user creation only via /users.
      if (status === 404 || status === 405) {
        return api.post('/users', payload, {
          skipAuth: true
        })
      }

      throw err
    }
  }
}

export default authAPI
