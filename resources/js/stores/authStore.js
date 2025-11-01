import { defineStore } from 'pinia'
import axios from 'axios'
import { useToast } from 'vue-toastification'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    loading: false,
    isAuthenticated: false
  }),

  getters: {
    isLoggedIn: (state) => !!state.token && !!state.user,
    userRole: (state) => state.user?.role || null,
    userName: (state) => state.user?.name || 'Guest'
  },

  actions: {
    async login(credentials) {
      this.loading = true
      const toast = useToast()
      
      try {
        const response = await axios.post('/api/login', credentials)
        const { user, token } = response.data
        
        this.user = user
        this.token = token
        this.isAuthenticated = true
        
        // Store token in localStorage
        localStorage.setItem('token', token)
        
        // Set default axios header
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
        
        toast.success('Login successful!')
        return { success: true, user }
      } catch (error) {
        const message = error.response?.data?.message || 'Login failed'
        toast.error(message)
        return { success: false, error: message }
      } finally {
        this.loading = false
      }
    },

    async register(userData) {
      this.loading = true
      const toast = useToast()
      
      try {
        const response = await axios.post('/api/register', userData)
        const { user, token } = response.data
        
        this.user = user
        this.token = token
        this.isAuthenticated = true
        
        // Store token in localStorage
        localStorage.setItem('token', token)
        
        // Set default axios header
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
        
        toast.success('Registration successful!')
        return { success: true, user }
      } catch (error) {
        const message = error.response?.data?.message || 'Registration failed'
        toast.error(message)
        return { success: false, error: message }
      } finally {
        this.loading = false
      }
    },

    async logout() {
      const toast = useToast()
      
      try {
        // Call logout API if needed
        if (this.token) {
          await axios.post('/api/logout', {}, {
            headers: { Authorization: `Bearer ${this.token}` }
          })
        }
      } catch (error) {
        console.error('Logout API error:', error)
      } finally {
        // Clear local state
        this.user = null
        this.token = null
        this.isAuthenticated = false
        
        // Remove auth data from localStorage
        localStorage.removeItem('token')
        localStorage.removeItem('user')
        
        // Remove axios header
        delete axios.defaults.headers.common['Authorization']
        
        toast.info('Logged out successfully')
      }
    },

    async checkAuth() {
      if (!this.token) return false
      
      this.loading = true
      
      try {
        // Set axios header
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
        
        const response = await axios.get('/api/user')
        this.user = response.data
        this.isAuthenticated = true
        return true
      } catch (error) {
        // Token is invalid, clear auth state
        this.logout()
        return false
      } finally {
        this.loading = false
      }
    },

    async updateProfile(profileData) {
      this.loading = true
      const toast = useToast()
      
      try {
        const response = await axios.put('/api/profile', profileData)
        this.user = response.data
        toast.success('Profile updated successfully!')
        return { success: true, user: response.data }
      } catch (error) {
        const message = error.response?.data?.message || 'Profile update failed'
        toast.error(message)
        return { success: false, error: message }
      } finally {
        this.loading = false
      }
    },

    async changePassword(passwordData) {
      this.loading = true
      const toast = useToast()
      
      try {
        await axios.put('/api/change-password', passwordData)
        toast.success('Password changed successfully!')
        return { success: true }
      } catch (error) {
        const message = error.response?.data?.message || 'Password change failed'
        toast.error(message)
        return { success: false, error: message }
      } finally {
        this.loading = false
      }
    }
  }
})
