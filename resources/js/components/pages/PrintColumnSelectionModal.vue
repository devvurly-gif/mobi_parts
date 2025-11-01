<template>
  <div v-if="modelValue" class="fixed z-50 inset-0 overflow-y-auto" @click.self="closeModal">
    <div class="flex items-center justify-center min-h-screen px-4">
      <div class="fixed inset-0 bg-black bg-opacity-50"></div>
      
      <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b">
          <h3 class="text-lg font-semibold text-gray-900">Select Columns to Print</h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Column Selection -->
        <div class="p-6 max-h-80 overflow-y-auto">
          <div class="space-y-2">
            <label
              v-for="col in columns"
              :key="col.key"
              class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer"
            >
              <input
                v-model="selectedColumns"
                :value="col.key"
                type="checkbox"
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
              />
              <span class="ml-3 text-sm text-gray-700">{{ col.label }}</span>
            </label>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between p-6 border-t bg-gray-50">
          <div>
            <button @click="selectAll" class="text-sm text-indigo-600 hover:text-indigo-800 mr-4">Select All</button>
            <button @click="deselectAll" class="text-sm text-gray-600 hover:text-gray-800">Deselect All</button>
          </div>
          <div class="flex gap-3">
            <button
              @click="closeModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              @click="handlePrint"
              :disabled="selectedColumns.length === 0"
              class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Print
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue'
import { useToast } from 'vue-toastification'

export default {
  name: 'PrintColumnSelectionModal',
  props: {
    modelValue: { type: Boolean, default: false },
    products: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) }
  },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    const toast = useToast()

    const columns = [
      { key: 'id', label: 'ID', get: p => p.id },
      { key: 'name', label: 'Name', get: p => p.name || '' },
      { key: 'category', label: 'Category', get: p => p.category?.name || 'No Category' },
      { key: 'ean13', label: 'EAN13', get: p => p.ean13 || '' },
      { key: 'description', label: 'Description', get: p => (p.description || '').replace(/"/g, "'") },
      { key: 'prix_achat', label: 'Purchase Price', get: p => `$${parseFloat(p.prix_achat || 0).toFixed(2)}`, align: 'right' },
      { key: 'prix_vente', label: 'Sale Price', get: p => `$${parseFloat(p.prix_vente || 0).toFixed(2)}`, align: 'right' },
      { key: 'stock_quantity', label: 'Stock', get: p => p.stock_quantity || 0, align: 'right' },
      { key: 'min_stock', label: 'Min Stock', get: p => p.min_stock || 0, align: 'right' },
      { key: 'status', label: 'Status', get: p => p.is_active ? 'Active' : 'Inactive', class: p => p.is_active ? 'status-active' : 'status-inactive' },
    ]

    const selectedColumns = ref([])

    watch(() => props.modelValue, (isOpen) => {
      if (isOpen) selectedColumns.value = []
    })

    const closeModal = () => emit('update:modelValue', false)
    const selectAll = () => selectedColumns.value = columns.map(c => c.key)
    const deselectAll = () => selectedColumns.value = []

    const handlePrint = () => {
      if (selectedColumns.value.length === 0) {
        toast.error('Please select at least one column')
        return
      }
      if (!props.products?.length) {
        toast.error('No products to print')
        return
      }

      const printWindow = window.open('', '_blank')
      const date = new Date().toLocaleDateString()
      const cols = columns.filter(c => selectedColumns.value.includes(c.key))
      
      const headers = cols.map(c => `<th class="${c.align === 'right' ? 'text-right' : ''}">${c.label}</th>`).join('')
      const rows = props.products.map(p => {
        const cells = cols.map(c => {
          const value = c.get(p)
          const align = c.align === 'right' ? 'text-right' : ''
          const cellClass = c.class ? c.class(p) : ''
          return `<td class="${align} ${cellClass}">${value}</td>`
        }).join('')
        return `<tr>${cells}</tr>`
      }).join('')

      const html = `<!DOCTYPE html><html><head><title>Products - ${date}</title><style>
        @page { size: A4 landscape; margin: 1cm; }
        body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
        h1 { color: #1f2937; margin-bottom: 10px; }
        .meta { color: #6b7280; margin-bottom: 20px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; }
        th { background: #f3f4f6; font-weight: bold; }
        tr:nth-child(even) { background: #f9fafb; }
        .status-active { color: #059669; font-weight: bold; }
        .status-inactive { color: #dc2626; font-weight: bold; }
        .text-right { text-align: right; }
      </style></head><body>
        <h1>Products Inventory</h1>
        <div class="meta">
          Date: ${date} | Total: ${props.products.length}${props.filters?.search ? ` | Filter: ${props.filters.search}` : ''}
        </div>
        <table><thead><tr>${headers}</tr></thead><tbody>${rows}</tbody></table>
      </body></html>`

      printWindow.document.write(html)
      printWindow.document.close()
      printWindow.onload = () => setTimeout(() => printWindow.print(), 250)
      toast.success(`Printing ${props.products.length} products`)
      closeModal()
    }

    return {
      columns,
      selectedColumns,
      closeModal,
      selectAll,
      deselectAll,
      handlePrint
    }
  }
}
</script>

