<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import Heading from '@/components/Heading.vue'

type RentalItem = {
  id: number
  heavy_equipment: {
    id: number
    name: string
    photo?: string | null
  }
}

type Rental = {
  id: number
  user?: {
    name: string
    email: string
  }
  start_date: string
  end_date: string
  total_price: number
  payment_proof?: string | null
  status: string
  rental_details: RentalItem[]
}

const props = defineProps<{ rental: Rental; isAdmin: boolean }>()

const status = ref(props.rental.status)
const isSubmitting = ref(false)

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

const handleUpdate = async () => {
  if (!props.isAdmin) {
    return
  }

  isSubmitting.value = true

  try {
    await router.put(`/rentals/${props.rental.id}`, { status: status.value })
  } finally {
    isSubmitting.value = false
  }
}

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'History', href: '/history' },
      { title: 'Edit Rental' },
    ],
  },
})
</script>

<template>
  <Head :title="`Edit Rental #${props.rental.id}`" />

  <div class="flex flex-col gap-6 px-6 py-6">
    <Heading :title="`Edit Rental #${props.rental.id}`" description="Manage rental status and payment proof." />

    <div class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
      <div class="rounded-xl border border-sidebar-border/70 bg-background p-6 shadow-sm">
        <div class="space-y-4">
          <div class="grid gap-2 sm:grid-cols-2">
            <div>
              <p class="text-sm text-muted-foreground">Start Date</p>
              <p>{{ formatDate(props.rental.start_date) }}</p>
            </div>
            <div>
              <p class="text-sm text-muted-foreground">End Date</p>
              <p>{{ formatDate(props.rental.end_date) }}</p>
            </div>
          </div>

          <div class="grid gap-2 sm:grid-cols-2">
            <div>
              <p class="text-sm text-muted-foreground">Total Price</p>
              <p>Rp{{ props.rental.total_price.toLocaleString() }}</p>
            </div>
            <div>
              <p class="text-sm text-muted-foreground">Status</p>
              <p>{{ props.rental.status }}</p>
            </div>
          </div>

          <div>
            <p class="text-sm text-muted-foreground">Customer</p>
            <p>{{ props.rental.user?.name ?? '-' }}</p>
            <p v-if="props.rental.user?.email" class="text-sm text-muted-foreground">{{ props.rental.user.email }}</p>
          </div>

          <div>
            <p class="text-sm text-muted-foreground">Items</p>
            <ul class="mt-2 space-y-2">
              <li v-for="item in props.rental.rental_details" :key="item.id" class="rounded-lg border border-input p-3">
                <div class="flex items-center gap-3">
                  <img
                    v-if="item.heavy_equipment.photo"
                    :src="item.heavy_equipment.photo"
                    alt="Equipment photo"
                    class="h-16 w-24 rounded object-cover"
                  />
                  <div>
                    <div class="font-medium">{{ item.heavy_equipment.name }}</div>
                  </div>
                </div>
              </li>
            </ul>
          </div>

          <div>
            <p class="text-sm text-muted-foreground">Payment Proof</p>
            <p v-if="props.rental.payment_proof">
              <a :href="`/storage/${props.rental.payment_proof}`" target="_blank" class="text-primary hover:underline">View proof</a>
            </p>
            <p v-else class="text-muted-foreground">No payment proof uploaded.</p>
          </div>
        </div>
      </div>

      <div class="rounded-xl border border-sidebar-border/70 bg-background p-6 shadow-sm">
        <div class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-foreground">Rental Status</label>
            <select v-model="status" class="mt-2 block w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring" :disabled="!props.isAdmin">
              <option value="Menunggu">Menunggu</option>
              <option value="Disetujui">Disetujui</option>
              <option value="Sedang Disewa">Sedang Disewa</option>
              <option value="Selesai">Selesai</option>
              <option value="Ditolak">Ditolak</option>
            </select>
          </div>

          <button v-if="props.isAdmin" @click.prevent="handleUpdate" class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50" :disabled="isSubmitting">
            Save Status
          </button>
          <p v-else class="text-sm text-muted-foreground">Only administrators can update status.</p>

        </div>
      </div>
    </div>
  </div>
</template>
