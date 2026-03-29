<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Package } from 'lucide-vue-next'
import { useAuthStore } from '../stores/auth'
import authAPI from '../Services/authService'
import { getSafeErrorMessage } from '../lib/safeErrorMessage'
import Button from '../components/ui/Button.vue'

const router = useRouter()
const authStore = useAuthStore()

const mode = ref('login')
const formData = ref({
  name: '',
  email: '',
  password: '',
  passwordConfirmation: ''
})

const formErrors = ref({})
const showPassword = ref(false)
const showPasswordConfirmation = ref(false)
const submitting = ref(false)
const registerFeedback = ref('')
const registerSuccess = ref(false)

const validateForm = () => {
  const errors = {}

  if (mode.value === 'register' && !formData.value.name.trim()) {
    errors.name = 'Nome é obrigatório'
  }

  if (!formData.value.email) {
    errors.email = 'E-mail é obrigatório'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.value.email)) {
    errors.email = 'E-mail inválido'
  }

  if (!formData.value.password) {
    errors.password = 'Senha é obrigatória'
  } else if (formData.value.password.length < 6) {
    errors.password = 'Senha deve ter no mínimo 6 caracteres'
  }

  if (mode.value === 'register' && !formData.value.passwordConfirmation) {
    errors.passwordConfirmation = 'Confirmação de senha é obrigatória'
  } else if (mode.value === 'register' && formData.value.password !== formData.value.passwordConfirmation) {
    errors.passwordConfirmation = 'As senhas não coincidem'
  }

  formErrors.value = errors
  return Object.keys(errors).length === 0
}

const handleLogin = async () => {
  if (!validateForm()) return

  submitting.value = true
  registerFeedback.value = ''
  registerSuccess.value = false

  const success = await authStore.login(formData.value.email, formData.value.password)

  if (success) {
    router.push({ name: 'Dashboard' })
  }

  submitting.value = false
}

const handleRegister = async () => {
  if (!validateForm()) return

  submitting.value = true
  registerFeedback.value = ''
  registerSuccess.value = false
  authStore.clearError()

  try {
    await authAPI.register(
      formData.value.name.trim(),
      formData.value.email.trim(),
      formData.value.password,
      formData.value.passwordConfirmation
    )

    registerFeedback.value = 'Cadastro realizado com sucesso. Fazendo login...'
    registerSuccess.value = true

    const loginSuccess = await authStore.login(formData.value.email, formData.value.password)
    if (loginSuccess) {
      router.push({ name: 'Dashboard' })
      return
    }

    registerFeedback.value = 'Cadastro concluído. Faça login com suas credenciais.'
    mode.value = 'login'
    formData.value.password = ''
    formData.value.passwordConfirmation = ''
  } catch (err) {
    registerSuccess.value = false
    registerFeedback.value = getSafeErrorMessage(err, 'Nao foi possivel concluir o cadastro.')
  } finally {
    submitting.value = false
  }
}

const handleSubmit = async () => {
  if (mode.value === 'register') {
    await handleRegister()
    return
  }

  await handleLogin()
}

const switchMode = (nextMode) => {
  mode.value = nextMode
  formErrors.value = {}
  registerFeedback.value = ''
  registerSuccess.value = false
  authStore.clearError()
  formData.value.password = ''
  formData.value.passwordConfirmation = ''
}
</script>

<template>
  <div class="relative flex min-h-screen items-center justify-center overflow-x-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 px-4 py-10">
    <div class="pointer-events-none absolute -left-28 -top-28 h-72 w-72 rounded-full bg-blue-500/16 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 -right-24 h-80 w-80 rounded-full bg-cyan-400/10 blur-3xl"></div>

    <div class="relative z-10 w-full max-w-md space-y-8">
      <div class="text-center">
        <div class="mx-auto mb-5 flex h-24 w-24 items-center justify-center rounded-3xl bg-gradient-to-br from-blue-500 to-cyan-500 shadow-[0_0_28px_rgba(59,130,246,0.35)] ring-2 ring-blue-300/20">
          <Package class="h-12 w-12 text-white" />
        </div>
        <h1 class="text-4xl font-extrabold tracking-tight text-white">Estoque Pro</h1>
        <p class="mt-2 text-sm text-blue-100/80">Acesse o sistema para gerenciar produtos, entradas e saídas</p>
      </div>

      <div class="rounded-2xl border border-white/10 bg-slate-900/72 p-8 shadow-2xl backdrop-blur-md">
        <h2 class="mb-1 text-2xl font-bold text-white">
          {{ mode === 'login' ? 'Bem-vindo de volta' : 'Crie sua conta' }}
        </h2>
        <p class="mb-6 text-sm text-slate-300">
          {{ mode === 'login' ? 'Faça login para continuar' : 'Cadastre nome, e-mail e senha para acessar o sistema' }}
        </p>

        <div
          v-if="mode === 'login' && authStore.error"
          class="mb-4 rounded-lg border border-destructive/50 bg-destructive/10 p-3 text-sm text-destructive"
        >
          {{ authStore.error }}
        </div>

        <div
          v-if="registerFeedback"
          class="mb-4 rounded-lg border p-3 text-sm"
          :class="registerSuccess ? 'border-success/40 bg-success/10 text-success' : 'border-destructive/50 bg-destructive/10 text-destructive'"
        >
          {{ registerFeedback }}
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div v-if="mode === 'register'" class="space-y-2">
            <label class="block text-sm font-medium text-slate-200">Nome <span class="text-destructive">*</span></label>
            <input
              v-model="formData.name"
              type="text"
              placeholder="Seu nome completo"
              required
              class="flex h-11 w-full rounded-xl border border-slate-700 bg-slate-950/80 px-4 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
              :class="formErrors.name ? 'border-destructive focus:ring-destructive/30' : ''"
            />
            <p v-if="formErrors.name" class="text-xs text-destructive">
              {{ formErrors.name }}
            </p>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-200">E-mail <span class="text-destructive">*</span></label>
            <input
              v-model="formData.email"
              type="email"
              placeholder="seu@email.com"
              required
              class="flex h-11 w-full rounded-xl border border-slate-700 bg-slate-950/80 px-4 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
              :class="formErrors.email ? 'border-destructive focus:ring-destructive/30' : ''"
            />
            <p v-if="formErrors.email" class="text-xs text-destructive">
              {{ formErrors.email }}
            </p>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-200">Senha <span class="text-destructive">*</span></label>
            <div class="relative">
              <input
                v-model="formData.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="••••••••"
                class="flex h-11 w-full rounded-xl border border-slate-700 bg-slate-950/80 px-4 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                :class="formErrors.password ? 'border-destructive focus:ring-destructive/30' : ''"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition-colors hover:text-slate-200"
              >
                <svg
                  v-if="!showPassword"
                  class="h-5 w-5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg
                  v-else
                  class="h-5 w-5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-4.803m5.596-3.856a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1l22 22" />
                </svg>
              </button>
            </div>
            <p v-if="formErrors.password" class="text-xs text-destructive">
              {{ formErrors.password }}
            </p>
          </div>

          <div v-if="mode === 'register'" class="space-y-2">
            <label class="block text-sm font-medium text-slate-200">Confirmar senha <span class="text-destructive">*</span></label>
            <div class="relative">
              <input
                v-model="formData.passwordConfirmation"
                :type="showPasswordConfirmation ? 'text' : 'password'"
                placeholder="••••••••"
                class="flex h-11 w-full rounded-xl border border-slate-700 bg-slate-950/80 px-4 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                :class="formErrors.passwordConfirmation ? 'border-destructive focus:ring-destructive/30' : ''"
              />
              <button
                type="button"
                @click="showPasswordConfirmation = !showPasswordConfirmation"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition-colors hover:text-slate-200"
              >
                <svg
                  v-if="!showPasswordConfirmation"
                  class="h-5 w-5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg
                  v-else
                  class="h-5 w-5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-4.803m5.596-3.856a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1l22 22" />
                </svg>
              </button>
            </div>
            <p v-if="formErrors.passwordConfirmation" class="text-xs text-destructive">
              {{ formErrors.passwordConfirmation }}
            </p>
          </div>

          <Button
            type="submit"
            class="w-full bg-primary text-primary-foreground hover:opacity-95"
            :disabled="authStore.loading || submitting"
          >
            <span v-if="authStore.loading || submitting">
              {{ mode === 'login' ? 'Conectando...' : 'Criando conta...' }}
            </span>
            <span v-else>{{ mode === 'login' ? 'Entrar' : 'Cadastrar' }}</span>
          </Button>

          <button
            type="button"
            class="w-full text-center text-sm text-slate-300 transition-colors hover:text-white"
            @click="switchMode(mode === 'login' ? 'register' : 'login')"
          >
            {{ mode === 'login' ? 'Nao tem conta? Cadastre-se' : 'Ja tem conta? Entrar' }}
          </button>
        </form>
      </div>

      <p class="text-center text-xs text-slate-300/80">
        Sistema de segurança implementado com autenticação por token
      </p>
    </div>
  </div>
</template>
