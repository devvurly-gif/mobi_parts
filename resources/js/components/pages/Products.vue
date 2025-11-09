<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Products</h1>
            <p class="mt-1 text-sm text-gray-600">Manage your product inventory and stock levels.</p>
          </div>
          <div class="flex items-center space-x-3">
            <button 
              @click="exportProducts"
              class="inline-flex items-center bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200"
              title="Export to CSV"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              Export
            </button>
            <button 
              @click="showPrintModal = true"
              class="inline-flex items-center bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200"
              title="Print Products"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
              </svg>
              Print
            </button>
            <button 
              @click="showAddProductModal = true"
              class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200"
            >
              Add Product
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Search -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
        <input
          id="search"
          v-model="filters.search"
          type="text"
          placeholder="Search by name, EAN13, or category name..."
          class="mt-1 block w-full h-10 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
        />
      </div>

      <!-- Products Table -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div v-if="loading" class="p-6 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          <p class="mt-2 text-gray-600">Loading products...</p>
        </div>

        <div v-else-if="products.length === 0" class="p-6 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
          <p class="mt-1 text-sm text-gray-500">Get started by creating a new product.</p>
        </div>

        <ul v-else class="divide-y divide-gray-200">
          <li v-for="product in products" :key="product.id" class="px-6 py-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="shrink-0 h-10 w-10">
                  <div class="h-10 w-10 rounded-full overflow-hidden bg-gray-100 border border-gray-200 flex items-center justify-center">
                    <img
                      v-if="product.primary_image?.image_url"
                      :src="product.primary_image.image_url"
                      :alt="product.primary_image.alt_text || product.name"
                      class="h-full w-full object-cover"
                      @error="handleImageError($event, product)"
                    />
                    <span v-else class="text-sm font-medium text-gray-700">{{ product.name.charAt(0) }}</span>
                  </div>
                </div>
                <div class="ml-4">
                  <div class="flex items-center">
                    <p class="text-sm font-medium text-gray-900">{{ product.name }}</p>
                    <span v-if="!product.is_active" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                      Inactive
                    </span>
                    <span v-if="product.isLowStock" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                      Low Stock
                    </span>
                  </div>
                  <p class="text-sm text-gray-500">{{ product.category?.name || 'No Category' }}</p>
                  <p v-if="product.ean13" class="text-xs text-gray-400 font-mono">{{ product.ean13 }}</p>
                </div>
              </div>
              <div class="flex items-center space-x-4">
                <div class="text-right">
                  <p class="text-sm font-medium text-gray-900">${{ product.prix_vente }}</p>
                  <p class="text-sm text-gray-500">Stock: {{ product.stock_quantity }}</p>
                </div>
                <div class="flex space-x-2">
                  <button 
                    @click="openImagesModal(product)"
                    class="inline-flex items-center text-green-600 hover:text-green-900 text-sm font-medium"
                    title="Manage Images"
                  >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Images
                  </button>
                  <button 
                    @click="editProduct(product)"
                    class="inline-flex items-center text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                    title="Modify Product"
                  >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Modify
                  </button>
                  <button 
                    @click="deleteProduct(product)"
                    class="inline-flex items-center text-red-600 hover:text-red-900 text-sm font-medium"
                    title="Delete Product"
                  >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete
                  </button>
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 mt-6">
        <div class="flex-1 flex justify-between sm:hidden">
          <button 
            @click="goToPage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
          >
            Previous
          </button>
          <button 
            @click="goToPage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
          >
            Next
          </button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of {{ pagination.total }} results
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
              <button 
                v-for="page in visiblePages" 
                :key="page"
                @click="goToPage(page)"
                :class="[
                  page === pagination.current_page 
                    ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' 
                    : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                  'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                ]"
              >
                {{ page }}
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Product Images Modal -->
    <ProductImagesModal 
      :is-open="showImagesModal" 
      :product="selectedProduct"
      @close="closeImagesModal"
      @primary-changed="onPrimaryChanged"
    />

    <!-- Print Column Selection Modal -->
    <PrintColumnSelectionModal
      v-model="showPrintModal"
      :products="products"
      
    />

    <!-- Add Product Modal -->
    <div
      v-if="showAddProductModal"
      class="fixed inset-0 z-50 overflow-y-auto"
      @click.self="closeAddProductModal"
    >
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" @click="closeAddProductModal"></div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Add New Product
              </h3>
              <button
                @click="closeAddProductModal"
                class="text-gray-400 hover:text-gray-500"
              >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>

            <form @submit.prevent="saveProduct">
              <div class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <!-- Product Name -->
                  <div>
                    <label for="add-name" class="block text-sm font-medium text-gray-700">
                      Product Name <span class="text-red-500">*</span>
                    </label>
                    <input
                      id="add-name"
                      v-model="productForm.name"
                      type="text"
                      required
                      class="mt-1 block w-full h-10 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': productErrors.name }"
                    />
                    <p v-if="productErrors.name" class="mt-1 text-sm text-red-600">{{ productErrors.name }}</p>
                  </div>

                  <!-- Category -->
                  <div>
                    <label for="add-category_id" class="block text-sm font-medium text-gray-700">
                      Category <span class="text-red-500">*</span>
                    </label>
                    <select
                      id="add-category_id"
                      v-model="productForm.category_id"
                      required
                      class="mt-1 block w-full h-10 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': productErrors.category_id }"
                    >
                      <option value="">Select a category</option>
                      <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                      </option>
                    </select>
                    <p v-if="productErrors.category_id" class="mt-1 text-sm text-red-600">{{ productErrors.category_id }}</p>
                  </div>

                  <!-- EAN13 -->
                  <div>
                    <label for="add-ean13" class="block text-sm font-medium text-gray-700">EAN13</label>
                    <input
                      id="add-ean13"
                      v-model="productForm.ean13"
                      type="text"
                      class="mt-1 block w-full h-10 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': productErrors.ean13 }"
                    />
                    <p v-if="productErrors.ean13" class="mt-1 text-sm text-red-600">{{ productErrors.ean13 }}</p>
                  </div>

                  <!-- Purchase Price -->
                  <div>
                    <label for="add-prix_achat" class="block text-sm font-medium text-gray-700">
                      Purchase Price <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">$</span>
                      </div>
                      <input
                        id="add-prix_achat"
                        v-model="productForm.prix_achat"
                        type="number"
                        step="0.01"
                        min="0"
                        required
                        class="pl-7 block w-full h-10 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        :class="{ 'border-red-300': productErrors.prix_achat }"
                      />
                    </div>
                    <p v-if="productErrors.prix_achat" class="mt-1 text-sm text-red-600">{{ productErrors.prix_achat }}</p>
                  </div>

                  <!-- Sale Price -->
                  <div>
                    <label for="add-prix_vente" class="block text-sm font-medium text-gray-700">
                      Sale Price <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">$</span>
                      </div>
                      <input
                        id="add-prix_vente"
                        v-model="productForm.prix_vente"
                        type="number"
                        step="0.01"
                        min="0"
                        required
                        class="pl-7 block w-full h-10 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        :class="{ 'border-red-300': productErrors.prix_vente }"
                      />
                    </div>
                    <p v-if="productErrors.prix_vente" class="mt-1 text-sm text-red-600">{{ productErrors.prix_vente }}</p>
                  </div>

                  <!-- Stock Quantity -->
                  <div>
                    <label for="add-stock_quantity" class="block text-sm font-medium text-gray-700">Stock Quantity</label>
                    <input
                      id="add-stock_quantity"
                      v-model="productForm.stock_quantity"
                      type="number"
                      min="0"
                      class="mt-1 block w-full h-10 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    />
                  </div>

                  <!-- Min Stock -->
                  <div>
                    <label for="add-min_stock" class="block text-sm font-medium text-gray-700">Min Stock</label>
                    <input
                      id="add-min_stock"
                      v-model="productForm.min_stock"
                      type="number"
                      min="0"
                      class="mt-1 block w-full h-10 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    />
                  </div>

                  <!-- Image URL -->
                  <div class="sm:col-span-2">
                    <label for="add-image" class="block text-sm font-medium text-gray-700">Image URL</label>
                    <input
                      id="add-image"
                      v-model="productForm.image"
                      type="url"
                      class="mt-1 block w-full h-10 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    />
                  </div>

                  <!-- Active Status -->
                  <div class="sm:col-span-2">
                    <div class="flex items-center">
                      <input
                        id="add-is_active"
                        v-model="productForm.is_active"
                        type="checkbox"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                      />
                      <label for="add-is_active" class="ml-2 block text-sm text-gray-900">
                        Product is active
                      </label>
                    </div>
                  </div>
                </div>

                <!-- Description -->
                <div>
                  <label for="add-description" class="block text-sm font-medium text-gray-700">Description</label>
                  <textarea
                    id="add-description"
                    v-model="productForm.description"
                    rows="3"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  ></textarea>
                </div>
              </div>

              <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button
                  type="submit"
                  :disabled="savingProduct"
                  class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="savingProduct">Creating...</span>
                  <span v-else>Create Product</span>
                </button>
                <button
                  type="button"
                  @click="closeAddProductModal"
                  class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useProductStore } from '../../stores/productStore'
import { useCategoryStore } from '../../stores/categoryStore'
import { useToast } from 'vue-toastification'
import ProductImagesModal from './ProductImagesModal.vue'
import PrintColumnSelectionModal from './PrintColumnSelectionModal.vue'

export default {
  name: 'Products',
  components: {
    ProductImagesModal,
    PrintColumnSelectionModal
  },
  setup() {
    const router = useRouter()
    const productStore = useProductStore()
    const categoryStore = useCategoryStore()
    const toast = useToast()

    const showAddProductModal = ref(false)
    const showImagesModal = ref(false)
    const showPrintModal = ref(false)
    const selectedProduct = ref(null)
    const savingProduct = ref(false)
    const filters = ref({
      search: ''
    })

    const productForm = ref({
      name: '',
      category_id: '',
      description: '',
      ean13: '',
      prix_achat: 0,
      prix_vente: 0,
      stock_quantity: 0,
      min_stock: 0,
      image: '',
      is_active: true
    })

    const productErrors = ref({})

   

    const products = computed(() => {
      // Use client-side filtering since server-side filtering is removed
      let filtered = productStore.products
      
      if (filters.value.search) {
        const search = filters.value.search.toLowerCase()
        filtered = filtered.filter(product => {
          const categoryName = product.category?.name?.toLowerCase() || ''
          return (
            product.name.toLowerCase().includes(search) ||
            product.description?.toLowerCase().includes(search) ||
            product.ean13?.toLowerCase().includes(search) ||
            categoryName.includes(search)
          )
        })
      }
      
      return filtered
    })
    const categories = computed(() => categoryStore.categories)
    const loading = computed(() => productStore.loading)
    const pagination = computed(() => productStore.pagination)

    const visiblePages = computed(() => {
      const current = pagination.value.current_page
      const last = pagination.value.last_page
      const pages = []
      
      for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
        pages.push(i)
      }
      
      return pages
    })

    const applyFilters = () => {
      productStore.setFilters(filters.value)
    }

    const clearFilters = () => {
      filters.value = { search: '' }
      productStore.clearFilters()
    }

    const goToPage = (page) => {
      if (page >= 1 && page <= pagination.value.last_page) {
        productStore.fetchProducts({ page })
      }
    }

    const editProduct = (product) => {
      router.push(`/products/${product.id}/edit`)
    }

    const deleteProduct = async (product) => {
      if (confirm(`Are you sure you want to delete "${product.name}"?`)) {
        const result = await productStore.deleteProduct(product.id)
        if (result.success) {
          toast.success('Product deleted successfully!')
        }
      }
    }

    const openImagesModal = (product) => {
      selectedProduct.value = product
      showImagesModal.value = true
    }

    const closeImagesModal = () => {
      showImagesModal.value = false
      selectedProduct.value = null
    }

    const onPrimaryChanged = ({ productId, image }) => {
      // Update selected product if open
      if (selectedProduct.value && selectedProduct.value.id === productId) {
        selectedProduct.value = { ...selectedProduct.value, primary_image: image }
      }
      // Update list item in store
      const idx = productStore.products.findIndex(p => p.id === productId)
      if (idx !== -1) {
        productStore.products[idx] = { ...productStore.products[idx], primary_image: image }
      }
    }

    const handleImageError = (event, product) => {
      // If default placeholder also fails, show fallback text
      if (product.primary_image?.id === null) {
        // Already showing default, just hide the broken image
        event.target.style.display = 'none'
      } else {
        // Try to use a placeholder service as fallback
        const placeholderUrl = ``
        event.target.src = placeholderUrl
      }
    }

    const exportProducts = () => {
      // Prepare CSV data
      const headers = ['ID', 'Name', 'Category', 'EAN13', 'Purchase Price', 'Sale Price', 'Stock Quantity', 'Min Stock', 'Status', 'Description']
      
      const csvData = products.value.map(product => [
        product.id,
        product.name || '',
        product.category?.name || 'No Category',
        product.ean13 || '',
        product.prix_achat || 0,
        product.prix_vente || 0,
        product.stock_quantity || 0,
        product.min_stock || 0,
        product.is_active ? 'Active' : 'Inactive',
        (product.description || '').replace(/,/g, ';').replace(/\n/g, ' ') // Replace commas and newlines
      ])
      
      // Create CSV content
      const csvContent = [
        headers.join(','),
        ...csvData.map(row => row.map(cell => `"${cell}"`).join(','))
      ].join('\n')
      
      // Add BOM for UTF-8 to support special characters
      const BOM = '\uFEFF'
      const blob = new Blob([BOM + csvContent], { type: 'text/csv;charset=utf-8;' })
      
      // Create download link
      const link = document.createElement('a')
      const url = URL.createObjectURL(blob)
      link.setAttribute('href', url)
      link.setAttribute('download', `products_${new Date().toISOString().split('T')[0]}.csv`)
      link.style.visibility = 'hidden'
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      
      toast.success(`Exported ${products.value.length} products to CSV`)
    }

    const resetProductForm = () => {
      productForm.value = {
        name: '',
        category_id: '',
        description: '',
        ean13: '',
        prix_achat: 0,
        prix_vente: 0,
        stock_quantity: 0,
        min_stock: 0,
        image: '',
        is_active: true
      }
      productErrors.value = {}
    }

    const closeAddProductModal = () => {
      showAddProductModal.value = false
      resetProductForm()
    }

    const saveProduct = async () => {
      savingProduct.value = true
      productErrors.value = {}

      try {
        const productData = {
          name: productForm.value.name,
          category_id: productForm.value.category_id,
          description: productForm.value.description || null,
          ean13: productForm.value.ean13 || null,
          prix_achat: parseFloat(productForm.value.prix_achat) || 0,
          prix_vente: parseFloat(productForm.value.prix_vente) || 0,
          stock_quantity: parseInt(productForm.value.stock_quantity) || 0,
          min_stock: parseInt(productForm.value.min_stock) || 0,
          image: productForm.value.image || null,
          is_active: productForm.value.is_active !== false
        }

        const result = await productStore.createProduct(productData)
        
        if (result.success) {
          closeAddProductModal()
          // Product will be added to the list automatically by the store
        } else {
          if (result.errors) {
            productErrors.value = result.errors
          }
        }
      } catch (error) {
        if (error.response?.data?.errors) {
          Object.keys(error.response.data.errors).forEach(key => {
            productErrors.value[key] = error.response.data.errors[key][0]
          })
        } else {
          toast.error(error.response?.data?.message || 'Failed to create product')
        }
      } finally {
        savingProduct.value = false
      }
    }


    
    // Watch for filter changes
    watch(filters, () => {
      applyFilters()
    }, { deep: true })

    // Initialize data
    onMounted(async () => {
      await Promise.all([
        productStore.fetchProducts(),
        categoryStore.fetchCategories()
      ])
    })

    return {
      showAddProductModal,
      showImagesModal,
      showPrintModal,
      selectedProduct,
      filters,
      products,
      categories,
      loading,
      pagination,
      visiblePages,
      productForm,
      productErrors,
      savingProduct,
      applyFilters,
      clearFilters,
      goToPage,
      editProduct,
      deleteProduct,
      openImagesModal,
      closeImagesModal,
      onPrimaryChanged,
      handleImageError,
      exportProducts,
      closeAddProductModal,
      saveProduct
    }
  }
}
</script>
