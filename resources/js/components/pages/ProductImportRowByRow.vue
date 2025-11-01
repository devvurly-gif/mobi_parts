<template>
  <div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Import Products</h1>
        <p class="text-gray-600">Upload an Excel or CSV file to import products one by one with real-time status tracking.</p>
      </div>

      <!-- Upload Section -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Upload File</h2>
        
        <!-- File Upload -->
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Select Excel or CSV File
          </label>
          <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-500 transition-colors duration-200"
               :class="{ 'border-indigo-500 bg-indigo-50': isDragOver }"
               @dragover.prevent="isDragOver = true"
               @dragleave.prevent="isDragOver = false"
               @drop.prevent="handleFileDrop">
            <div class="space-y-1 text-center">
              <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <div class="flex text-sm text-gray-600">
                <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                  <span>Upload a file</span>
                  <input id="file-upload" name="file-upload" type="file" class="sr-only" accept=".xlsx,.xls,.csv" @change="handleFileSelect">
                </label>
                <p class="pl-1">or drag and drop</p>
              </div>
              <p class="text-xs text-gray-500">Excel (.xlsx, .xls) or CSV files only</p>
            </div>
          </div>
        </div>

        <!-- File Info -->
        <div v-if="selectedFile" class="mb-6 p-4 bg-blue-50 rounded-lg">
          <div class="flex items-center">
            <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
              <p class="text-sm font-medium text-blue-800">File Selected: {{ selectedFile.name }}</p>
              <p class="text-xs text-blue-600">Size: {{ formatFileSize(selectedFile.size) }}</p>
            </div>
          </div>
        </div>

        <!-- Category Selection -->
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Category
          </label>
          <select v-model="selectedCategoryId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
            <option value="">Select a category</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </div>

        <!-- Import Options -->
        <div class="mb-6">
          <h3 class="text-sm font-medium text-gray-700 mb-3">Import Options</h3>
          <div class="space-y-2">
            <label class="flex items-center">
              <input type="checkbox" v-model="skipDuplicates" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
              <span class="ml-2 text-sm text-gray-700">Skip duplicate products (same name)</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" v-model="updateExisting" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
              <span class="ml-2 text-sm text-gray-700">Update existing products instead of skipping</span>
            </label>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex space-x-4">
          <button
            @click="parseFile"
            :disabled="!selectedFile || !selectedCategoryId || isProcessing"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg v-if="isProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ isProcessing ? 'Processing...' : 'Parse File' }}
          </button>

          <button
            v-if="parsedData.length > 0"
            @click="startImport"
            :disabled="isImporting"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg v-if="isImporting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ isImporting ? 'Importing...' : `Start Import (${parsedData.length} products)` }}
          </button>

          <button
            v-if="parsedData.length > 0"
            @click="clearData"
            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Clear Data
          </button>
        </div>
      </div>

      <!-- Progress Section -->
      <div v-if="isImporting" class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Import Progress</h3>
        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4">
          <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" :style="{ width: `${importProgress}%` }"></div>
        </div>
        <p class="text-sm text-gray-600">
          {{ currentImportIndex }} of {{ parsedData.length }} products processed
          ({{ importProgress.toFixed(1) }}% complete)
        </p>
      </div>

      <!-- Preview Table -->
      <div v-if="parsedData.length > 0" class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-800">Product Preview</h3>
          <p class="text-sm text-gray-600">Review your products before importing. Status will update as each product is processed.</p>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(product, index) in parsedData" :key="index" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ index + 1 }}</td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ product.name }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ product.description || '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ product.prix_achat }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ product.prix_vente }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product.stock_quantity || 0 }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="product.status === 'pending'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                    Pending
                  </span>
                  <span v-else-if="product.status === 'importing'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <svg class="animate-spin w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Importing
                  </span>
                  <span v-else-if="product.status === 'success'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Success
                  </span>
                  <span v-else-if="product.status === 'error'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    Failed
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ product.message || '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Summary -->
      <div v-if="importComplete" class="bg-white rounded-lg shadow-md p-6 mt-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Import Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="text-center">
            <div class="text-2xl font-bold text-gray-900">{{ summary.total }}</div>
            <div class="text-sm text-gray-500">Total Products</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-green-600">{{ summary.success }}</div>
            <div class="text-sm text-gray-500">Successfully Imported</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-red-600">{{ summary.failed }}</div>
            <div class="text-sm text-gray-500">Failed</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-gray-600">{{ summary.skipped }}</div>
            <div class="text-sm text-gray-500">Skipped</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useCategoryStore } from '../../stores/categoryStore'
import { useProductStore } from '../../stores/productStore'
import { useToast } from 'vue-toastification'
import * as XLSX from 'xlsx'

export default {
  name: 'ProductImportRowByRow',
  setup() {
    const categoryStore = useCategoryStore()
    const productStore = useProductStore()
    const toast = useToast()

    // Reactive data
    const selectedFile = ref(null)
    const selectedCategoryId = ref('')
    const skipDuplicates = ref(true)
    const updateExisting = ref(false)
    const isDragOver = ref(false)
    const isProcessing = ref(false)
    const isImporting = ref(false)
    const parsedData = ref([])
    const currentImportIndex = ref(0)
    const importComplete = ref(false)

    // Computed properties
    const categories = computed(() => categoryStore.categories)
    const importProgress = computed(() => {
      if (parsedData.value.length === 0) return 0
      return (currentImportIndex.value / parsedData.value.length) * 100
    })

    const summary = computed(() => {
      const total = parsedData.value.length
      const success = parsedData.value.filter(p => p.status === 'success').length
      const failed = parsedData.value.filter(p => p.status === 'error').length
      const skipped = parsedData.value.filter(p => p.status === 'skipped').length
      return { total, success, failed, skipped }
    })

    // Methods
    const formatFileSize = (bytes) => {
      if (bytes === 0) return '0 Bytes'
      const k = 1024
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    }

    const handleFileSelect = (event) => {
      const file = event.target.files[0]
      if (file) {
        selectedFile.value = file
        parsedData.value = []
        importComplete.value = false
      }
    }

    const handleFileDrop = (event) => {
      isDragOver.value = false
      const file = event.dataTransfer.files[0]
      if (file && (file.type.includes('sheet') || file.type.includes('csv') || file.name.endsWith('.xlsx') || file.name.endsWith('.xls') || file.name.endsWith('.csv'))) {
        selectedFile.value = file
        parsedData.value = []
        importComplete.value = false
      } else {
        toast.error('Please select a valid Excel or CSV file')
      }
    }

    const parseFile = async () => {
      if (!selectedFile.value) return

      isProcessing.value = true
      try {
        const data = await readFile(selectedFile.value)
        console.log('Raw parsed data:', data)
        parsedData.value = data.map((row, index) => {
          console.log(`Mapping row ${index}:`, row)
          const mappedRow = mapProductData(row)
          console.log(`Mapped row ${index}:`, mappedRow)
          return {
            ...mappedRow,
            status: 'pending',
            message: '',
            rowIndex: index
          }
        })
        toast.success(`Successfully parsed ${data.length} products`)
      } catch (error) {
        toast.error('Failed to parse file: ' + error.message)
        console.error('Parse error:', error)
      } finally {
        isProcessing.value = false
      }
    }

    const readFile = (file) => {
      return new Promise((resolve, reject) => {
        const reader = new FileReader()
        reader.onload = (e) => {
          try {
            const data = e.target.result
            let workbook

            if (file.name.endsWith('.csv')) {
              // Handle CSV
              const lines = data.split('\n')
              const headers = lines[0].split(',').map(h => h.trim().replace(/"/g, ''))
              const rows = lines.slice(1).filter(line => line.trim()).map(line => {
                const values = line.split(',').map(v => v.trim().replace(/"/g, ''))
                const row = {}
                headers.forEach((header, index) => {
                  row[header] = values[index] || ''
                })
                return row
              })
              resolve(rows)
            } else {
              // Handle Excel
              workbook = XLSX.read(data, { type: 'binary' })
              const sheetName = workbook.SheetNames[0]
              const worksheet = workbook.Sheets[sheetName]
              const jsonData = XLSX.utils.sheet_to_json(worksheet)
              resolve(jsonData)
            }
          } catch (error) {
            reject(error)
          }
        }
        reader.onerror = () => reject(new Error('Failed to read file'))
        
        if (file.name.endsWith('.csv')) {
          reader.readAsText(file)
        } else {
          reader.readAsBinaryString(file)
        }
      })
    }

    const mapProductData = (row) => {
      console.log('mapProductData input:', row)
      // Map various column names to our standard format
      const mapping = {
        'product name': 'name',
        'product_name': 'name',
        'name': 'name',
        'product description': 'description',
        'product_description': 'description',
        'description': 'description',
        'barcode/ean13': 'ean13',
        'barcode': 'ean13',
        'ean13': 'ean13',
        'purchase price': 'prix_achat',
        'purchase_price': 'prix_achat',
        'prix_achat': 'prix_achat',
        'sale price': 'prix_vente',
        'sale_price': 'prix_vente',
        'prix_vente': 'prix_vente',
        'stock quantity': 'stock_quantity',
        'stock_quantity': 'stock_quantity',
        'minimum stock': 'min_stock',
        'minimum_stock': 'min_stock',
        'min_stock': 'min_stock',
        'active (true/false)': 'is_active',
        'active': 'is_active',
        'is_active': 'is_active'
      }

      const mappedRow = {}
      Object.keys(row).forEach(key => {
        const normalizedKey = key.toLowerCase().trim()
        if (mapping[normalizedKey]) {
          let value = row[key]
          
          // Clean and convert values
          if (mapping[normalizedKey] === 'prix_achat' || mapping[normalizedKey] === 'prix_vente') {
            value = parseFloat(String(value).replace(',', '.')) || 0
          } else if (mapping[normalizedKey] === 'stock_quantity' || mapping[normalizedKey] === 'min_stock') {
            value = parseInt(String(value).replace(',', '.')) || 0
          } else if (mapping[normalizedKey] === 'is_active') {
            const val = String(value).toLowerCase().trim()
            value = ['true', '1', 'yes', 'oui', 'vrai'].includes(val)
          } else if (mapping[normalizedKey] === 'ean13') {
            // Handle scientific notation
            if (String(value).includes('e') || String(value).includes('E')) {
              value = String(Math.round(parseFloat(value)))
            }
            value = String(value).replace(/[^0-9]/g, '')
          }
          
          mappedRow[mapping[normalizedKey]] = value
        }
      })

      // Add category_id
      mappedRow.category_id = parseInt(selectedCategoryId.value)
      
      console.log('mapProductData output:', mappedRow)
      return mappedRow
    }

    const startImport = async () => {
      if (parsedData.value.length === 0) return

      isImporting.value = true
      currentImportIndex.value = 0
      importComplete.value = false

      for (let i = 0; i < parsedData.value.length; i++) {
        const product = parsedData.value[i]
        
        // Skip if already processed
        if (product.status !== 'pending') continue

        // Update status to importing
        product.status = 'importing'
        product.message = 'Sending to server...'
        currentImportIndex.value = i + 1

        try {
          // Map the product data
          const mappedData = mapProductData(product)
          
          // Validate required fields
          if (!mappedData.name || !mappedData.prix_achat || !mappedData.prix_vente) {
            product.status = 'error'
            product.message = 'Missing required fields (name, purchase price, sale price)'
            continue
          }

          // Create product via API
          const result = await productStore.createProduct(mappedData)
          
          product.status = 'success'
          product.message = 'Successfully imported'
          
        } catch (error) {
          product.status = 'error'
          product.message = error.response?.data?.message || error.message || 'Import failed'
          console.error('Import error for product:', product.name, error)
        }

        // Small delay to show progress
        await new Promise(resolve => setTimeout(resolve, 100))
      }

      isImporting.value = false
      importComplete.value = true
      
      const successCount = parsedData.value.filter(p => p.status === 'success').length
      const errorCount = parsedData.value.filter(p => p.status === 'error').length
      
      toast.success(`Import completed! ${successCount} successful, ${errorCount} failed`)
    }

    const clearData = () => {
      parsedData.value = []
      selectedFile.value = null
      importComplete.value = false
      currentImportIndex.value = 0
    }

    // Load categories on mount
    onMounted(async () => {
      await categoryStore.fetchCategories()
    })

    return {
      selectedFile,
      selectedCategoryId,
      skipDuplicates,
      updateExisting,
      isDragOver,
      isProcessing,
      isImporting,
      parsedData,
      currentImportIndex,
      importComplete,
      categories,
      importProgress,
      summary,
      formatFileSize,
      handleFileSelect,
      handleFileDrop,
      parseFile,
      startImport,
      clearData
    }
  }
}
</script>
