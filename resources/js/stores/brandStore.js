import { defineStore } from 'pinia'
import axios from 'axios'
import { useToast } from 'vue-toastification'

export const useBrandStore = defineStore('brand', {
  state: () => ({
    brands: [],
    currentBrand: null,
    loading: false,
    error: null
  }),

  getters: {
    activeBrands: (state) => state.brands.filter(brand => brand.is_active),
    brandById: (state) => (id) => state.brands.find(brand => brand.id === id),
    brandsCount: (state) => state.brands.length,
    activeBrandsCount: (state) => state.activeBrands.length,
    rootBrands: (state) => state.brands.filter(brand => !brand.parent_id),
    hierarchicalBrands: (state) => {
      const buildTree = (parentId = null) => {
        return state.brands
          .filter(brand => brand.parent_id === parentId)
          .map(brand => ({
            ...brand,
            children: buildTree(brand.id)
          }))
      }
      return buildTree()
    },
    flatBrandsForSelect: (state) => {
      const flatten = (brands, level = 0, excludeId = null) => {
        let result = []
        brands.forEach(brand => {
          if (brand.id !== excludeId) {
            const prefix = '  '.repeat(level)
            result.push({
              ...brand,
              displayName: prefix + brand.name,
              level
            })
            if (brand.children && brand.children.length > 0) {
              result = result.concat(flatten(brand.children, level + 1, excludeId))
            }
          }
        })
        return result
      }
      return flatten(state.hierarchicalBrands)
    }
  },

  actions: {
    async fetchBrands() {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        const response = await axios.get('/api/brands')
        this.brands = response.data
        return { success: true, data: response.data }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch brands'
        toast.error(this.error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async createBrand(brandData) {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        const response = await axios.post('/api/brands', brandData)
        const brand = response.data.brand || response.data
        this.brands.push(brand)
        toast.success(response.data.message || 'Brand created successfully!')
        return { success: true, data: brand }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create brand'
        toast.error(this.error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async updateBrand(id, brandData) {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        const response = await axios.put(`/api/brands/${id}`, brandData)
        const brand = response.data.brand || response.data
        const index = this.brands.findIndex(b => b.id === id)
        if (index !== -1) {
          this.brands[index] = brand
        } else {
          this.brands.push(brand)
        }
        toast.success(response.data.message || 'Brand updated successfully!')
        return { success: true, data: brand }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update brand'
        toast.error(this.error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async removeParent(id) {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        const response = await axios.post(`/api/brands/${id}/remove-parent`)
        const brand = response.data.brand || response.data
        const index = this.brands.findIndex(b => b.id === id)
        if (index !== -1) {
          // Update the brand in the list with the new data (parent_id removed)
          this.brands[index] = { ...this.brands[index], ...brand, parent_id: null, parent: null }
        } else {
          // If not found, refresh the entire list
          await this.fetchBrands()
        }
        toast.success(response.data.message || 'Brand restored to root level successfully!')
        return { success: true, data: brand }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to remove parent'
        toast.error(this.error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async deleteBrand(id) {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        await axios.delete(`/api/brands/${id}`)
        this.brands = this.brands.filter(brand => brand.id !== id)
        toast.success('Brand deleted successfully!')
        return { success: true }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete brand'
        toast.error(this.error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async fetchBrand(id) {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        const response = await axios.get(`/api/brands/${id}`)
        const brand = response.data.brand || response.data
        this.currentBrand = brand
        return { success: true, data: brand }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch brand'
        toast.error(this.error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    setCurrentBrand(brand) {
      this.currentBrand = brand
    },

    clearCurrentBrand() {
      this.currentBrand = null
    },

    clearError() {
      this.error = null
    }
  }
})

