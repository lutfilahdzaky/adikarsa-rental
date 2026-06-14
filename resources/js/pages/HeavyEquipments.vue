<script setup lang="ts">
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { Pencil, Search, Trash2, Plus } from '@lucide/vue';
import type {
    ColumnDef,
    ColumnFiltersState,
    ExpandedState,
    SortingState,
    VisibilityState,
} from '@tanstack/vue-table'
import {
    FlexRender,
    getCoreRowModel,
    getExpandedRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
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

// local valueUpdater implementation
const valueUpdater = (updaterOrValue: any, refVar: any) => {
    if (typeof updaterOrValue === 'function') {
        refVar.value = updaterOrValue(refVar.value)
        return
    }

    refVar.value = updaterOrValue
}

export interface HeavyEquipment {
    id: number
    name: string
    description: string | null
    daily_rate: number
    photo: string | null
    status: 'available' | 'rented' | 'maintenance' | 'offline'
}

const page = usePage<{ heavyEquipments: HeavyEquipment[] }>()
const data = computed(() => page.props.heavyEquipments ?? [])

const columns: ColumnDef<HeavyEquipment>[] = [
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
    {
        accessorKey: 'description',
        header: 'Description',
        cell: ({ row }: any) => h('div', { class: 'min-w-[200px]' }, row.getValue('description') ?? '-'),
    },
    {
        accessorKey: 'daily_rate',
        header: () => h('div', { class: 'text-left' }, 'Daily Rate'),
        cell: ({ row }: any) => {
            const amount = Number.parseInt(String(row.getValue('daily_rate')));

            const formatted = 'Rp' + amount.toLocaleString();

            return h('div', { class: 'text-left font-medium min-w-[200px]' }, formatted);
        },
    },
    {
        accessorKey: 'photo',
        header: 'Photo',
        cell: ({ row }: any) => {
            const url = row.getValue('photo')

            if (url) {
                return h('div', { class: 'min-w-[180px] flex items-center' }, [
                    h('img', { src: url, class: 'h-12 w-16 rounded object-cover', alt: 'photo' })
                ])
            }

            return h('div', { class: 'text-muted-foreground min-w-[180px]' }, 'No photo')
        },
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: ({ row }: any) => {
            const status = row.getValue('status')
            let label = status

            if (status === 'available') {
                label = 'Available'
            } else if (status === 'rented') {
                label = 'Rented'
            } else if (status === 'maintenance') {
                label = 'Maintenance'
            } else if (status === 'offline') {
                label = 'Offline'
            }

            return h('div', { class: 'text-sm' }, label)
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }: any) => {
            const equipment = row.original

            return h('div', { class: 'flex items-center gap-2 min-w-[100px]' }, [
                h(Link, { href: `/heavy-equipments/${equipment.id}/edit`, class: 'inline-flex items-center justify-center rounded p-1.5 hover:bg-muted' }, [
                    h(Pencil, { class: 'h-4 w-4 text-blue-600' }),
                ]),
                h('button', { type: 'button', class: 'inline-flex items-center justify-center rounded p-1.5 hover:bg-muted', onClick: () => handleDelete(equipment.id) }, [
                    h(Trash2, { class: 'h-4 w-4 text-red-600' }),
                ]),
            ])
        },
    },
]

const sorting = ref<SortingState>([])
const columnFilters = ref<ColumnFiltersState>([])
const columnVisibility = ref<VisibilityState>({})

const expanded = ref<ExpandedState>({})

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

const handleDelete = (id: number) => {
    if (!confirm('Are you sure you want to delete this equipment?')) return

    router.post(`/heavy-equipments/${id}`, { _method: 'DELETE' }, {
        onSuccess: () => {
            // refresh index data so table updates without full browser reload
            router.get('/heavy-equipments')
        },
    })
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Heavy equipments',
                href: '/heavy-equipments',
            },
        ],
    },
})
</script>

<template>
    <Head title="Heavy equipments" />

    <div class="flex flex-col gap-6 px-6 py-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <Heading
                variant="small"
                title="Heavy equipments"
                description="Manage and organize your rental equipment inventory."
            />

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

                <Link href="/heavy-equipments/create" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 whitespace-nowrap">
                    <Plus class="h-4 w-4" />
                    Add Equipment
                </Link>
            </div>
        </div>

        <div class="w-full space-y-6">
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
    </div>
</template>
