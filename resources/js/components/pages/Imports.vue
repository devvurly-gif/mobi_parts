<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900">Imports</h1>
        <p class="mt-1 text-sm text-gray-600">Import products, stock, brands, and categories from Excel or CSV files.</p>
      </div>
    </div>

    <!-- Tabs -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              activeTab === tab.id
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            {{ tab.name }}
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div class="mt-6">
        <!-- Products Import -->
        <ProductImportRowByRow v-if="activeTab === 'products'" />

        <!-- Stock Import -->
        <ProductStockImport v-if="activeTab === 'stock'" />

        <!-- Brands Import -->
        <div v-if="activeTab === 'brands'" class="bg-white rounded-lg shadow p-6">
          <div class="mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Import Brands</h2>
            <p class="mt-1 text-sm text-gray-600">Import brands from Excel or CSV file. Required columns: <span class="font-mono">name</span>. Optional: <span class="font-mono">description</span>, <span class="font-mono">logo</span>, <span class="font-mono">is_active</span>.</p>
          </div>

          <!-- File Upload -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Select Excel or CSV File
            </label>
            <div
              class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-500 transition-colors duration-200"
              :class="{ 'border-indigo-500 bg-indigo-50': isDragOver }"
              @dragover.prevent="isDragOver = true"
              @dragleave.prevent="isDragOver = false"
              @drop.prevent="handleFileDrop"
            >
              <div class="space-y-1 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                  <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="flex text-sm text-gray-600">
                  <label for="brand-file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                    <span>Upload a file</span>
                    <input id="brand-file-upload" name="brand-file-upload" type="file" class="sr-only" accept=".xlsx,.xls,.csv" @change="handleBrandFileSelect">
                  </label>
                  <p class="pl-1">or drag and drop</p>
                </div>
                <p class="text-xs text-gray-500">Excel (.xlsx, .xls) or CSV files only</p>
              </div>
            </div>
          </div>

          <!-- File Info -->
          <div v-if="brandFile" class="mb-6 p-4 bg-blue-50 rounded-lg">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                  <p class="text-sm font-medium text-blue-800">File Selected: {{ brandFile.name }}</p>
                  <p class="text-xs text-blue-600">Size: {{ formatFileSize(brandFile.size) }}</p>
                </div>
              </div>
              <button
                @click="brandFile = null; brandParsedData = []"
                class="text-blue-600 hover:text-blue-800"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
          </div>

          <!-- Import Options -->
          <div class="mb-6">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Import Options</h3>
            <div class="space-y-2">
              <label class="flex items-center">
                <input type="checkbox" v-model="brandSkipDuplicates" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-gray-700">Skip duplicate brands (same name)</span>
              </label>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex space-x-4 mb-6">
            <button
              @click="parseBrandFile"
              :disabled="!brandFile || isProcessingBrands"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="isProcessingBrands" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ isProcessingBrands ? 'Processing...' : 'Parse File' }}
            </button>

            <button
              v-if="brandParsedData.length > 0"
              @click="startBrandImport"
              :disabled="isImportingBrands"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="isImportingBrands" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ isImportingBrands ? 'Importing...' : `Import ${brandParsedData.length} Brands` }}
            </button>

            <button
              v-if="brandParsedData.length > 0"
              @click="clearBrandData"
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Clear
            </button>
          </div>

          <!-- Preview Table -->
          <div v-if="brandParsedData.length > 0" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(brand, index) in brandParsedData" :key="index">
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ index + 1 }}</td>
                  <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ brand.name || '-' }}</td>
                  <td class="px-4 py-3 text-sm text-gray-500">{{ brand.description || '-' }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <span v-if="brand.status === 'success'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Success</span>
                    <span v-else-if="brand.status === 'error'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Error</span>
                    <span v-else-if="brand.status === 'importing'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Importing</span>
                    <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Pending</span>
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-500">{{ brand.message || '-' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Categories Import -->
        <div v-if="activeTab === 'categories'" class="bg-white rounded-lg shadow p-6">
          <div class="mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Import Categories</h2>
            <p class="mt-1 text-sm text-gray-600">Import categories from Excel or CSV file. Required columns: <span class="font-mono">name</span>. Optional: <span class="font-mono">is_active</span>.</p>
          </div>

          <!-- File Upload -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Select Excel or CSV File
            </label>
            <div
              class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-500 transition-colors duration-200"
              :class="{ 'border-indigo-500 bg-indigo-50': isDragOverCategory }"
              @dragover.prevent="isDragOverCategory = true"
              @dragleave.prevent="isDragOverCategory = false"
              @drop.prevent="handleCategoryFileDrop"
            >
              <div class="space-y-1 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                  <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="flex text-sm text-gray-600">
                  <label for="category-file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                    <span>Upload a file</span>
                    <input id="category-file-upload" name="category-file-upload" type="file" class="sr-only" accept=".xlsx,.xls,.csv" @change="handleCategoryFileSelect">
                  </label>
                  <p class="pl-1">or drag and drop</p>
                </div>
                <p class="text-xs text-gray-500">Excel (.xlsx, .xls) or CSV files only</p>
              </div>
            </div>
          </div>

          <!-- File Info -->
          <div v-if="categoryFile" class="mb-6 p-4 bg-blue-50 rounded-lg">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                  <p class="text-sm font-medium text-blue-800">File Selected: {{ categoryFile.name }}</p>
                  <p class="text-xs text-blue-600">Size: {{ formatFileSize(categoryFile.size) }}</p>
                </div>
              </div>
              <button
                @click="categoryFile = null; categoryParsedData = []"
                class="text-blue-600 hover:text-blue-800"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
          </div>

          <!-- Import Options -->
          <div class="mb-6">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Import Options</h3>
            <div class="space-y-2">
              <label class="flex items-center">
                <input type="checkbox" v-model="categorySkipDuplicates" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-gray-700">Skip duplicate categories (same name)</span>
              </label>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex space-x-4 mb-6">
            <button
              @click="parseCategoryFile"
              :disabled="!categoryFile || isProcessingCategories"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="isProcessingCategories" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ isProcessingCategories ? 'Processing...' : 'Parse File' }}
            </button>

            <button
              v-if="categoryParsedData.length > 0"
              @click="startCategoryImport"
              :disabled="isImportingCategories"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="isImportingCategories" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ isImportingCategories ? 'Importing...' : `Import ${categoryParsedData.length} Categories` }}
            </button>

            <button
              v-if="categoryParsedData.length > 0"
              @click="clearCategoryData"
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Clear
            </button>
          </div>

          <!-- Preview Table -->
          <div v-if="categoryParsedData.length > 0" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(category, index) in categoryParsedData" :key="index">
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ index + 1 }}</td>
                  <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ category.name || '-' }}</td>
                  <td class="px-4 py-3 text-sm text-gray-500">{{ category.is_active !== false ? 'Yes' : 'No' }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <span v-if="category.status === 'success'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Success</span>
                    <span v-else-if="category.status === 'error'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Error</span>
                    <span v-else-if="category.status === 'importing'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Importing</span>
                    <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Pending</span>
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-500">{{ category.message || '-' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import * as XLSX from 'xlsx'
import { useBrandStore } from '../../stores/brandStore'
import { useCategoryStore } from '../../stores/categoryStore'
import { useToast } from 'vue-toastification'
import ProductImportRowByRow from './ProductImportRowByRow.vue'
import ProductStockImport from './ProductStockImport.vue'

export default {
  name: 'Imports',
  components: {
    ProductImportRowByRow,
    ProductStockImport
  },
  setup() {
    const brandStore = useBrandStore()
    const categoryStore = useCategoryStore()
    const toast = useToast()

    const activeTab = ref('products')
    const tabs = [
      { id: 'products', name: 'Products' },
      { id: 'stock', name: 'Stock' },
      { id: 'brands', name: 'Brands' },
      { id: 'categories', name: 'Categories' }
    ]

    // Brand import state
    const brandFile = ref(null)
    const isDragOver = ref(false)
    const isProcessingBrands = ref(false)
    const isImportingBrands = ref(false)
    const brandParsedData = ref([])
    const brandSkipDuplicates = ref(true)

    // Category import state
    const categoryFile = ref(null)
    const isDragOverCategory = ref(false)
    const isProcessingCategories = ref(false)
    const isImportingCategories = ref(false)
    const categoryParsedData = ref([])
    const categorySkipDuplicates = ref(true)

    const formatFileSize = (bytes) => {
      if (bytes === 0) return '0 Bytes'
      const k = 1024
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    }

    // Brand file handling
    const handleBrandFileSelect = (event) => {
      const file = event.target.files[0]
      if (file) {
        brandFile.value = file
        brandParsedData.value = []
      }
    }

    const handleFileDrop = (event) => {
      isDragOver.value = false
      const file = event.dataTransfer.files[0]
      if (file && (file.type.includes('sheet') || file.name.endsWith('.csv'))) {
        brandFile.value = file
        brandParsedData.value = []
      }
    }

    const parseBrandFile = async () => {
      if (!brandFile.value) return

      isProcessingBrands.value = true
      brandParsedData.value = []

      try {
        const file = brandFile.value
        const data = await file.arrayBuffer()
        const workbook = XLSX.read(data, { type: 'array' })
        const firstSheet = workbook.Sheets[workbook.SheetNames[0]]
        const jsonData = XLSX.utils.sheet_to_json(firstSheet)

        brandParsedData.value = jsonData.map((row, index) => {
          const name = row.name || row.Name || row.NAME || ''
          const description = row.description || row.Description || row.DESCRIPTION || ''
          const logo = row.logo || row.Logo || row.LOGO || ''
          const is_active = row.is_active !== undefined ? row.is_active : (row['is_active'] !== undefined ? row['is_active'] : true)

          return {
            name: String(name).trim(),
            description: description ? String(description).trim() : null,
            logo: logo ? String(logo).trim() : null,
            is_active: is_active === true || is_active === 'true' || is_active === 1 || is_active === '1',
            status: 'pending',
            message: ''
          }
        }).filter(brand => brand.name) // Filter out rows without name

        toast.success(`Parsed ${brandParsedData.value.length} brands from file`)
      } catch (error) {
        toast.error('Failed to parse file: ' + error.message)
        console.error('Parse error:', error)
      } finally {
        isProcessingBrands.value = false
      }
    }

    const startBrandImport = async () => {
      if (brandParsedData.value.length === 0) return

      isImportingBrands.value = true

      for (let i = 0; i < brandParsedData.value.length; i++) {
        const brand = brandParsedData.value[i]
        if (brand.status !== 'pending') continue

        brand.status = 'importing'
        brand.message = 'Sending to server...'

        try {
          // Check for duplicates if enabled
          if (brandSkipDuplicates.value) {
            const existing = brandStore.brands.find(b => b.name.toLowerCase() === brand.name.toLowerCase())
            if (existing) {
              brand.status = 'error'
              brand.message = 'Duplicate brand name (skipped)'
              continue
            }
          }

          const brandData = {
            name: brand.name,
            description: brand.description,
            logo: brand.logo,
            is_active: brand.is_active
          }

          await brandStore.createBrand(brandData)
          brand.status = 'success'
          brand.message = 'Successfully imported'
        } catch (error) {
          brand.status = 'error'
          brand.message = error.response?.data?.message || error.message || 'Import failed'
        }

        await new Promise(resolve => setTimeout(resolve, 100))
      }

      isImportingBrands.value = false
      const successCount = brandParsedData.value.filter(b => b.status === 'success').length
      const errorCount = brandParsedData.value.filter(b => b.status === 'error').length
      toast.success(`Brand import completed! ${successCount} successful, ${errorCount} failed`)
    }

    const clearBrandData = () => {
      brandFile.value = null
      brandParsedData.value = []
    }

    // Category file handling
    const handleCategoryFileSelect = (event) => {
      const file = event.target.files[0]
      if (file) {
        categoryFile.value = file
        categoryParsedData.value = []
      }
    }

    const handleCategoryFileDrop = (event) => {
      isDragOverCategory.value = false
      const file = event.dataTransfer.files[0]
      if (file && (file.type.includes('sheet') || file.name.endsWith('.csv'))) {
        categoryFile.value = file
        categoryParsedData.value = []
      }
    }

    const parseCategoryFile = async () => {
      if (!categoryFile.value) return

      isProcessingCategories.value = true
      categoryParsedData.value = []

      try {
        const file = categoryFile.value
        const data = await file.arrayBuffer()
        const workbook = XLSX.read(data, { type: 'array' })
        const firstSheet = workbook.Sheets[workbook.SheetNames[0]]
        const jsonData = XLSX.utils.sheet_to_json(firstSheet)

        categoryParsedData.value = jsonData.map((row, index) => {
          const name = row.name || row.Name || row.NAME || ''
          const is_active = row.is_active !== undefined ? row.is_active : (row['is_active'] !== undefined ? row['is_active'] : true)

          return {
            name: String(name).trim(),
            is_active: is_active === true || is_active === 'true' || is_active === 1 || is_active === '1',
            status: 'pending',
            message: ''
          }
        }).filter(category => category.name) // Filter out rows without name

        toast.success(`Parsed ${categoryParsedData.value.length} categories from file`)
      } catch (error) {
        toast.error('Failed to parse file: ' + error.message)
        console.error('Parse error:', error)
      } finally {
        isProcessingCategories.value = false
      }
    }

    const startCategoryImport = async () => {
      if (categoryParsedData.value.length === 0) return

      isImportingCategories.value = true

      for (let i = 0; i < categoryParsedData.value.length; i++) {
        const category = categoryParsedData.value[i]
        if (category.status !== 'pending') continue

        category.status = 'importing'
        category.message = 'Sending to server...'

        try {
          // Check for duplicates if enabled
          if (categorySkipDuplicates.value) {
            const existing = categoryStore.categories.find(c => c.name.toLowerCase() === category.name.toLowerCase())
            if (existing) {
              category.status = 'error'
              category.message = 'Duplicate category name (skipped)'
              continue
            }
          }

          const categoryData = {
            name: category.name,
            is_active: category.is_active
          }

          await categoryStore.createCategory(categoryData)
          category.status = 'success'
          category.message = 'Successfully imported'
        } catch (error) {
          category.status = 'error'
          category.message = error.response?.data?.message || error.message || 'Import failed'
        }

        await new Promise(resolve => setTimeout(resolve, 100))
      }

      isImportingCategories.value = false
      const successCount = categoryParsedData.value.filter(c => c.status === 'success').length
      const errorCount = categoryParsedData.value.filter(c => c.status === 'error').length
      toast.success(`Category import completed! ${successCount} successful, ${errorCount} failed`)
    }

    const clearCategoryData = () => {
      categoryFile.value = null
      categoryParsedData.value = []
    }

    return {
      activeTab,
      tabs,
      // Brand imports
      brandFile,
      isDragOver,
      isProcessingBrands,
      isImportingBrands,
      brandParsedData,
      brandSkipDuplicates,
      handleBrandFileSelect,
      handleFileDrop,
      parseBrandFile,
      startBrandImport,
      clearBrandData,
      // Category imports
      categoryFile,
      isDragOverCategory,
      isProcessingCategories,
      isImportingCategories,
      categoryParsedData,
      categorySkipDuplicates,
      handleCategoryFileSelect,
      handleCategoryFileDrop,
      parseCategoryFile,
      startCategoryImport,
      clearCategoryData,
      formatFileSize
    }
  }
}
</script>

