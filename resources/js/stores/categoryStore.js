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
    categoriesCount: (state) => state.categories.length,
    activeCategoriesCount: (state) => state.activeCategories.length,
    flatCategories: (state) => state.categories
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
        const category = response.data.category || response.data
        this.categories.push(category)
        toast.success(response.data.message || 'Category created successfully!')
        return { success: true, data: category }
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
        const category = response.data.category || response.data
        const index = this.categories.findIndex(cat => cat.id === id)
        if (index !== -1) {
          this.categories[index] = category
        } else {
          this.categories.push(category)
        }
        toast.success(response.data.message || 'Category updated successfully!')
        return { success: true, data: category }
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
        const category = response.data.category || response.data
        this.currentCategory = category
        return { success: true, data: category }
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
