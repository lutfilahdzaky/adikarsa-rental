<script setup lang="ts">
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { Pencil, Trash2, Search, Plus } from '@lucide/vue';
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

// local valueUpdater implementation
const valueUpdater = (updaterOrValue: any, refVar: any) => {
    if (typeof updaterOrValue === 'function') {
        refVar.value = updaterOrValue(refVar.value)
        return
    }

    refVar.value = updaterOrValue
}

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

// reuse types from User shape
export interface Customer {
    id: number
    name: string
    email: string
    phone?: string | null
    role: string
}

const page = usePage<{ customers: Customer[] }>()
const data = computed(() => page.props.customers ?? [])

const columns: ColumnDef<Customer>[] = [
    {
        accessorKey: 'id',
        header: 'ID',
        cell: ({ row }: any) => h('div', { class: 'font-medium text-muted-foreground' }, `#${row.getValue('id')}`),
    },
    {
        accessorKey: 'name',
        header: 'Name',
        cell: ({ row }: any) => h('div', { class: 'font-medium min-w-[200px]' }, row.getValue('name')),
    },
    { accessorKey: 'email', header: 'Email', cell: ({ row }: any) => row.getValue('email') },
    { accessorKey: 'phone', header: 'Phone', cell: ({ row }: any) => row.getValue('phone') ?? '-' },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }: any) => {
            const customer = row.original

            const handleDelete = (id: number) => {
                if (!confirm('Are you sure you want to delete this customer?')) return

                router.delete(`/customers/${id}`, {
                    onSuccess: () => router.get('/customers'),
                })
            }

            return h('div', { class: 'flex items-center gap-2 min-w-[100px]' }, [
                h(Link, { href: `/customers/${customer.id}/edit`, class: 'inline-flex items-center justify-center rounded p-1.5 hover:bg-muted' }, [
                    h(Pencil, { class: 'h-4 w-4 text-blue-600' }),
                ]),
                h('button', { type: 'button', class: 'inline-flex items-center justify-center rounded p-1.5 hover:bg-muted', onClick: () => handleDelete(customer.id) }, [
                    h(Trash2, { class: 'h-4 w-4 text-red-600' }),
                ]),
            ])
        },
    },
]

const sorting = ref([])
const columnFilters = ref([])
const columnVisibility = ref({} as any)

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

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Customers', href: '/customers' },
        ],
    },
})
</script>

<template>
    <Head title="Customers" />

    <div class="flex flex-col gap-6 px-6 py-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <Heading variant="small" title="Customers" description="List of registered customers." />

            <div class="flex flex-col gap-3 sm:flex-1 sm:min-w-0 sm:flex-row sm:items-center sm:justify-end">
            <div class="relative w-full max-w-sm">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-muted-foreground">
                    <Search class="h-4 w-4" />
                </span>
                <Input
                    class="w-full pl-9"
                    placeholder="Filter names..."
                    :model-value="table.getColumn('name')?.getFilterValue() as string"
                    @update:model-value=" table.getColumn('name')?.setFilterValue($event)"
                />
            </div>

            <Link href="/customers/create" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 whitespace-nowrap">
                <Plus class="h-4 w-4" />
                Add Customer
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
                        </template>
                        <template v-for="row in table.getRowModel().rows" :key="`expanded-${row.id}`">
                            <TableRow v-if="row.getIsExpanded()">
                                <TableCell :colspan="row.getAllCells().length">
                                    {{ JSON.stringify(row.original) }}
                                </TableCell>
                            </TableRow>
                        </template>
                    </template>
                    <TableRow v-else>
                        <TableCell :colspan="columns.length" class="h-24 text-center">No results.</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
        
        <div class="flex items-center justify-end space-x-2">
            <div class="space-x-2">
                <Button variant="outline" size="sm" :disabled="!table.getCanPreviousPage()" @click="table.previousPage()">Previous</Button>
                <Button variant="outline" size="sm" :disabled="!table.getCanNextPage()" @click="table.nextPage()">Next</Button>
            </div>
        </div>
    </div>
</template>
