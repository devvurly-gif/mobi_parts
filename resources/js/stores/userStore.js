import { defineStore } from 'pinia'
import axios from 'axios'
import { useToast } from 'vue-toastification'

export const useUserStore = defineStore('user', {
  state: () => ({
    users: [],
    currentUser: null,
    loading: false,
    error: null
  }),

  getters: {
    userById: (state) => (id) => state.users.find(user => user.id === id),
    usersCount: (state) => state.users.length
  },

  actions: {
    async fetchUsers() {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        const response = await axios.get('/api/users')
        this.users = response.data
        return { success: true, data: response.data }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch users'
        toast.error(this.error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async createUser(userData) {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        const response = await axios.post('/api/users', userData)
        const user = response.data.user || response.data
        this.users.push(user)
        toast.success(response.data.message || 'User created successfully!')
        return { success: true, data: user }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create user'
        if (error.response?.data?.errors) {
          const errorMessages = Object.values(error.response.data.errors).flat()
          toast.error(errorMessages[0] || this.error)
        } else {
          toast.error(this.error)
        }
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async updateUser(id, userData) {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        const response = await axios.put(`/api/users/${id}`, userData)
        const user = response.data.user || response.data
        const index = this.users.findIndex(u => u.id === id)
        if (index !== -1) {
          this.users[index] = user
        } else {
          this.users.push(user)
        }
        toast.success(response.data.message || 'User updated successfully!')
        return { success: true, data: user }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update user'
        if (error.response?.data?.errors) {
          const errorMessages = Object.values(error.response.data.errors).flat()
          toast.error(errorMessages[0] || this.error)
        } else {
          toast.error(this.error)
        }
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async deleteUser(id) {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        await axios.delete(`/api/users/${id}`)
        this.users = this.users.filter(user => user.id !== id)
        toast.success('User deleted successfully!')
        return { success: true }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete user'
        toast.error(this.error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async fetchUser(id) {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        const response = await axios.get(`/api/users/${id}`)
        const user = response.data.user || response.data
        this.currentUser = user
        return { success: true, data: user }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch user'
        toast.error(this.error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    setCurrentUser(user) {
      this.currentUser = user
    },

    clearCurrentUser() {
      this.currentUser = null
    },

    clearError() {
      this.error = null
    }
  }
})

