<template>
  <div class="min-h-screen bg-gray-50 flex flex-col h-screen overflow-y-auto md:overflow-visible">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900">Home</h1>
        <p class="mt-1 text-sm text-gray-600">Browse all products.</p>
      </div>
    </header>

    <!-- Content -->
    <main class="flex-1 overflow-y-auto">
      <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Loading -->
        <div v-if="loading" class="p-6 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          <p class="mt-2 text-gray-600">Loading products...</p>
        </div>

        <!-- Empty state -->
        <div v-else-if="products.length === 0" class="p-10 text-center bg-white rounded-lg shadow">
          <h3 class="mt-2 text-lg font-medium text-gray-900">No products found</h3>
          <p class="mt-1 text-sm text-gray-500">Please add products from the dashboard.</p>
        </div>

        <!-- Product grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div
            v-for="product in products"
            :key="product.id"
            class="bg-white rounded-lg shadow hover:shadow-md transition-shadow overflow-hidden flex flex-col"
          >
            <div class="aspect-w-4 aspect-h-3 bg-gray-100">
            
              <img
                v-if="product.primary_image?.image_url"
                :src="product.primary_image.image_url"
                alt="Product image"
                class="w-full h-96 object-cover"
                @error="handleImageError"
              />
              <div v-else class="w-full h-48 flex items-center justify-center text-gray-400">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l6 6-6 6M21 7l-6 6 6 6"></path>
                </svg>
              </div>
            </div>
            <div class="p-4 flex-1 flex flex-col">
              <div class="flex items-start justify-between">
                <h3 class="text-base font-semibold text-gray-900 line-clamp-2">{{ product.name }}</h3>
                <span v-if="!product.is_active" class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                  Inactive
                </span>
              </div>
              <p class="mt-1 text-sm text-gray-500">{{ product.category?.name || 'No Category' }}</p>
              <div class="mt-auto">
                <div class="mt-3 flex items-center justify-between">
                  <p class="text-indigo-600 font-semibold">${{ product.prix_vente }}</p>
                  <p class="text-sm text-gray-600">Stock: {{ product.stock_quantity }}</p>
                </div>
                <p v-if="product.ean13" class="mt-1 text-xs text-gray-400 font-mono">{{ product.ean13 }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">© {{ new Date().getFullYear() }} Mobi Parts</p>
        <router-link to="/login" class="text-sm text-indigo-600 hover:text-indigo-800">Login</router-link>
      </div>
    </footer>
  </div>
</template>

<script>
import { computed, onMounted } from 'vue'
import { useProductStore } from '../../stores/productStore'

export default {
  name: 'Home',
  setup() {
    const productStore = useProductStore()
    const products = computed(() => productStore.products)
    const loading = computed(() => productStore.loading)

    const handleImageError = (event) => {
      console.error('Image failed to load:', event.target.src)
      // Try placeholder service as fallback
      const img = event.target
      const productName = img.alt || 'Product'
      const placeholderUrl = `https://via.placeholder.com/800x800/E5E7EB/9CA3AF?text=${encodeURIComponent(productName)}`
      
      // Only use placeholder if it's not already a placeholder
      if (!img.src.includes('placeholder') && !img.src.includes('via.placeholder')) {
        img.src = placeholderUrl
      } else {
        // If placeholder also fails, show the fallback div
        img.style.display = 'none'
        const placeholder = img.nextElementSibling
        if (placeholder && placeholder.classList.contains('w-full')) {
          placeholder.style.display = 'flex'
        }
      }
    }

    onMounted(async () => {
      if (!products.value || products.value.length === 0) {
        await productStore.fetchProducts()
      }
    })

    return { products, loading, handleImageError }
  }
}
</script>

<style scoped>
/***** line-clamp fallback for older Tailwind setups *****/
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
