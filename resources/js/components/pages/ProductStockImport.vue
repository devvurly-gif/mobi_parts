<template>
  <div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-gray-900">Import Stock Updates</h1>
        <p class="mt-1 text-sm text-gray-600">Upload an XLS/XLSX/CSV file with columns: <span class="font-mono">ean13</span> and <span class="font-mono">stock_quantity</span>.</p>
      </div>
    </div>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Upload Area -->
      <div class="bg-white p-6 rounded-lg shadow">
        <!-- Actions and Info -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-3">
          <div class="text-sm text-gray-600">
            <div v-if="fileName">Selected: <span class="font-medium">{{ fileName }}</span></div>
            <div v-if="rawCount">Rows detected: <span class="font-medium">{{ rawCount }}</span> | Valid: <span class="font-medium">{{ rows.length }}</span></div>
          </div>
          <div class="space-x-3">
            <button @click="downloadTemplate" :disabled="importing" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50">
              Download Template
            </button>
          </div>
        </div>
        <div
          class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-indigo-400"
          @dragover.prevent
          @drop.prevent="onDrop"
        >
          <div class="flex flex-col items-center">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3 3m0 0l-3-3m3 3V6" />
            </svg>
            <p class="mt-2 text-sm text-gray-600">Drag and drop your file here, or</p>
            <label class="mt-2 inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md cursor-pointer hover:bg-indigo-700">
              <input type="file" class="hidden" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" @change="onFileChange" />
              Choose File
            </label>
          </div>
        </div>

        <div class="mt-6 flex items-center justify-between" v-if="rows.length">
          <div class="text-sm text-gray-600">Parsed rows: <span class="font-semibold">{{ rows.length }}</span></div>
          <div class="space-x-3">
            <button @click="startImport" :disabled="importing || !rows.length" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50">
              <svg v-if="importing" class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              {{ importing ? 'Importing...' : 'Start Import' }}
            </button>
            <button @click="clear" :disabled="importing" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 disabled:opacity-50">Clear</button>
          </div>
        </div>

        <!-- Errors / Hints -->
        <div v-if="lastError" class="mt-4 p-3 rounded bg-red-50 text-red-700 text-sm">
          {{ lastError }}
        </div>
        <div v-else-if="fileName && !rows.length" class="mt-4 p-3 rounded bg-yellow-50 text-yellow-800 text-sm">
          No valid rows parsed. Expected columns: <span class="font-mono">ean13</span> and <span class="font-mono">stock_quantity</span> (aliases: stock, qty, quantity).
        </div>
      </div>

      <!-- Preview Table -->
      <div v-if="rows.length" class="mt-6 bg-white p-4 rounded-lg shadow">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">EAN13</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Stock</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(row, index) in rows" :key="index">
                <td class="px-3 py-2 text-sm text-gray-700">{{ index + 1 }}</td>
                <td class="px-3 py-2 text-sm text-gray-700">{{ row.ean13 || '-' }}</td>
                <td class="px-3 py-2 text-sm text-gray-700">{{ row.stock_quantity }}</td>
                <td class="px-3 py-2 text-sm">
                  <span v-if="row.status === 'passed'" class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Passed</span>
                  <span v-else-if="row.status === 'failed'" class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Failed</span>
                  <span v-else class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">Pending</span>
                </td>
                <td class="px-3 py-2 text-sm text-gray-500">{{ row.message || '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import * as XLSX from 'xlsx'

export default {
  name: 'ProductStockImport',
  data() {
    return {
      rows: [],
      importing: false,
      fileName: '',
      lastError: '',
      rawCount: 0,
    }
  },
  methods: {
    onFileChange(event) {
      const file = event.target.files?.[0]
      if (file) this.readFile(file)
      event.target.value = ''
    },
    downloadTemplate() {
      const rows = [
        { ean13: '1234567890123', stock_quantity: 10 },
        { ean13: '9876543210987', stock_quantity: 25 },
      ]
      const ws = XLSX.utils.json_to_sheet(rows, { header: ['ean13', 'stock_quantity'] })
      const wb = XLSX.utils.book_new()
      XLSX.utils.book_append_sheet(wb, ws, 'StockUpdates')
      XLSX.writeFile(wb, 'stock_import_template.xlsx')
    },
    onDrop(e) {
      const file = e.dataTransfer.files?.[0]
      if (file) this.readFile(file)
    },
    async readFile(file) {
      this.fileName = file.name
      this.lastError = ''
      this.rows = []
      this.rawCount = 0
      try {
        console.log('[StockImport] Reading file:', file.name)
        const data = await file.arrayBuffer()
        const workbook = XLSX.read(data, { type: 'array' })
        const sheetName = workbook.SheetNames[0]
        const sheet = workbook.Sheets[sheetName]
        const raw = XLSX.utils.sheet_to_json(sheet, { defval: '' })
        this.rawCount = raw.length
        const mapped = raw.map(this.mapRow).filter(r => r)
        console.log('[StockImport] Parsed rows:', { raw: raw.length, valid: mapped.length, sample: mapped[0] })
        // Clone to ensure plain, same-realm objects (avoids XrayWrapper issues in Firefox)
        this.rows = mapped.map(r => ({ ...r }))
        if (!mapped.length) {
          this.lastError = 'No valid rows found. Expected columns: id or ean13, and stock_quantity (aliases: stock, qty, quantity).'
        }
      } catch (err) {
        console.error('[StockImport] Parse error:', err)
        this.lastError = 'Failed to read file. Please ensure it is a valid CSV/XLS/XLSX.'
      }
    },
    mapRow(row) {
      console.log('[StockImport] Mapping row:', row)
      
      const get = (keys) => {
        for (const key of keys) {
          const found = Object.keys(row).find(k => k.toString().trim().toLowerCase() === key)
          if (found !== undefined) return row[found]
        }
        return undefined
      }

      const ean = get(['ean13', 'ean', 'barcode'])
      const qtyRaw = get(['stock_quantity', 'stock', 'qty', 'quantity'])

      console.log('[StockImport] Extracted values:', { ean, qtyRaw })

      const qty = this.normalizeNumber(qtyRaw)
      
      console.log('[StockImport] Normalized qty:', qty)
      
      if (!ean || ean === '') {
        console.log('[StockImport] Row rejected: no ean13')
        return null
      }
      if (qty === null || isNaN(qty)) {
        console.log('[StockImport] Row rejected: invalid quantity')
        return null
      }

      // Ensure EAN13 is exactly 13 digits, padding with leading zeros if needed
      let ean13Formatted = String(ean).trim()
      if (ean13Formatted.length < 13) {
        ean13Formatted = ean13Formatted.padStart(13, '0')
      } else if (ean13Formatted.length > 13) {
        ean13Formatted = ean13Formatted.slice(-13) // Take last 13 digits
      }

      const mapped = {
        ean13: ean13Formatted,
        stock_quantity: Number(qty),
        status: 'pending',
        message: ''
      }
      
      console.log('[StockImport] Mapped result:', mapped)
      return mapped
    },
    normalizeNumber(value) {
      if (value === null || value === undefined) return null
      if (typeof value === 'number') return Math.round(value)
      const s = String(value).trim()
      if (!s) return null
      const normalized = s.replace(/\s+/g, '').replace(',', '.')
      const n = Number(normalized)
      return Number.isFinite(n) ? Math.round(n) : null
    },
    async startImport() {
      if (!this.rows.length) return
      this.importing = true
      console.log('[StockImport] Starting import of', this.rows.length, 'rows')
      
      for (let i = 0; i < this.rows.length; i++) {
        const row = this.rows[i]
        if (row.status === 'passed') continue
        
        console.log('[StockImport] Processing row', i + 1, ':', row)
        
        try {
          const payload = {
            stock_quantity: row.stock_quantity,
            ean13: row.ean13,
          }
          
          console.log('[StockImport] Sending payload:', payload)
          console.log('[StockImport] EAN13 length:', payload.ean13.length)
          
          const res = await window.axios.post('/api/products/stock-by-code', payload)
          
          console.log('[StockImport] Response for row', i + 1, ':', res.data)
          
          row.status = 'passed'
          row.message = 'Updated'
        } catch (err) {
          console.error('[StockImport] Error for row', i + 1, ':', err)
          row.status = 'failed'
          row.message = err?.response?.data?.message || err.message || 'Error'
        }
        
        // Replace item to ensure reactivity without forcing updates
        this.rows.splice(i, 1, { ...row })
        await new Promise(r => setTimeout(r, 100))
      }
      
      console.log('[StockImport] Import completed')
      this.importing = false
    },
    clear() {
      this.rows = []
      this.importing = false
      this.fileName = ''
      this.lastError = ''
      this.rawCount = 0
    }
  }
}
</script>


