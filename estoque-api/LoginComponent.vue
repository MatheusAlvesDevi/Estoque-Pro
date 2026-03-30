<template>
  <div class="login-container">
    <!-- Formulário de Login -->
    <form @submit.prevent="handleLogin" v-if="!isLogged" class="login-form">
      <div class="login-brand">
        <img :src="APP_LOGO" alt="Estoque Pro" class="login-logo" />
      </div>
      <h2>Estoque Pro - Login</h2>

      <!-- Email Input -->
      <div class="form-group">
        <label for="email">Email:</label>
        <input
          v-model="credentials.email"
          type="email"
          id="email"
          placeholder="seu.email@example.com"
          required
        />
      </div>

      <!-- Password Input -->
      <div class="form-group">
        <label for="password">Senha:</label>
        <input
          v-model="credentials.password"
          type="password"
          id="password"
          placeholder="Sua senha"
          required
        />
      </div>

      <!-- Error Message -->
      <div v-if="error" class="error-message">
        {{ error }}
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading">Autenticando...</div>

      <!-- Submit Button -->
      <button
        type="submit"
        :disabled="loading"
        class="btn-login"
      >
        {{ loading ? 'Processando...' : 'Entrar' }}
      </button>
    </form>

    <!-- Área de Acesso Autorizado -->
    <div v-else class="dashboard">
      <div class="user-info">
        <h2>Bem-vindo, {{ user.name }}!</h2>
        <p>Email: {{ user.email }}</p>
        <p>ID: {{ user.id }}</p>
      </div>

      <!-- Dashboard Content -->
      <div class="dashboard-content" v-if="products.length > 0">
        <h3>Produtos Carregados (Acesso Protegido)</h3>
        <p>Total de produtos: {{ productsCount }}</p>
        <ul>
          <li v-for="product in products.slice(0, 3)" :key="product.id">
            {{ product.name }} - R$ {{ product.price }}
          </li>
        </ul>
        <p v-if="products.length > 3">... e mais {{ products.length - 3 }} produtos</p>
      </div>

      <!-- Logout Button -->
      <button @click="handleLogout" class="btn-logout">
        {{ loading ? 'Processando...' : 'Sair' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const APP_LOGO = '/favicon.ico'

// Estado
const credentials = ref({
  email: 'joao.silva@example.com',
  password: 'password'
})

const user = ref(null)
const token = ref(localStorage.getItem('authToken') || null)
const isLogged = ref(!!token.value)
const error = ref('')
const loading = ref(false)
const products = ref([])
const productsCount = ref(0)

const setFavicon = (iconHref) => {
  let icon16 = document.querySelector('link[rel="icon"][sizes="16x16"]')
  let icon32 = document.querySelector('link[rel="icon"][sizes="32x32"]')

  if (!icon16) {
    icon16 = document.createElement('link')
    icon16.rel = 'icon'
    icon16.sizes = '16x16'
    document.head.appendChild(icon16)
  }

  if (!icon32) {
    icon32 = document.createElement('link')
    icon32.rel = 'icon'
    icon32.sizes = '32x32'
    document.head.appendChild(icon32)
  }

  icon16.href = iconHref
  icon32.href = iconHref
}

// API base
const API_BASE = 'http://127.0.0.1:8000/api'

// Headers com token
const getHeaders = () => ({
  Authorization: `Bearer ${token.value}`,
  Accept: 'application/json',
  'Content-Type': 'application/json'
})

// Função: Login
const handleLogin = async () => {
  error.value = ''
  loading.value = true

  try {
    const response = await axios.post(
      `${API_BASE}/login`,
      {
        email: credentials.value.email,
        password: credentials.value.password
      },
      {
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json'
        }
      }
    )

    // Salvar token e usuário
    token.value = response.data.token
    user.value = response.data.user
    isLogged.value = true
    localStorage.setItem('authToken', token.value)

    // Carregar dados protegidos
    await loadProtectedData()

    error.value = ''
  } catch (err) {
    error.value =
      err.response?.data?.message || 'Erro ao fazer login. Verifique suas credenciais.'
    console.error('Login error:', err)
  } finally {
    loading.value = false
  }
}

// Função: Carregar dados protegidos
const loadProtectedData = async () => {
  try {
    const response = await axios.get(
      `${API_BASE}/products`,
      { headers: getHeaders() }
    )
    products.value = response.data.data || response.data
    productsCount.value = products.value.length
  } catch (err) {
    console.error('Erro ao carregar produtos:', err)
  }
}

// Função: Logout
const handleLogout = async () => {
  loading.value = true

  try {
    await axios.post(
      `${API_BASE}/logout`,
      {},
      { headers: getHeaders() }
    )

    // Limpar estado
    token.value = null
    user.value = null
    isLogged.value = false
    products.value = []
    credentials.value = {
      email: 'joao.silva@example.com',
      password: 'password'
    }
    localStorage.removeItem('authToken')

    error.value = ''
  } catch (err) {
    error.value = 'Erro ao fazer logout'
    console.error('Logout error:', err)
  } finally {
    loading.value = false
  }
}

// Verificar se há token ao montar o componente
onMounted(() => {
  setFavicon(APP_LOGO)

  if (isLogged.value) {
    loadProtectedData()
  }
})
</script>

<style scoped>
.login-container {
  max-width: 500px;
  margin: 50px auto;
  padding: 20px;
  font-family: Arial, sans-serif;
}

/* Formulário de Login */
.login-form {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 30px;
  background-color: #f9f9f9;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.login-brand {
  display: flex;
  justify-content: center;
  margin-bottom: 12px;
}

.login-logo {
  width: 64px;
  height: 64px;
  object-fit: contain;
}

.login-form h2 {
  text-align: center;
  color: #333;
  margin-bottom: 25px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
  color: #555;
}

.form-group input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 14px;
  box-sizing: border-box;
  transition: border-color 0.3s;
}

.form-group input:focus {
  outline: none;
  border-color: #4CAF50;
  box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
}

.error-message {
  background-color: #ffebee;
  color: #c62828;
  padding: 12px;
  border-radius: 4px;
  margin-bottom: 15px;
  border-left: 4px solid #c62828;
}

.loading {
  text-align: center;
  color: #666;
  padding: 10px;
  font-style: italic;
}

.btn-login {
  width: 100%;
  padding: 12px;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s;
  margin-top: 10px;
}

.btn-login:hover:not(:disabled) {
  background-color: #45a049;
}

.btn-login:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

/* Dashboard */
.dashboard {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 30px;
  background-color: #f9f9f9;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.user-info {
  background-color: #e8f5e9;
  padding: 20px;
  border-radius: 4px;
  margin-bottom: 20px;
}

.user-info h2 {
  color: #2e7d32;
  margin: 0 0 10px 0;
}

.user-info p {
  color: #555;
  margin: 5px 0;
}

.dashboard-content {
  background-color: white;
  padding: 20px;
  border-radius: 4px;
  margin-bottom: 20px;
}

.dashboard-content h3 {
  color: #333;
  margin-top: 0;
}

.dashboard-content ul {
  list-style: none;
  padding: 0;
}

.dashboard-content li {
  padding: 10px;
  border-bottom: 1px solid #eee;
  color: #555;
}

.dashboard-content li:last-child {
  border-bottom: none;
}

.btn-logout {
  width: 100%;
  padding: 12px;
  background-color: #f44336;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-logout:hover:not(:disabled) {
  background-color: #da190b;
}

.btn-logout:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}
</style>
