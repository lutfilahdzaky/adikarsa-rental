<script setup lang="ts">
import { Head, usePage, Link, router } from '@inertiajs/vue3'
import { Pencil, Search, Eye, Plus } from '@lucide/vue'
import type { ColumnDef } from '@tanstack/vue-table'
import {
  FlexRender,
  getCoreRowModel,
  getExpandedRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  getFilteredRowModel,
  useVueTable,
} from '@tanstack/vue-table'
import { h, ref, computed } from 'vue'

import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

const valueUpdater = (updaterOrValue: any, refVar: any) => {
  if (typeof updaterOrValue === 'function') {
    refVar.value = updaterOrValue(refVar.value)

    return
  }

  refVar.value = updaterOrValue
}

interface RentalDetail {
  id: number
  heavy_equipment: {
    id: number
    name: string
  }
}

interface Rental {
  id: number
  user?: {
    name: string
    email: string
  }
  start_date: string
  end_date: string
  total_price: number
  status: string
  rental_details: RentalDetail[]
}

const page = usePage<{ rentals: Rental[]; isAdmin: boolean }>()
const data = computed(() => page.props.rentals ?? [])
const isAdmin = computed(() => page.props.isAdmin)

const handleCancel = async (rentalId: number) => {
  if (!confirm('Cancel this rental request?')) {
    return
  }

  await router.delete(`/rentals/${rentalId}`)
}

const formatDate = (dateString: string) => {
  if (!dateString) {
    return '-'
  }

  const date = new Date(dateString)

  if (Number.isNaN(date.getTime())) {
    return dateString
  }

  const day = String(date.getDate()).padStart(2, '0')
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const year = String(date.getFullYear())

  return `${day}-${month}-${year}`
}

const getStatusBadgeVariant = (status: string) => {
  switch (status) {
    case 'Menunggu':
      return 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
    case 'Disetujui':
      return 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
    case 'Sedang Disewa':
      return 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
    case 'Selesai':
      return 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
    case 'Ditolak':
      return 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
    default:
      return 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
  }
}

const columns: ColumnDef<Rental>[] = [
  {
    accessorKey: 'id',
    header: 'ID',
    cell: ({ row }: any) => h('div', { class: 'font-medium text-muted-foreground' }, `#${row.getValue('id')}`),
  },
  ...(isAdmin.value
    ? [
        {
          id: 'customer',
          header: 'Customer',
          accessorFn: (row: Rental) => row.user?.name ?? '-',
          cell: ({ getValue }: any) => getValue() ?? '-',
        },
      ]
    : []),
  {
    accessorKey: 'start_date',
    header: 'Start',
    cell: ({ row }: any) => formatDate(row.getValue('start_date')),
  },
  {
    accessorKey: 'end_date',
    header: 'End',
    cell: ({ row }: any) => formatDate(row.getValue('end_date')),
  },
  {
    accessorKey: 'total_price',
    header: 'Total Price',
    cell: ({ row }: any) => {
      const amount = Number.parseInt(String(row.getValue('total_price')))

      return h('div', { class: 'text-left font-medium min-w-[160px]' }, `Rp${amount.toLocaleString()}`)
    },
  },
  {
    accessorKey: 'status',
    header: 'Status',
    cell: ({ row }: any) => h('div', { class: getStatusBadgeVariant(row.getValue('status')) }, row.getValue('status')),
  },

  {
    id: 'actions',
    enableHiding: false,
    cell: ({ row }: any) => {
      const rental = row.original

      return h('div', { class: 'flex items-center gap-2 min-w-[120px]' }, [
        !isAdmin.value
          ? h(Link, { href: `/rentals/${rental.id}`, class: 'inline-flex items-center justify-center rounded p-1.5 hover:bg-muted' }, [
              h(Eye, { class: 'h-4 w-4 text-slate-600' }),
            ])
          : null,
        isAdmin.value
          ? h(Link, { href: `/rentals/${rental.id}/edit`, class: 'inline-flex items-center justify-center rounded p-1.5 hover:bg-muted' }, [
              h(Pencil, { class: 'h-4 w-4 text-blue-600' }),
            ])
          : null,
        !isAdmin.value && rental.status === 'Menunggu'
          ? h(Button, {
              variant: 'ghost',
              size: 'sm',
              class: 'text-destructive hover:bg-destructive/10',
              onClick: () => handleCancel(rental.id),
            }, 'Cancel')
          : null,
      ])
    },
  },
]

const sorting = ref([])
const columnFilters = ref([])
const columnVisibility = ref({})
const expanded = ref({} as any)

const table = useVueTable({
  data: data.value,
  columns,
  getCoreRowModel: getCoreRowModel(),
  getPaginationRowModel: getPaginationRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
  getExpandedRowModel: getExpandedRowModel(),
  onSortingChange: (updaterOrValue: any) => valueUpdater(updaterOrValue, sorting),
  onColumnFiltersChange: (updaterOrValue: any) => valueUpdater(updaterOrValue, columnFilters),
  onColumnVisibilityChange: (updaterOrValue: any) => valueUpdater(updaterOrValue, columnVisibility),
  onExpandedChange: (updaterOrValue: any) => valueUpdater(updaterOrValue, expanded),
  state: {
    get sorting() {
      return sorting.value
    },
    get columnFilters() {
      return columnFilters.value
    },
    get columnVisibility() {
      return columnVisibility.value
    },
    get expanded() {
      return expanded.value
    },
  },
})

const exportToCsv = () => {
  const rows = data.value as Rental[]

  if (!rows || !rows.length) {
    alert('No rentals to export.')
    return
  }

  const includeCustomer = isAdmin.value === true
  const baseHeaders = ['id']
  if (includeCustomer) {
    baseHeaders.push('customer_name', 'customer_email')
  }
  baseHeaders.push('start_date', 'end_date', 'total_price', 'status', 'equipments')

  const csvRows = [baseHeaders.join(',')]

  for (const r of rows) {
    const equipmentNames = (r.rental_details || []).map((d) => d.heavy_equipment?.name ?? '').join('; ')
    const values: any[] = [r.id]

    if (includeCustomer) {
      values.push(r.user?.name ?? '', r.user?.email ?? '')
    }

    values.push(r.start_date ?? '', r.end_date ?? '', r.total_price ?? '', r.status ?? '', equipmentNames)

    const line = values
      .map((v) => {
        if (v === null || typeof v === 'undefined') return '""'
        const s = String(v).replace(/"/g, '""')
        return `"${s}"`
      })
      .join(',')

    csvRows.push(line)
  }

  const csv = csvRows.join('\n')
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `rentals_history_${new Date().toISOString().slice(0, 10)}.csv`
  document.body.appendChild(a)
  a.click()
  a.remove()
  URL.revokeObjectURL(url)
}

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'History',
        href: '/history',
      },
    ],
  },
})
</script>

<template>
  <Head title="History" />

  <div class="flex flex-col gap-6 px-6 py-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
      <Heading variant="small" title="History" description="Track rental requests and approval status." />

      <div class="flex flex-col gap-3 sm:flex-1 sm:min-w-0 sm:flex-row sm:items-center sm:justify-end">
        <div class="relative w-full max-w-sm">
          <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-muted-foreground">
            <Search class="h-4 w-4" />
          </span>
          <Input class="w-full pl-9" placeholder="Filter rentals..." />
        </div>

        <Link href="/rentals/create" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 whitespace-nowrap">
          <Plus class="h-4 w-4" />
          New Rental
        </Link>
      </div>
    </div>

    <div class="w-full overflow-x-auto rounded-md border border-sidebar-border/70">
      <Table class="w-full border-collapse" :style="{ tableLayout: 'auto' }">
        <TableHeader>
          <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
            <TableHead class="w-auto px-4" v-for="header in headerGroup.headers" :key="header.id">
              <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="table.getRowModel().rows?.length">
            <template v-for="row in table.getRowModel().rows" :key="row.id">
              <TableRow :data-state="row.getIsSelected() && 'selected'">
                <TableCell class="w-auto px-4" v-for="cell in row.getVisibleCells()" :key="cell.id">
                  <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                </TableCell>
              </TableRow>
              <TableRow v-if="row.getIsExpanded()">
                <TableCell :colspan="row.getAllCells().length">
                  {{ JSON.stringify(row.original) }}
                </TableCell>
              </TableRow>
            </template>
          </template>
          <TableRow v-else>
            <TableCell :colspan="columns.length" class="h-24 text-center">No rentals found.</TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <div class="flex items-center justify-between">
      <div>
        <Button variant="outline" size="sm" @click="exportToCsv()">Export CSV</Button>
      </div>

      <div class="flex items-center space-x-2">
        <Button variant="outline" size="sm" :disabled="!table.getCanPreviousPage()" @click="table.previousPage()">Previous</Button>
        <Button variant="outline" size="sm" :disabled="!table.getCanNextPage()" @click="table.nextPage()">Next</Button>
      </div>
    </div>
  </div>
</template>
