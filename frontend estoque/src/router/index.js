import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/LoginView.vue'),
    meta: { requiresAuth: false, title: 'Login' }
  },
  {
    path: '/',
    name: 'Dashboard',
    alias: ['/dashboard', '/home'],
    component: () => import('../views/Dashboard.vue'),
    meta: { requiresAuth: true, title: 'Dashboard' }
  },
  {
    path: '/products',
    name: 'Products',
    component: () => import('../views/Products.vue'),
    meta: { requiresAuth: true, title: 'Produtos' }
  },
  {
    path: '/suppliers',
    name: 'Suppliers',
    component: () => import('../views/Suppliers.vue'),
    meta: { requiresAuth: true, title: 'Fornecedores' }
  },
  {
    path: '/stock-entry',
    name: 'StockEntry',
    component: () => import('../views/StockEntry.vue'),
    meta: { requiresAuth: true, title: 'Entrada de Estoque' }
  },
  {
    path: '/stock-exit',
    name: 'StockExit',
    component: () => import('../views/StockExit.vue'),
    meta: { requiresAuth: true, title: 'Saida de Estoque' }
  },
  {
    path: '/users',
    name: 'Users',
    component: () => import('../views/Users.vue'),
    meta: { requiresAuth: true, title: 'Usuarios' }
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Enforces access control and handles auth-only redirects.
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  const requiresAuth = to.meta.requiresAuth ?? true

  if (requiresAuth && !authStore.isAuthenticated) {
    // Anonymous user trying to access a protected route.
    next({ name: 'Login', query: { redirect: to.fullPath } })
  } else if (to.name === 'Login' && authStore.isAuthenticated) {
    // Authenticated user should not remain on the login screen.
    next({ name: 'Dashboard' })
  } else {
    next()
  }
})

export default router
