<template>
  <div class="min-h-screen bg-gray-50 flex flex-col h-screen overflow-y-auto md:overflow-visible">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="max-w-full mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900">Home</h1>
        <p class="mt-1 text-sm text-gray-600">Browse all products.</p>
      </div>
    </header>

    <!-- Category Menu (Top) -->
    <div class="bg-white border-b shadow-sm sticky top-0 z-10">
      <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center space-x-1 overflow-x-auto py-4">
          <button
            @click="selectedCategory = null"
            :class="[
              'px-4 py-2 rounded-md text-sm font-medium whitespace-nowrap transition-colors',
              selectedCategory === null
                ? 'bg-indigo-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            All Categories
          </button>
          <button
            v-for="category in activeCategories"
            :key="category.id"
            @click="selectedCategory = category.id"
            :class="[
              'px-4 py-2 rounded-md text-sm font-medium whitespace-nowrap transition-colors',
              selectedCategory === category.id
                ? 'bg-indigo-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            {{ category.name }}
          </button>
        </div>
      </div>
    </div>

    <!-- Content with Sidebar -->
    <main class="flex-1 overflow-y-auto flex">
      <!-- Left Sidebar - Brands -->
      <aside class="hidden lg:block w-64 bg-white border-r shadow-sm min-h-full">
        <div class="p-4">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Brands</h2>
          <div class="space-y-2">
            <button
              @click="selectedBrand = null"
              :class="[
                'w-full text-left px-4 py-2 rounded-md text-sm font-medium transition-colors',
                selectedBrand === null
                  ? 'bg-indigo-600 text-white'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              ]"
            >
              All Brands
            </button>
            <button
              v-for="brand in activeBrands"
              :key="brand.id"
              @click="selectedBrand = brand.id"
              :class="[
                'w-full text-left px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center',
                selectedBrand === brand.id
                  ? 'bg-indigo-600 text-white'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              ]"
            >
              <img
                v-if="brand.logo"
                :src="brand.logo"
                :alt="brand.name"
                class="w-6 h-6 mr-2 rounded object-cover"
                @error="handleBrandLogoError"
              />
              <span v-else class="w-6 h-6 mr-2 rounded bg-gray-300 flex items-center justify-center text-xs font-bold text-gray-600">
                {{ brand.name.charAt(0).toUpperCase() }}
              </span>
              <span>{{ brand.name }}</span>
            </button>
          </div>
        </div>
      </aside>

      <!-- Mobile Brand Menu Button -->
      <div class="lg:hidden fixed bottom-4 right-4 z-20">
        <button
          @click="showMobileBrandMenu = !showMobileBrandMenu"
          class="bg-indigo-600 text-white p-4 rounded-full shadow-lg hover:bg-indigo-700 transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>
      </div>

      <!-- Mobile Brand Menu -->
      <div
        v-if="showMobileBrandMenu"
        class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-30"
        @click="showMobileBrandMenu = false"
      >
        <div class="absolute right-0 top-0 bottom-0 w-64 bg-white shadow-xl overflow-y-auto" @click.stop>
          <div class="p-4">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-lg font-semibold text-gray-900">Brands</h2>
              <button
                @click="showMobileBrandMenu = false"
                class="text-gray-400 hover:text-gray-600"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
            <div class="space-y-2">
              <button
                @click="selectedBrand = null; showMobileBrandMenu = false"
                :class="[
                  'w-full text-left px-4 py-2 rounded-md text-sm font-medium transition-colors',
                  selectedBrand === null
                    ? 'bg-indigo-600 text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                ]"
              >
                All Brands
              </button>
              <button
                v-for="brand in activeBrands"
                :key="brand.id"
                @click="selectedBrand = brand.id; showMobileBrandMenu = false"
                :class="[
                  'w-full text-left px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center',
                  selectedBrand === brand.id
                    ? 'bg-indigo-600 text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                ]"
              >
                <img
                  v-if="brand.logo"
                  :src="brand.logo"
                  :alt="brand.name"
                  class="w-6 h-6 mr-2 rounded object-cover"
                  @error="handleBrandLogoError"
                />
                <span v-else class="w-6 h-6 mr-2 rounded bg-gray-300 flex items-center justify-center text-xs font-bold text-gray-600">
                  {{ brand.name.charAt(0).toUpperCase() }}
                </span>
                <span>{{ brand.name }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Area -->
      <div class="flex-1 overflow-y-auto">
      <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Loading -->
        <div v-if="loading" class="p-6 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          <p class="mt-2 text-gray-600">Loading products...</p>
        </div>

        <!-- Empty state -->
          <div v-else-if="!loading && filteredProducts.length === 0" class="p-10 text-center bg-white rounded-lg shadow">
          <h3 class="mt-2 text-lg font-medium text-gray-900">No products available</h3>
            <p class="mt-1 text-sm text-gray-500">
              <span v-if="selectedCategory || selectedBrand">
                No products found for the selected filters.
              </span>
              <span v-else>
                No active products found at this time.
              </span>
            </p>
        </div>

        <!-- Product grid -->
          <div v-else-if="!loading && filteredProducts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <router-link
              v-for="product in filteredProducts"
            :key="product.id"
            :to="`/product/${product.id}`"
            class="bg-white rounded-lg shadow hover:shadow-md transition-shadow overflow-hidden flex flex-col cursor-pointer"
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
                <p class="mt-1 text-sm text-gray-500">
                  {{ product.category?.name || 'No Category' }}
                  <span v-if="product.brand?.name" class="text-gray-400"> • {{ product.brand.name }}</span>
                </p>
              <div class="mt-auto">
                <div class="mt-3 flex items-center justify-between">
                  <p class="text-indigo-600 font-semibold">${{ product.prix_vente }}</p>
                  <p class="text-sm text-gray-600">Stock: {{ product.stock_quantity }}</p>
                </div>
                <p v-if="product.ean13" class="mt-1 text-xs text-gray-400 font-mono">{{ product.ean13 }}</p>
              </div>
            </div>
          </router-link>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t">
      <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">© {{ new Date().getFullYear() }} Mobi Parts</p>
        <router-link to="/login" class="text-sm text-indigo-600 hover:text-indigo-800">Login</router-link>
      </div>
    </footer>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useProductStore } from '../../stores/productStore'
import { useCategoryStore } from '../../stores/categoryStore'
import { useBrandStore } from '../../stores/brandStore'

export default {
  name: 'Home',
  setup() {
    const productStore = useProductStore()
    const categoryStore = useCategoryStore()
    const brandStore = useBrandStore()

    const selectedCategory = ref(null)
    const selectedBrand = ref(null)
    const showMobileBrandMenu = ref(false)

    const products = computed(() => productStore.products)
    const loading = computed(() => productStore.loading)
    const categories = computed(() => categoryStore.categories)
    const brands = computed(() => brandStore.brands)

    // Filter active categories and brands
    const activeCategories = computed(() => {
      return categories.value.filter(category => category.is_active)
    })

    const activeBrands = computed(() => {
      return brands.value.filter(brand => brand.is_active)
    })

    // Filter products based on selected category and brand
    const filteredProducts = computed(() => {
      let filtered = products.value.filter(product => product.is_active !== false)

      if (selectedCategory.value) {
        filtered = filtered.filter(product => product.category_id === selectedCategory.value)
      }

      if (selectedBrand.value) {
        filtered = filtered.filter(product => product.brand_id === selectedBrand.value)
      }

      return filtered
    })

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

    const handleBrandLogoError = (event) => {
      // Hide the image and show the fallback
      event.target.style.display = 'none'
    }

    onMounted(async () => {
      // Fetch products if not already loaded
      if (!products.value || products.value.length === 0) {
        await productStore.fetchProducts()
      }

      // Fetch categories if not already loaded
      if (!categories.value || categories.value.length === 0) {
        await categoryStore.fetchCategories()
      }

      // Fetch brands if not already loaded
      if (!brands.value || brands.value.length === 0) {
        await brandStore.fetchBrands()
      }
    })

    return {
      products,
      filteredProducts,
      loading,
      categories,
      brands,
      activeCategories,
      activeBrands,
      selectedCategory,
      selectedBrand,
      showMobileBrandMenu,
      handleImageError,
      handleBrandLogoError
    }
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
