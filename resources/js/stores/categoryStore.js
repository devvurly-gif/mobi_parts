import { defineStore } from 'pinia'
import axios from 'axios'
import { useToast } from 'vue-toastification'

export const useCategoryStore = defineStore('category', {
  state: () => ({
    categories: [],
    currentCategory: null,
    loading: false,
    error: null
  }),

  getters: {
    activeCategories: (state) => state.categories.filter(category => category.is_active),
    categoryById: (state) => (id) => state.categories.find(category => category.id === id),
    categoryBySlug: (state) => (slug) => state.categories.find(category => category.slug === slug),
    categoriesCount: (state) => state.categories.length,
    activeCategoriesCount: (state) => state.activeCategories.length
  },

  actions: {
    async fetchCategories() {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        const response = await axios.get('/api/categories')
        this.categories = response.data
        return { success: true, data: response.data }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch categories'
        toast.error(this.error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async createCategory(categoryData) {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        const response = await axios.post('/api/categories', categoryData)
        this.categories.push(response.data)
        toast.success('Category created successfully!')
        return { success: true, data: response.data }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create category'
        toast.error(this.error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async updateCategory(id, categoryData) {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        const response = await axios.put(`/api/categories/${id}`, categoryData)
        const index = this.categories.findIndex(category => category.id === id)
        if (index !== -1) {
          this.categories[index] = response.data
        }
        toast.success('Category updated successfully!')
        return { success: true, data: response.data }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update category'
        toast.error(this.error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async deleteCategory(id) {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        await axios.delete(`/api/categories/${id}`)
        this.categories = this.categories.filter(category => category.id !== id)
        toast.success('Category deleted successfully!')
        return { success: true }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete category'
        toast.error(this.error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async fetchCategory(id) {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        const response = await axios.get(`/api/categories/${id}`)
        this.currentCategory = response.data
        return { success: true, data: response.data }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch category'
        toast.error(this.error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    setCurrentCategory(category) {
      this.currentCategory = category
    },

    clearCurrentCategory() {
      this.currentCategory = null
    },

    clearError() {
      this.error = null
    }
  }
})
