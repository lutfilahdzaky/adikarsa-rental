<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { getLocalTimeZone, today } from '@internationalized/date'
import type { DateValue } from '@internationalized/date'
import type { DateRange } from 'reka-ui'
import { computed, reactive, ref } from 'vue'
import type { Ref } from 'vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { RangeCalendar } from '@/components/ui/range-calendar'
import {
  Stepper,
  StepperDescription,
  StepperIndicator,
  StepperItem,
  StepperSeparator,
  StepperTitle,
  StepperTrigger,
} from '@/components/ui/stepper'
import { CalendarDays, CreditCard, Truck } from '@lucide/vue'

interface Equipment {
  id: number
  name: string
  daily_rate: number
  status: string
  photo?: string | null
}

interface Customer {
  id: number
  name: string
  email: string
}

interface Props {
  heavyEquipments: Equipment[]
  isAdmin: boolean
  customers: Customer[]
}

const props = defineProps<Props>()

const todayDate = today(getLocalTimeZone())
const dateRange = ref<DateRange>({
  start: todayDate,
  end: todayDate.add({ days: 7 }),
})
const searchQuery = ref('')
const activeStep = ref(1)

const form = reactive({
  heavy_equipment_ids: [] as number[],
  customer_id: null as number | null,
})

const paymentProof = ref<File | null>(null)
const errors = ref<Record<string, string>>({})
const isSubmitting = ref(false)

const steps = [
  {
    step: 1,
    title: 'Equipment',
    description: 'Select equipment and add to cart',
    icon: Truck,
  },
  {
    step: 2,
    title: 'Rental',
    description: 'Choose dates and customer',
    icon: CalendarDays,
  },
  {
    step: 3,
    title: 'Payment',
    description: 'Upload payment proof',
    icon: CreditCard,
  },
]

const filteredEquipments = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()

  if (!query) {
    return props.heavyEquipments
  }

  return props.heavyEquipments.filter((equipment) =>
    equipment.name.toLowerCase().includes(query) ||
    equipment.status.toLowerCase().includes(query),
  )
})

const selectedEquipments = computed(() =>
  props.heavyEquipments.filter((equipment) => form.heavy_equipment_ids.includes(equipment.id)),
)

const rentalDays = computed(() => {
  if (!dateRange.value?.start || !dateRange.value?.end) {
    return 0
  }

  const start = new Date(dateRange.value.start.toString())
  const end = new Date(dateRange.value.end.toString())
  const diff = Math.round((end.getTime() - start.getTime()) / 86400000)

  return Math.max(1, diff + 1)
})

const totalPrice = computed(() =>
  selectedEquipments.value.reduce((sum, equipment) => sum + equipment.daily_rate * rentalDays.value, 0),
)

const canProceedToRental = computed(() => selectedEquipments.value.length > 0)
const canProceedToPayment = computed(() => {
  if (!canProceedToRental.value) {
    return false
  }

  if (!dateRange.value?.start || !dateRange.value?.end) {
    return false
  }

  if (props.isAdmin && !form.customer_id) {
    return false
  }

  return true
})

const canSubmit = computed(() =>
  canProceedToPayment.value && paymentProof.value !== null && selectedEquipments.value.length > 0,
)

const toggleEquipment = (equipmentId: number) => {
  const index = form.heavy_equipment_ids.indexOf(equipmentId)

  if (index === -1) {
    form.heavy_equipment_ids.push(equipmentId)
  } else {
    form.heavy_equipment_ids.splice(index, 1)
  }
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)

  if (Number.isNaN(date.getTime())) {
    return dateString
  }

  const day = String(date.getDate()).padStart(2, '0')
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const year = String(date.getFullYear())

  return `${day}-${month}-${year}`
}

const isDateUnavailable = (date: DateValue) => {
  const dateToCompare = new Date(date.toString())
  const todayToCompare = new Date(todayDate.toString())

  return dateToCompare < todayToCompare
}

const handleProofFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement | null
  paymentProof.value = target?.files?.[0] ?? null
}

const goNext = () => {
  if (activeStep.value === 1 && !canProceedToRental.value) {
    return
  }

  if (activeStep.value === 2 && !canProceedToPayment.value) {
    return
  }

  if (activeStep.value < steps.length) {
    activeStep.value += 1
  }
}

const goPrevious = () => {
  if (activeStep.value > 1) {
    activeStep.value -= 1
  }
}

const handleSubmit = async () => {
  isSubmitting.value = true
  errors.value = {}

  try {
    const formData = new FormData()
    formData.append('start_date', dateRange.value?.start?.toString() ?? '')
    formData.append('end_date', dateRange.value?.end?.toString() ?? '')

    form.heavy_equipment_ids.forEach((equipmentId) => {
      formData.append('heavy_equipment_ids[]', String(equipmentId))
    })

    if (props.isAdmin && form.customer_id) {
      formData.append('customer_id', String(form.customer_id))
    }

    if (paymentProof.value) {
      formData.append('payment_proof', paymentProof.value)
    }

    await router.post('/rentals', formData, {
      onError: (pageErrors) => {
        errors.value = pageErrors
      },
    })
  } finally {
    isSubmitting.value = false
  }
}

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'New Rental' },
    ],
  },
})
</script>

<template>
  <Head title="New Rental" />

  <div class="flex flex-col gap-4 px-4 py-4">
    <Heading variant="small" title="New Rental" description="Create a new rental request in a few easy steps." />

    <div class="w-full max-w-6xl mx-auto overflow-hidden">
      <div class="space-y-4">
        <Stepper
          class="flex w-full items-start gap-2"
          v-model:modelValue="activeStep"
        >
        <StepperItem
          v-for="item in steps"
          :key="item.step"
          :step="item.step"
          class="relative flex w-full flex-col items-center justify-center"
        >
          <StepperTrigger>
            <StepperIndicator v-slot="{ step }">
              <template v-if="item.icon">
                <component :is="item.icon" class="w-4 h-4" />
              </template>
              <span v-else>{{ step }}</span>
            </StepperIndicator>
          </StepperTrigger>

          <StepperSeparator
            v-if="item.step !== steps[steps.length - 1]?.step"
            class="absolute left-[calc(50%+20px)] right-[calc(-50%+10px)] top-5 block h-0.5 shrink-0 rounded-full bg-muted group-data-[state=completed]:bg-primary"
          />

          <div class="flex flex-col items-center">
            <StepperTitle>{{ item.title }}</StepperTitle>
            <StepperDescription>{{ item.description }}</StepperDescription>
          </div>
        </StepperItem>
      </Stepper>
        </div>

      <div class="mt-6 space-y-6">
        <section v-if="activeStep === 1" class="space-y-6">
          <div class="grid gap-4 lg:grid-cols-[1fr_340px] lg:items-start">
            <div class="space-y-4">
              <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm font-medium text-foreground">Available Equipment</p>
                  <p class="text-sm text-muted-foreground">Choose available equipment to book.</p>
                </div>
                <Input
                  v-model="searchQuery"
                  placeholder="Cari peralatan..."
                  class="max-w-sm"
                />
              </div>

              <div class="overflow-hidden rounded-xl border border-input bg-background shadow-sm">
                <div class="max-h-[62vh] overflow-y-auto p-4">
                  <div class="grid gap-3 sm:grid-cols-2">
                    <template v-if="filteredEquipments.length">
                      <div
                        v-for="equipment in filteredEquipments"
                        :key="equipment.id"
                        :class="[
                          'flex flex-col justify-between gap-4 rounded-lg border p-4 transition',
                          form.heavy_equipment_ids.includes(equipment.id)
                            ? 'border-primary bg-primary/10'
                            : 'border-input hover:border-primary',
                        ]"
                      >
                        <div>
                          <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                              <img
                                v-if="equipment.photo"
                                :src="equipment.photo"
                                alt="Equipment photo"
                                class="h-16 w-24 rounded object-cover"
                              />
                              <div>
                                <span class="font-medium">{{ equipment.name }}</span>
                                <p class="text-sm text-muted-foreground">Rp{{ equipment.daily_rate.toLocaleString() }} / day</p>
                              </div>
                            </div>
                            <span class="rounded-full bg-muted px-2 py-1 text-xs text-muted-foreground">{{ equipment.status }}</span>
                          </div>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                          <span class="text-sm text-muted-foreground">{{ form.heavy_equipment_ids.includes(equipment.id) ? 'Added to cart' : 'Ready to add' }}</span>
                          <Button
                            type="button"
                            size="sm"
                            variant="outline"
                            :class="form.heavy_equipment_ids.includes(equipment.id) ? 'border-primary text-primary hover:bg-primary/10' : ''"
                            @click="toggleEquipment(equipment.id)"
                          >
                            {{ form.heavy_equipment_ids.includes(equipment.id) ? 'Remove' : 'Add' }}
                          </Button>
                        </div>
                      </div>
                    </template>
                    <div v-else class="sm:col-span-2 rounded-lg border border-input p-6 text-center text-sm text-muted-foreground">
                      No equipment matches your search.
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="space-y-4 rounded-xl border border-input bg-background p-4 shadow-sm lg:sticky lg:top-4 lg:self-start flex h-full flex-col">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium">Cart</p>
                  <p class="text-sm text-muted-foreground">{{ selectedEquipments.length }} items selected</p>
                </div>
                <span class="rounded-full bg-primary px-3 py-1 text-xs font-semibold text-primary-foreground">{{ selectedEquipments.length }}</span>
              </div>

              <div class="space-y-3 max-h-[44vh] overflow-y-auto">
                <div
                  v-for="equipment in selectedEquipments"
                  :key="equipment.id"
                  class="rounded-lg border border-input p-3"
                >
                  <div class="flex items-center gap-3">
                    <img
                      v-if="equipment.photo"
                      :src="equipment.photo"
                      alt="Equipment photo"
                      class="h-14 w-20 rounded object-cover"
                    />
                    <div>
                      <div class="font-medium">{{ equipment.name }}</div>
                      <div class="text-sm text-muted-foreground">Rp{{ equipment.daily_rate.toLocaleString() }}/day</div>
                    </div>
                  </div>
                </div>
                <div v-if="selectedEquipments.length === 0" class="rounded-lg border border-dashed border-input p-4 text-sm text-muted-foreground">
                  Your cart is empty. Add equipment to continue.
                </div>
              </div>

              <div class="flex justify-end border-t border-input pt-3">
                <Button type="button" class="min-w-[180px]" :disabled="!canProceedToRental" @click="goNext">
                  Continue to Rental
                </Button>
              </div>
            </div>
          </div>
        </section>

        <section v-if="activeStep === 2" class="space-y-6">
          <div class="grid gap-4 lg:grid-cols-[1fr_340px]">
            <div class="space-y-6 rounded-xl border border-input bg-background p-4 shadow-sm">
              <div>
                <p class="text-sm font-medium">Rental Dates</p>
                <p class="text-sm text-muted-foreground">Choose a start date and end date.</p>
              </div>
              <div class="rounded-md border border-input bg-background p-3">
                <RangeCalendar
                  v-model:modelValue="dateRange"
                  class="rounded-md border shadow-sm"
                  :number-of-months="2"
                  disable-days-outside-current-view
                  :min-value="todayDate"
                  :is-date-unavailable="isDateUnavailable"
                />
              </div>

              <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-lg border border-input p-4">
                  <p class="text-sm text-muted-foreground">Start</p>
                  <p class="mt-1 font-medium">{{ formatDate(dateRange?.start?.toString() ?? '') }}</p>
                </div>
                <div class="rounded-lg border border-input p-4">
                  <p class="text-sm text-muted-foreground">End</p>
                  <p class="mt-1 font-medium">{{ formatDate(dateRange?.end?.toString() ?? '') }}</p>
                </div>
              </div>
            </div>

            <div class="space-y-4 rounded-xl border border-input bg-background p-4 shadow-sm">
              <p class="text-sm font-medium">Selection Summary</p>
              <div class="space-y-3">
                <div v-for="equipment in selectedEquipments" :key="equipment.id" class="rounded-lg border border-input p-3">
                  <div class="flex items-center justify-between gap-3 text-sm">
                    <span>{{ equipment.name }}</span>
                    <span class="text-muted-foreground">Rp{{ equipment.daily_rate.toLocaleString() }}/day</span>
                  </div>
                </div>
              </div>

              <div class="rounded-lg border border-input p-3 text-sm text-muted-foreground">
                <div class="flex items-center justify-between">
                  <span>Duration</span>
                  <span>{{ rentalDays }} days</span>
                </div>
                <div class="flex items-center justify-between">
                  <span>Total Price</span>
                  <span>Rp{{ totalPrice.toLocaleString() }}</span>
                </div>
              </div>

              <div v-if="props.isAdmin" class="rounded-lg border border-input p-4">
                <Label for="customer">Customer *</Label>
                <select
                  id="customer"
                  v-model.number="form.customer_id"
                  class="mt-2 block w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring"
                >
                  <option value="" disabled>Select customer</option>
                  <option v-for="customer in props.customers" :key="customer.id" :value="customer.id">
                    {{ customer.name }} - {{ customer.email }}
                  </option>
                </select>
                <p v-if="errors.customer_id" class="mt-1 text-sm text-destructive">{{ errors.customer_id }}</p>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-between gap-3">
            <Button type="button" variant="outline" class="min-w-[140px]" @click="goPrevious">
              Back
            </Button>
            <Button type="button" class="min-w-[180px]" :disabled="!canProceedToPayment" @click="goNext">
              Continue to Payment
            </Button>
          </div>
        </section>

        <section v-if="activeStep === 3" class="space-y-6">
          <div class="grid gap-4 lg:grid-cols-[1fr_340px]">
            <div class="space-y-6 rounded-xl border border-input bg-background p-4 shadow-sm">
              <div>
                <p class="text-sm font-medium">Payment</p>
                <p class="text-sm text-muted-foreground">Upload payment proof to complete the booking.</p>
              </div>

              <div class="grid w-full max-w-sm items-center gap-1.5">
                <Label for="paymentProof">Payment Proof *</Label>
                <Input id="paymentProof" type="file" @change="handleProofFileChange" class="payment-file-input" />
                <p class="text-sm text-muted-foreground">JPG, PNG, or PDF up to 10MB.</p>
                <p v-if="errors.payment_proof" class="mt-1 text-sm text-destructive">{{ errors.payment_proof }}</p>
              </div>
            </div>

            <div class="space-y-4 rounded-xl border border-input bg-background p-4 shadow-sm">
              <p class="text-sm font-medium">Order Summary</p>
              <div class="space-y-3">
                <div v-for="equipment in selectedEquipments" :key="equipment.id" class="flex items-center justify-between rounded-lg border border-input p-3 text-sm">
                  <span>{{ equipment.name }}</span>
                  <span class="text-muted-foreground">Rp{{ equipment.daily_rate.toLocaleString() }}/day</span>
                </div>
              </div>

              <div class="rounded-lg border border-input p-3 text-sm text-muted-foreground">
                <div class="flex items-center justify-between">
                  <span>Date</span>
                    <span>{{ formatDate(dateRange?.start?.toString() ?? '') }} - {{ formatDate(dateRange?.end?.toString() ?? '') }}</span>
                  <span>{{ rentalDays }} days</span>
                </div>
                <div class="flex items-center justify-between font-semibold text-foreground mt-2">
                  <span>Total</span>
                  <span>Rp{{ totalPrice.toLocaleString() }}</span>
                </div>
              </div>

              <div v-if="props.isAdmin" class="rounded-lg border border-input p-3 text-sm text-muted-foreground">
                <div class="font-medium">Customer</div>
                <div class="mt-2">
                  {{ props.customers.find((customer) => customer.id === form.customer_id)?.name ?? 'Not selected' }}
                </div>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-between gap-3">
            <Button type="button" variant="outline" class="min-w-[140px]" @click="goPrevious">
              Back
            </Button>
            <Button type="button" class="min-w-[180px]" :disabled="!canSubmit || isSubmitting" @click="handleSubmit">
              {{ isSubmitting ? 'Submitting...' : 'Submit Request' }}
            </Button>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Improve spacing between file selector button and filename */
.payment-file-input::file-selector-button {
  margin-right: 0.6rem;
  padding: 0.3rem 0.6rem;
  border-radius: 0.375rem;
}

/* WebKit fallback */
.payment-file-input::-webkit-file-upload-button {
  margin-right: 0.6rem;
  padding: 0.3rem 0.6rem;
  border-radius: 0.375rem;
}
</style>
