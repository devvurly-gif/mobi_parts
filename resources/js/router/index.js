import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/authStore'

// Import page components
import Dashboard from '../components/pages/Dashboard.vue'
import Products from '../components/pages/Products.vue'
import ProductImportRowByRow from '../components/pages/ProductImportRowByRow.vue'
import ProductStockImport from '../components/pages/ProductStockImport.vue'
import ProductEdit from '../components/pages/ProductEdit.vue'
import Login from '../components/Login.vue'
import Install from '../components/pages/Install.vue'
import Home from '../components/pages/Home.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: Home,
    meta: {
      title: 'Home'
    }
  },
  {
    path: '/home',
    redirect: '/'
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    meta: { 
      requiresAuth: true,
      title: 'Dashboard'
    }
  },
  {
    path: '/install',
    name: 'install',
    component: Install,
    meta: {
      requiresGuest: true,
      title: 'Install'
    }
  },
  {
    path: '/products',
    name: 'products',
    component: Products,
    meta: { 
      requiresAuth: true,
      title: 'Products'
    }
  },
  {
    path: '/products/:id/edit',
    name: 'product-edit',
    component: ProductEdit,
    meta: { 
      requiresAuth: true,
      title: 'Edit Product'
    }
  },
  {
    path: '/products/import',
    name: 'product-import',
    component: ProductImportRowByRow,
    meta: { 
      requiresAuth: true,
      title: 'Import Products'
    }
  },
  {
    path: '/products/import-stock',
    name: 'product-stock-import',
    component: ProductStockImport,
    meta: {
      requiresAuth: true,
      title: 'Import Stock'
    }
  },
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { 
      requiresGuest: true,
      title: 'Login'
    }
  },
  {
    path: '/register',
    name: 'register',
    component: Login,
    meta: { 
      requiresGuest: true,
      title: 'Register'
    }
  },
  // Catch all route - redirect to home
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Installation status cache
let cachedInstallStatus = null
let installStatusCheckedAt = 0
async function getInstallStatus() {
  const now = Date.now()
  if (cachedInstallStatus !== null && now - installStatusCheckedAt < 30000) {
    return cachedInstallStatus
  }
  try {
    const res = await window.axios.get('/api/install/status')
    cachedInstallStatus = !!res.data.installed
  } catch (e) {
    // If status endpoint fails, assume installed to avoid blocking app
    cachedInstallStatus = true
  }
  installStatusCheckedAt = now
  return cachedInstallStatus
}

// Export function to clear install status cache
export function clearInstallStatusCache() {
  cachedInstallStatus = null
  installStatusCheckedAt = 0
}

// Navigation guards
router.beforeEach(async (to, from, next) => {
  // 1) Installation gate first
  const installed = await getInstallStatus()
  if (!installed && to.path !== '/install') {
    next('/install')
    return
  }
  if (installed && to.path === '/install') {
    next('/login')
    return
  }

  const authStore = useAuthStore()
  
  // Check if user is authenticated
  const isAuthenticated = await authStore.checkAuth()
  
  // Check if route requires authentication
  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/login')
    return
  }
  
  // Check if route requires guest (not authenticated)
  if (to.meta.requiresGuest && isAuthenticated) {
    next('/')
    return
  }
  
  // Set page title using app name from window
  if (to.meta.title) {
    const appName = (typeof window !== 'undefined' && window.APP_NAME) ? window.APP_NAME : 'Laravel Vue App'
    document.title = `${to.meta.title} - ${appName}`
  }
  
  next()
})

export default router
