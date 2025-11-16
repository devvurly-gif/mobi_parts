<template>
  <div class="homepage">
    <!-- Top Bar -->
    <div class="top-bar">
      <div class="container">
        <div class="top-bar-content">
          <div class="top-left">
            <span>Cell Phone Parts Wholesale</span>
          </div>
          <div class="top-right">
            <a href="#" class="top-link">Recently Viewed</a>
            <a href="#" class="top-link">Wish List</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Header -->
    <header class="header">
      <div class="container">
        <div class="header-content">
          <div class="logo-section">
            <router-link to="/" class="logo">
              <span class="logo-text">MobiParts</span>
            </router-link>
          </div>

          <div class="search-section">
            <div class="search-wrapper">
              <select class="category-select">
                <option>All categories</option>
                <option v-for="cat in mainCategories" :key="cat.id">{{ cat.name }}</option>
              </select>
              <input
                type="text"
                v-model="searchQuery"
                @keyup.enter="handleSearch"
                placeholder="Search..."
                class="search-input"
              />
              <button @click="handleSearch" class="search-btn">Search</button>
            </div>
          </div>

          <div class="header-actions">
            <router-link to="/login" class="login-link">Login</router-link>
            <span class="divider">|</span>
            <a href="#" class="join-link">Join Free</a>
          </div>
        </div>
      </div>
    </header>

    <!-- Navigation -->
    <nav class="main-nav">
      <div class="container">
        <div class="nav-content">
          <a
            v-for="category in mainCategories"
            :key="category.id"
            @click.prevent="selectCategory(category.id)"
            href="#"
            class="nav-link"
            :class="{ active: selectedCategoryId === category.id }"
          >
            {{ category.name }}
            <span class="nav-count">({{ getCategoryProductCount(category.id) }})</span>
          </a>
          <a
            @click.prevent="selectCategory(null)"
            href="#"
            class="nav-link"
            :class="{ active: selectedCategoryId === null }"
          >
            All Products
          </a>
        </div>
      </div>
    </nav>

    <!-- Breadcrumbs -->
    <div class="breadcrumbs" v-if="selectedCategory">
      <div class="container">
        <router-link to="/">Home</router-link>
        <span class="separator">></span>
        <span>{{ selectedCategory.name }}</span>
      </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <div class="container">
        <div class="content-wrapper">
          <!-- Sidebar -->
          <aside class="sidebar">
            <div class="sidebar-section">
              <h3 class="sidebar-title">Brands</h3>
              <div class="brand-list">
                <label class="brand-item">
                  <input
                    type="checkbox"
                    :checked="selectedBrands.length === 0"
                    @change="clearBrands"
                  />
                  <span>All Brands</span>
                  <span class="count">({{ products.length }})</span>
                </label>
                <div v-for="brand in rootBrands" :key="brand.id" class="brand-group">
                  <label class="brand-item">
                    <input
                      type="checkbox"
                      :checked="selectedBrands.includes(brand.id)"
                      @change="toggleBrand(brand.id)"
                    />
                    <span class="brand-name-text">{{ brand.name }}</span>
                    <span class="count">({{ getBrandProductCount(brand.id) }})</span>
                    <button
                      v-if="brand.children && brand.children.length > 0"
                      @click.stop="toggleBrandExpand(brand.id)"
                      class="expand-icon"
                      :class="{ expanded: expandedBrands.includes(brand.id) }"
                      type="button"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                      </svg>
                    </button>
                  </label>
                  <div
                    v-if="expandedBrands.includes(brand.id) && brand.children && brand.children.length > 0"
                    class="sub-brands"
                  >
                    <label
                      v-for="child in brand.children.filter(c => c.is_active)"
                      :key="child.id"
                      class="brand-item sub-brand"
                    >
                      <input
                        type="checkbox"
                        :checked="selectedBrands.includes(child.id)"
                        @change="toggleBrand(child.id)"
                      />
                      <span>{{ child.name }}</span>
                      <span class="count">({{ getBrandProductCount(child.id) }})</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </aside>

          <!-- Products Area -->
          <main class="products-area">
            <!-- Products Header -->
            <div class="products-header">
              <h1 class="page-title">{{ selectedCategory ? selectedCategory.name : 'All Products' }}</h1>
              <div class="products-controls">
                <select v-model="sortBy" @change="applyFilters" class="sort-select">
                  <option value="created_at">Sort by Default</option>
                  <option value="prix_vente">Price: Low to High</option>
                  <option value="prix_vente_desc">Price: High to Low</option>
                  <option value="name">Name: A to Z</option>
                </select>
                <select v-model="perPage" @change="applyFilters" class="per-page-select">
                  <option :value="12">Show 12</option>
                  <option :value="24">Show 24</option>
                  <option :value="48">Show 48</option>
                </select>
              </div>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="loading">
              <div class="spinner"></div>
              <p>Loading products...</p>
            </div>

            <!-- Products Grid -->
            <div v-else-if="paginatedProducts.length > 0" class="products-grid">
              <div
                v-for="product in paginatedProducts"
                :key="product.id"
                class="product-card"
              >
                <div class="product-image-wrapper" @click="goToProduct(product.id)">
                  <img
                    :src="product.primary_image?.image_url || defaultImage"
                    :alt="product.name"
                    class="product-image"
                    @error="handleImageError"
                  />
                  <div v-if="product.stock_quantity === 0" class="stock-badge out-of-stock">Out of Stock</div>
                  <div v-else-if="product.stock_quantity <= product.min_stock" class="stock-badge low-stock">Low Stock</div>
                </div>
                <div class="product-info">
                  <h3 class="product-name" @click="goToProduct(product.id)">{{ product.name }}</h3>
                  <div class="product-price">${{ product.prix_vente }}</div>
                  <div class="product-actions hidden">
                    <button class="btn-order" @click.stop="handleOrder(product)">
                      Login to Order 
                    </button>
                    <button class="btn-wishlist" @click.stop="addToWishlist(product)" title="Add to wish list">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                      </svg>
                      Add to wish list
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div v-else class="empty-state">
              <p>No products found matching your criteria.</p>
            </div>

            <!-- Pagination -->
            <div class="pagination" v-if="totalPages > 1 && !loading">
              <button
                @click="changePage(currentPage - 1)"
                :disabled="currentPage === 1"
                class="page-btn"
              >
                &lt;
              </button>
              <div class="page-numbers">
                <button
                  v-for="page in totalPages"
                  :key="page"
                  @click="changePage(page)"
                  :class="['page-number', { active: currentPage === page }]"
                >
                  {{ page }}
                </button>
              </div>
              <button
                @click="changePage(currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="page-btn"
              >
                &gt;
              </button>
            </div>
          </main>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const searchQuery = ref('')
const products = ref([])
const categories = ref([])
const brands = ref([])
const loading = ref(true)
const defaultImage = 'https://via.placeholder.com/400x400/E5E7EB/9CA3AF?text=No+Image'

const selectedCategoryId = ref(null)
const selectedBrands = ref([])
const sortBy = ref('created_at')
const perPage = ref(12)
const currentPage = ref(1)
const expandedBrands = ref([])

const mainCategories = computed(() => categories.value.slice(0, 6))
const selectedCategory = computed(() => 
  categories.value.find(c => c.id === selectedCategoryId.value)
)

const rootBrands = computed(() => {
  return brands.value.filter(brand => !brand.parent_id && brand.is_active)
})

const filteredProducts = computed(() => {
  let filtered = products.value.filter(p => p.is_active !== false)

  if (selectedCategoryId.value) {
    filtered = filtered.filter(p => p.category_id === selectedCategoryId.value)
  }

  if (selectedBrands.value.length > 0) {
    filtered = filtered.filter(p => selectedBrands.value.includes(p.brand_id))
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(p => 
      p.name.toLowerCase().includes(query) ||
      p.description?.toLowerCase().includes(query) ||
      p.ean13?.toLowerCase().includes(query)
    )
  }

  const sorted = [...filtered]
  if (sortBy.value === 'prix_vente') {
    sorted.sort((a, b) => a.prix_vente - b.prix_vente)
  } else if (sortBy.value === 'prix_vente_desc') {
    sorted.sort((a, b) => b.prix_vente - a.prix_vente)
  } else if (sortBy.value === 'name') {
    sorted.sort((a, b) => a.name.localeCompare(b.name))
  } else {
    sorted.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
  }

  return sorted
})

const totalPages = computed(() => Math.ceil(filteredProducts.value.length / perPage.value))
const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  const end = start + perPage.value
  return filteredProducts.value.slice(start, end)
})

const getBrandProductCount = (brandId) => {
  // Get all child brand IDs recursively
  const getAllChildBrandIds = (parentId) => {
    const childIds = [parentId] // Include the parent itself
    const children = brands.value.filter(b => b.parent_id === parentId && b.is_active)
    children.forEach(child => {
      childIds.push(...getAllChildBrandIds(child.id))
    })
    return childIds
  }
  
  const allBrandIds = getAllChildBrandIds(brandId)
  return products.value.filter(p => allBrandIds.includes(p.brand_id) && p.is_active !== false).length
}

const getCategoryProductCount = (categoryId) => {
  return products.value.filter(p => p.category_id === categoryId && p.is_active !== false).length
}

const selectCategory = (categoryId) => {
  selectedCategoryId.value = categoryId
  currentPage.value = 1
}

const toggleBrand = (brandId) => {
  const index = selectedBrands.value.indexOf(brandId)
  if (index > -1) {
    selectedBrands.value.splice(index, 1)
  } else {
    selectedBrands.value.push(brandId)
  }
  currentPage.value = 1
}

const toggleBrandExpand = (brandId) => {
  const index = expandedBrands.value.indexOf(brandId)
  if (index > -1) {
    expandedBrands.value.splice(index, 1)
  } else {
    expandedBrands.value.push(brandId)
  }
}

const clearBrands = () => {
  selectedBrands.value = []
  currentPage.value = 1
}

const applyFilters = () => {
  currentPage.value = 1
}

const handleSearch = () => {
  currentPage.value = 1
}

const changePage = (page) => {
  currentPage.value = page
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const goToProduct = (id) => {
  router.push(`/product/${id}`)
}

const handleImageError = (event) => {
  event.target.src = defaultImage
}

const addToWishlist = (product) => {
  console.log('Add to wishlist:', product)
}

const handleOrder = (product) => {
  router.push('/login')
}

const fetchData = async () => {
  loading.value = true
  try {
    const [productsRes, categoriesRes, brandsRes] = await Promise.all([
      window.axios.get('/api/products'),
      window.axios.get('/api/categories'),
      window.axios.get('/api/brands', { params: { is_active: true } })
    ])
    
    products.value = Array.isArray(productsRes.data) ? productsRes.data : productsRes.data.data || []
    categories.value = categoriesRes.data.filter(c => c.is_active)
    brands.value = brandsRes.data.filter(b => b.is_active)
  } catch (error) {
    console.error('Error fetching data:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>

<style scoped>
/* Reset and Base */
.hidden {
  display: none !important;
}

.homepage {
  min-height: 100vh;
  background: #f5f5f5;
  font-family: Arial, Helvetica, sans-serif;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 15px;
}

/* Top Bar */
.top-bar {
  background: #333;
  color: #fff;
  padding: 8px 0;
  font-size: 13px;
}

.top-bar-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.top-link {
  color: #fff;
  text-decoration: none;
  margin-left: 20px;
}

.top-link:hover {
  text-decoration: underline;
}

/* Header */
.header {
  background: #fff;
  border-bottom: 1px solid #ddd;
  padding: 15px 0;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 30px;
}

.logo-section {
  flex-shrink: 0;
}

.logo {
  text-decoration: none;
  color: #333;
  font-size: 24px;
  font-weight: bold;
}

.search-section {
  flex: 1;
  max-width: 600px;
}

.search-wrapper {
  display: flex;
  border: 1px solid #ddd;
  border-radius: 0;
}

.category-select {
  padding: 8px 12px;
  border: none;
  border-right: 1px solid #ddd;
  background: #f5f5f5;
  font-size: 13px;
  cursor: pointer;
}

.search-input {
  flex: 1;
  padding: 8px 12px;
  border: none;
  outline: none;
  font-size: 13px;
}

.search-btn {
  padding: 8px 20px;
  background: #333;
  color: #fff;
  border: none;
  cursor: pointer;
  font-size: 13px;
}

.search-btn:hover {
  background: #555;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
}

.login-link,
.join-link {
  color: #333;
  text-decoration: none;
}

.login-link:hover,
.join-link:hover {
  text-decoration: underline;
}

.divider {
  color: #ccc;
}

/* Navigation */
.main-nav {
  background: #fff;
  border-bottom: 1px solid #ddd;
}

.nav-content {
  display: flex;
  gap: 0;
  padding: 0;
}

.nav-link {
  padding: 12px 20px;
  color: #333;
  text-decoration: none;
  display: inline-block;
  border-bottom: 2px solid transparent;
  transition: all 0.2s;
}

.nav-link:hover {
  background: #f5f5f5;
}

.nav-link.active {
  border-bottom-color: #333;
  font-weight: bold;
}

.nav-count {
  color: #999;
  font-size: 12px;
  font-weight: normal;
  margin-left: 5px;
}

/* Breadcrumbs */
.breadcrumbs {
  background: #f9f9f9;
  padding: 10px 0;
  font-size: 13px;
}

.breadcrumbs a {
  color: #333;
  text-decoration: none;
}

.breadcrumbs a:hover {
  text-decoration: underline;
}

.separator {
  margin: 0 8px;
  color: #999;
}

/* Main Content */
.main-content {
  padding: 20px 0;
}

.content-wrapper {
  display: flex;
  gap: 20px;
  align-items: flex-start;
}

/* Sidebar */
.sidebar {
  width: 250px;
  background: #fff;
  padding: 20px;
  border: 1px solid #ddd;
  flex-shrink: 0;
}

.sidebar-section {
  margin-bottom: 30px;
}

.sidebar-section:last-child {
  margin-bottom: 0;
}

.sidebar-title {
  font-size: 14px;
  font-weight: bold;
  margin-bottom: 15px;
  text-transform: uppercase;
  color: #333;
}

.brand-list {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.brand-group {
  position: relative;
}

.brand-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 5px 0;
  cursor: pointer;
  font-size: 13px;
}

.brand-item input[type="checkbox"] {
  cursor: pointer;
}

.brand-name-text {
  flex: 1;
}

.count {
  color: #999;
  font-size: 12px;
}

.expand-icon {
  width: 16px;
  height: 16px;
  border: none;
  background: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  margin-left: auto;
  color: #666;
  flex-shrink: 0;
}

.expand-icon svg {
  width: 12px;
  height: 12px;
  transition: transform 0.2s;
}

.expand-icon.expanded svg {
  transform: rotate(90deg);
}

.sub-brands {
  margin-left: 20px;
  margin-top: 5px;
  padding-left: 15px;
  border-left: 1px solid #ddd;
}

.sub-brand {
  font-size: 12px;
}

/* Products Area */
.products-area {
  flex: 1;
  min-width: 0;
}

.products-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  background: #fff;
  padding: 15px 20px;
  border: 1px solid #ddd;
}

.page-title {
  font-size: 20px;
  font-weight: bold;
  margin: 0;
  color: #333;
}

.products-controls {
  display: flex;
  gap: 10px;
}

.sort-select,
.per-page-select {
  padding: 6px 10px;
  border: 1px solid #ddd;
  font-size: 13px;
  cursor: pointer;
}

/* Products Grid */
.products-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 30px;
}

.product-card {
  background: #fff;
  border: 1px solid #ddd;
  transition: box-shadow 0.2s;
}

.product-card:hover {
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.product-image-wrapper {
  position: relative;
  width: 100%;
  height: 200px;
  background: #f9f9f9;
  overflow: hidden;
  cursor: pointer;
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.stock-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  padding: 4px 8px;
  font-size: 11px;
  font-weight: bold;
  text-transform: uppercase;
}

.out-of-stock {
  background: #dc3545;
  color: #fff;
}

.low-stock {
  background: #ffc107;
  color: #333;
}

.product-info {
  padding: 15px;
}

.product-name {
  font-size: 14px;
  font-weight: normal;
  margin: 0 0 10px 0;
  color: #333;
  line-height: 1.4;
  cursor: pointer;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product-name:hover {
  color: #0066cc;
  text-decoration: underline;
}

.product-price {
  font-size: 18px;
  font-weight: bold;
  color: #333;
  margin-bottom: 10px;
}

.product-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.btn-order {
  width: 100%;
  padding: 8px;
  background: #333;
  color: #fff;
  border: none;
  cursor: pointer;
  font-size: 13px;
  text-align: center;
}

.btn-order:hover {
  background: #555;
}

.btn-wishlist {
  width: 100%;
  padding: 6px;
  background: transparent;
  color: #333;
  border: 1px solid #ddd;
  cursor: pointer;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
}

.btn-wishlist:hover {
  background: #f5f5f5;
  border-color: #999;
}

.btn-wishlist svg {
  width: 14px;
  height: 14px;
}

/* Loading */
.loading {
  text-align: center;
  padding: 60px 20px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #333;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: #fff;
  border: 1px solid #ddd;
}

.empty-state p {
  color: #666;
  font-size: 14px;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 5px;
  margin-top: 30px;
}

.page-btn {
  padding: 6px 12px;
  background: #fff;
  border: 1px solid #ddd;
  cursor: pointer;
  font-size: 14px;
}

.page-btn:hover:not(:disabled) {
  background: #f5f5f5;
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-numbers {
  display: flex;
  gap: 5px;
}

.page-number {
  padding: 6px 12px;
  background: #fff;
  border: 1px solid #ddd;
  cursor: pointer;
  font-size: 14px;
}

.page-number:hover {
  background: #f5f5f5;
}

.page-number.active {
  background: #333;
  color: #fff;
  border-color: #333;
}

/* Responsive */
@media (max-width: 1024px) {
  .products-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .content-wrapper {
    flex-direction: column;
  }

  .sidebar {
    width: 100%;
  }

  .products-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
  }

  .header-content {
    flex-wrap: wrap;
  }

  .search-section {
    order: 3;
    width: 100%;
    max-width: 100%;
  }
}

@media (max-width: 480px) {
  .products-grid {
    grid-template-columns: 1fr;
  }
}
</style>
