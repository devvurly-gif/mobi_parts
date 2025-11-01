import { defineStore } from 'pinia'
import axios from 'axios'
import { useToast } from 'vue-toastification'

export const useProductStore = defineStore('product', {
  state: () => ({
    products: [],
    currentProduct: null,
    pagination: {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0
    },
    filters: {
      search: '',
      category_id: null,
      is_active: null,
      low_stock: false,
      sort_by: 'created_at',
      sort_direction: 'desc'
    },
    statistics: {
      total_products: 0,
      active_products: 0,
      low_stock_products: 0,
      out_of_stock_products: 0,
      total_value: 0,
      average_profit_margin: 0
    },
    loading: false,
    error: null
  }),

  getters: {
    activeProducts: (state) => state.products.filter(product => product.is_active),
    lowStockProducts: (state) => state.products.filter(product => product.stock_quantity <= product.min_stock),
    outOfStockProducts: (state) => state.products.filter(product => product.stock_quantity <= 0),
    productById: (state) => (id) => state.products.find(product => product.id === id),
    productsByCategory: (state) => (categoryId) => state.products.filter(product => product.category_id === categoryId),
    filteredProducts: (state) => {
      let filtered = [...state.products]
      
      if (state.filters.search) {
        const search = state.filters.search.toLowerCase()
        filtered = filtered.filter(product => 
          product.name.toLowerCase().includes(search) ||
          product.description?.toLowerCase().includes(search) ||
          product.ean13?.toLowerCase().includes(search)
        )
      }
      
      if (state.filters.category_id) {
        filtered = filtered.filter(product => product.category_id === state.filters.category_id)
      }
      
      if (state.filters.is_active !== null) {
        filtered = filtered.filter(product => product.is_active === state.filters.is_active)
      }
      
      if (state.filters.low_stock) {
        filtered = filtered.filter(product => product.stock_quantity <= product.min_stock)
      }
      
      return filtered
    },
    totalValue: (state) => state.products.reduce((sum, product) => sum + (product.prix_vente * product.stock_quantity), 0),
    averageProfitMargin: (state) => {
      const productsWithProfit = state.products.filter(p => p.prix_achat > 0)
      if (productsWithProfit.length === 0) return 0
      
      const totalMargin = productsWithProfit.reduce((sum, product) => {
        return sum + ((product.prix_vente - product.prix_achat) / product.prix_achat) * 100
      }, 0)
      
      return totalMargin / productsWithProfit.length
    }
  },

  actions: {
    async fetchProducts(params = {}) {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
       
        
        const response = await axios.get('/api/products')
        
        // Handle both paginated and non-paginated responses
        if (response.data.data) {
          // Paginated response
          this.products = response.data.data
          this.pagination = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total
          }
        } else {
          // Direct array response
          this.products = response.data
          this.pagination = {
            current_page: 1,
            last_page: 1,
            per_page: response.data.length,
            total: response.data.length
          }
        }
        
        return { success: true, data: response.data }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch products'
        toast.error(this.error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async fetchProduct(id) {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        const response = await axios.get(`/api/products/${id}`)
        this.currentProduct = response.data.product
        return { success: true, data: response.data }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch product'
        toast.error(this.error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async createProduct(productData) {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        const response = await axios.post('/api/products', productData)
        this.products.unshift(response.data.product)
        toast.success('Product created successfully!')
        return { success: true, data: response.data }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create product'
        toast.error(this.error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async updateProduct(id, productData) {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        const response = await axios.put(`/api/products/${id}`, productData)
        const index = this.products.findIndex(product => product.id === id)
        if (index !== -1) {
          this.products[index] = response.data.product
        }
        if (this.currentProduct && this.currentProduct.id === id) {
          this.currentProduct = response.data.product
        }
        toast.success('Product updated successfully!')
        return { success: true, data: response.data }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update product'
        toast.error(this.error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async deleteProduct(id) {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        await axios.delete(`/api/products/${id}`)
        this.products = this.products.filter(product => product.id !== id)
        if (this.currentProduct && this.currentProduct.id === id) {
          this.currentProduct = null
        }
        toast.success('Product deleted successfully!')
        return { success: true }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete product'
        toast.error(this.error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async updateStock(id, stockQuantity) {
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        const response = await axios.patch(`/api/products/${id}/stock`, {
          stock_quantity: stockQuantity
        })
        
        const index = this.products.findIndex(product => product.id === id)
        if (index !== -1) {
          this.products[index] = response.data.product
        }
        if (this.currentProduct && this.currentProduct.id === id) {
          this.currentProduct = response.data.product
        }
        
        toast.success('Stock updated successfully!')
        return { success: true, data: response.data }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update stock'
        toast.error(this.error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async fetchStatistics() {
      console.log('ProductStore: Fetching statistics...')
      this.loading = true
      this.error = null
      const toast = useToast()
      
      try {
        const response = await axios.get('/api/products/statistics')
        console.log('ProductStore: Statistics response:', response.data)
        this.statistics = response.data
        console.log('ProductStore: Statistics updated:', this.statistics)
        return { success: true, data: response.data }
      } catch (error) {
        console.error('ProductStore: Error fetching statistics:', error)
        this.error = error.response?.data?.message || 'Failed to fetch statistics'
        toast.error(this.error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    setFilters(filters) {
      this.filters = { ...this.filters, ...filters }
      // Automatically fetch products when filters change
      this.fetchProducts()
    },

    clearFilters() {
      this.filters = {
        search: '',
        category_id: null,
        is_active: null,
        low_stock: false,
        sort_by: 'created_at',
        sort_direction: 'desc'
      }
      // Automatically fetch products when filters are cleared
      this.fetchProducts()
    },

    setCurrentProduct(product) {
      this.currentProduct = product
    },

    clearCurrentProduct() {
      this.currentProduct = null
    },

    setPagination(pagination) {
      this.pagination = { ...this.pagination, ...pagination }
    },

    clearError() {
      this.error = null
    }
  }
})
