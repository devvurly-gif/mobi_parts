<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Sign in to your account
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Or
          <button 
            @click="toggleMode" 
            class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors duration-200"
          >
            {{ isLoginMode ? 'create a new account' : 'sign in to existing account' }}
          </button>
        </p>
      </div>
      
      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div class="rounded-md shadow-sm -space-y-px">
          <!-- Name field (only for registration) -->
          <div v-if="!isLoginMode">
            <label for="name" class="sr-only">Full Name</label>
            <input
              id="name"
              v-model="form.name"
              name="name"
              type="text"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="Full name"
            />
          </div>
          
          <!-- Email field -->
          <div>
            <label for="email" class="sr-only">Email address</label>
            <input
              id="email"
              v-model="form.email"
              name="email"
              type="email"
              autocomplete="email"
              required
              class="appearance-none rounded-none relative block w-full px-3 h-12 text-lg border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10"
              :class="isLoginMode ? 'rounded-t-md' : ''"
              placeholder="Email address"
            />
          </div>
          
          <!-- Password field -->
          <div>
            <label for="password" class="sr-only">Password</label>
            <input
              id="password"
              v-model="form.password"
              name="password"
              type="password"
              autocomplete="current-password"
              required
              class="appearance-none rounded-none relative block w-full px-3 h-12 text-lg border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10"
              placeholder="Password"
            />
          </div>
          
          <!-- Confirm Password field (only for registration) -->
          <div v-if="!isLoginMode">
            <label for="password_confirmation" class="sr-only">Confirm Password</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              name="password_confirmation"
              type="password"
              autocomplete="new-password"
              required
              class="appearance-none rounded-none relative block w-full px-3 h-12 text-lg border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10"
              placeholder="Confirm password"
            />
          </div>
        </div>

        <!-- Remember me (only for login) -->
        <div v-if="isLoginMode" class="flex items-center justify-between">
          <div class="flex items-center">
            <input
              id="remember-me"
              v-model="form.remember"
              name="remember-me"
              type="checkbox"
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
            />
            <label for="remember-me" class="ml-2 block text-sm text-gray-900">
              Remember me
            </label>
          </div>

          <div class="text-sm">
            <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors duration-200">
              Forgot your password?
            </a>
          </div>
        </div>

        <!-- Submit button -->
        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors duration-200"
          >
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg v-if="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
              </svg>
            </span>
            {{ loading ? 'Processing...' : (isLoginMode ? 'Sign in' : 'Sign up') }}
          </button>
        </div>

        <!-- Demo credentials -->
        <div v-if="isLoginMode" class="mt-4 p-4 bg-blue-50 rounded-md">
          <h3 class="text-sm font-medium text-blue-800 mb-2">Demo Credentials:</h3>
          <p class="text-xs text-blue-700">Email: demo@example.com</p>
          <p class="text-xs text-blue-700">Password: password</p>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore'

export default {
  name: 'Login',
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()
    
    const isLoginMode = ref(true)
    const loading = ref(false)
    
    const form = reactive({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      remember: false
    })

    const toggleMode = () => {
      isLoginMode.value = !isLoginMode.value
      // Clear form when switching modes
      Object.keys(form).forEach(key => {
        if (key !== 'remember') {
          form[key] = ''
        }
      })
    }

    const validateForm = () => {
      if (!isLoginMode.value && form.password !== form.password_confirmation) {
        return 'Passwords do not match'
      }
      if (form.password.length < 6) {
        return 'Password must be at least 6 characters'
      }
      return null
    }

    const handleSubmit = async () => {
      const validationError = validateForm()
      if (validationError) {
        // You could show this error in a toast
        console.error(validationError)
        return
      }

      loading.value = true
      
      try {
        let result
        if (isLoginMode.value) {
          result = await authStore.login({
            email: form.email,
            password: form.password,
            remember: form.remember
          })
        } else {
          result = await authStore.register({
            name: form.name,
            email: form.email,
            password: form.password,
            password_confirmation: form.password_confirmation
          })
        }

        if (result.success) {
          // Redirect to dashboard after successful login/register
          router.push('/dashboard')
        }
      } catch (error) {
        console.error('Authentication error:', error)
      } finally {
        loading.value = false
      }
    }

    return {
      isLoginMode,
      loading,
      form,
      toggleMode,
      handleSubmit
    }
  }
}
</script>
