<script setup lang="ts">
import { computed, ref } from 'vue'
import { formatCurrency } from '@/lib/utils'

const props = defineProps<{
  open: boolean
  cart: any
  totals: any
  addresses: any[]
  isAuthenticated?: boolean
  whatsappCartUrl?: string | null
  themeColor?: string
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'update-item', payload: { itemId: number; quantity: number }): void
  (e: 'remove-item', itemId: number): void
  (e: 'clear'): void
  (e: 'checkout', payload: { addressId: number; paymentMethod: string; shippingMethod?: string; notes?: string }): void
  (e: 'open-customer-panel'): void
}>()

const addressId = ref<number | null>(null)
const paymentMethod = ref('manual')
const shippingMethod = ref('retirada')
const notes = ref('')

const items = computed(() => props.cart?.items ?? [])
const hasItems = computed(() => items.value.length > 0)

function submitCheckout() {
  if (!addressId.value) return

  emit('checkout', {
    addressId: addressId.value,
    paymentMethod: paymentMethod.value,
    shippingMethod: shippingMethod.value,
    notes: notes.value,
  })
}
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-[70]">
    <div class="absolute inset-0 bg-slate-900/50" @click="emit('close')"></div>

    <aside class="absolute right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl overflow-y-auto">
      <header class="sticky top-0 bg-white border-b px-4 py-3 flex items-center justify-between z-10">
        <h3 class="font-semibold text-slate-900">Carrinho</h3>
        <button class="text-sm text-slate-500" @click="emit('close')">Fechar</button>
      </header>

      <div class="p-4 space-y-4">
        <div v-if="!hasItems" class="text-sm text-slate-500">Seu carrinho esta vazio.</div>

        <article v-for="item in items" :key="item.id" class="border rounded-xl p-3">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="font-semibold text-sm">{{ item.product?.name ?? 'Produto' }}</p>
              <p class="text-xs text-slate-500">{{ formatCurrency(item.unit_price) }}</p>
            </div>
            <button class="text-xs text-rose-600" @click="emit('remove-item', item.id)">Remover</button>
          </div>

          <div class="mt-3 flex items-center gap-2">
            <button class="px-2 py-1 border rounded" @click="emit('update-item', { itemId: item.id, quantity: Math.max(0, (item.quantity ?? 1) - 1) })">-</button>
            <span class="text-sm font-semibold min-w-8 text-center">{{ item.quantity }}</span>
            <button class="px-2 py-1 border rounded" @click="emit('update-item', { itemId: item.id, quantity: (item.quantity ?? 1) + 1 })">+</button>
          </div>
        </article>

        <div v-if="hasItems" class="rounded-xl border p-3 bg-slate-50">
          <p class="text-sm">Subtotal: <strong>{{ formatCurrency(totals?.subtotal ?? 0) }}</strong></p>
          <p class="text-sm">Total: <strong>{{ formatCurrency(totals?.total ?? 0) }}</strong></p>
        </div>

        <div v-if="hasItems" class="space-y-2">
          <label class="text-sm text-slate-700">Endereço de entrega</label>
          <select v-model="addressId" class="w-full border rounded-lg px-3 py-2">
            <option :value="null">Selecione um endereço</option>
            <option v-for="address in addresses" :key="address.id" :value="address.id">
              {{ address.street }}, {{ address.number }} - {{ address.neighborhood }}
            </option>
          </select>

          <div v-if="!props.isAuthenticated" class="text-xs text-amber-700 bg-amber-50 border border-amber-200 p-2 rounded">
            Para finalizar pedido, entre ou crie uma conta.
            <button class="underline ml-1" @click="emit('open-customer-panel')">Entrar/Cadastrar</button>
          </div>

          <div v-else-if="!addresses?.length" class="text-xs text-amber-700 bg-amber-50 border border-amber-200 p-2 rounded">
            Você precisa cadastrar um endereço antes de finalizar.
            <button class="underline ml-1" @click="emit('open-customer-panel')">Abrir painel do cliente</button>
          </div>

          <label class="text-sm text-slate-700">Forma de pagamento</label>
          <select v-model="paymentMethod" class="w-full border rounded-lg px-3 py-2">
            <option value="manual">Manual</option>
            <option value="whatsapp">WhatsApp</option>
            <option value="pix">PIX (futuro)</option>
            <option value="card">Cartão (futuro)</option>
          </select>

          <label class="text-sm text-slate-700">Entrega</label>
          <select v-model="shippingMethod" class="w-full border rounded-lg px-3 py-2">
            <option value="retirada">Retirada</option>
            <option value="entrega">Entrega</option>
          </select>

          <textarea v-model="notes" rows="3" class="w-full border rounded-lg px-3 py-2" placeholder="Observações do pedido"></textarea>

          <div class="grid grid-cols-2 gap-2">
            <button class="rounded-lg border py-2" @click="emit('clear')">Limpar</button>
            <button class="rounded-lg text-white py-2" :style="{ backgroundColor: themeColor }" :disabled="!props.isAuthenticated || !addressId || !addresses?.length" @click="submitCheckout">
              Finalizar
            </button>
          </div>

          <a v-if="props.whatsappCartUrl" :href="props.whatsappCartUrl" target="_blank" class="block text-center rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 py-2 text-sm">
            Pedir tudo no WhatsApp
          </a>
        </div>
      </div>
    </aside>
  </div>
</template>
