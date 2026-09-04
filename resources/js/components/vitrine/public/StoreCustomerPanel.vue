<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import { formatCurrency, formatDate } from '@/lib/utils'

const props = defineProps<{
  open: boolean
  loading?: boolean
  customer: any
  orders: any[]
  favorites: any[]
  addresses: any[]
  storeThemeColor?: string
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'login', payload: { email: string; password: string }): void
  (e: 'register', payload: Record<string, any>): void
  (e: 'logout'): void
  (e: 'save-address', payload: { address: Record<string, any>; id?: number }): void
  (e: 'delete-address', id: number): void
  (e: 'repeat-order', orderId: number): void
  (e: 'open-whatsapp-order', orderId: number): void
}>()

const activeTab = ref<'login' | 'register' | 'orders' | 'favorites' | 'addresses'>('login')

const loginForm = reactive({
  email: '',
  password: '',
})

const registerForm = reactive({
  name: '',
  email: '',
  whatsapp: '',
  password: '',
  address_mode: 'manual',
  zip: '',
  street: '',
  number: '',
  complement: '',
  neighborhood: '',
  city: '',
  state: '',
  reference: '',
  notes: '',
})

const addressForm = reactive({
  id: undefined as number | undefined,
  label: 'Principal',
  is_default: false,
  zip: '',
  street: '',
  number: '',
  complement: '',
  neighborhood: '',
  city: '',
  state: '',
  reference: '',
  notes: '',
})

const isAuthenticated = computed(() => !!props.customer)

watch(
  () => props.open,
  (value) => {
    if (!value) return
    activeTab.value = isAuthenticated.value ? 'orders' : 'login'
  },
)

function submitLogin() {
  emit('login', { ...loginForm })
}

function submitRegister() {
  emit('register', { ...registerForm })
}

function submitAddress() {
  emit('save-address', {
    id: addressForm.id,
    address: { ...addressForm },
  })

  Object.assign(addressForm, {
    id: undefined,
    label: 'Principal',
    is_default: false,
    zip: '',
    street: '',
    number: '',
    complement: '',
    neighborhood: '',
    city: '',
    state: '',
    reference: '',
    notes: '',
  })
}

function editAddress(address: any) {
  Object.assign(addressForm, {
    id: address.id,
    label: address.label ?? 'Principal',
    is_default: !!address.is_default,
    zip: address.zip ?? '',
    street: address.street ?? '',
    number: address.number ?? '',
    complement: address.complement ?? '',
    neighborhood: address.neighborhood ?? '',
    city: address.city ?? '',
    state: address.state ?? '',
    reference: address.reference ?? '',
    notes: address.notes ?? '',
  })

  activeTab.value = 'addresses'
}
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-[80]">
    <div class="absolute inset-0 bg-slate-900/50" @click="emit('close')"></div>

    <aside class="absolute right-0 top-0 h-full w-full max-w-xl bg-white shadow-2xl overflow-y-auto">
      <header class="sticky top-0 bg-white border-b px-4 py-3 flex items-center justify-between z-10">
        <div>
          <p class="text-xs uppercase tracking-wide text-slate-500">Área do cliente</p>
          <h3 class="font-semibold text-slate-900">Minha conta</h3>
        </div>
        <button class="text-sm text-slate-500" @click="emit('close')">Fechar</button>
      </header>

      <div class="p-4 space-y-4">
        <div class="flex flex-wrap gap-2">
          <button v-if="!isAuthenticated" class="px-3 py-1.5 rounded-full text-sm border" :style="activeTab === 'login' ? { backgroundColor: storeThemeColor, color: '#fff' } : {}" @click="activeTab = 'login'">Entrar</button>
          <button v-if="!isAuthenticated" class="px-3 py-1.5 rounded-full text-sm border" :style="activeTab === 'register' ? { backgroundColor: storeThemeColor, color: '#fff' } : {}" @click="activeTab = 'register'">Cadastro</button>

          <button v-if="isAuthenticated" class="px-3 py-1.5 rounded-full text-sm border" :style="activeTab === 'orders' ? { backgroundColor: storeThemeColor, color: '#fff' } : {}" @click="activeTab = 'orders'">Pedidos</button>
          <button v-if="isAuthenticated" class="px-3 py-1.5 rounded-full text-sm border" :style="activeTab === 'favorites' ? { backgroundColor: storeThemeColor, color: '#fff' } : {}" @click="activeTab = 'favorites'">Favoritos</button>
          <button v-if="isAuthenticated" class="px-3 py-1.5 rounded-full text-sm border" :style="activeTab === 'addresses' ? { backgroundColor: storeThemeColor, color: '#fff' } : {}" @click="activeTab = 'addresses'">Endereços</button>
        </div>

        <div v-if="!isAuthenticated && activeTab === 'login'" class="space-y-3">
          <input v-model="loginForm.email" type="email" class="w-full border rounded-lg px-3 py-2" placeholder="E-mail" />
          <input v-model="loginForm.password" type="password" class="w-full border rounded-lg px-3 py-2" placeholder="Senha" />
          <button class="w-full rounded-lg text-white py-2" :style="{ backgroundColor: storeThemeColor }" @click="submitLogin">Entrar</button>
        </div>

        <div v-if="!isAuthenticated && activeTab === 'register'" class="space-y-2">
          <input v-model="registerForm.name" class="w-full border rounded-lg px-3 py-2" placeholder="Nome" />
          <input v-model="registerForm.email" type="email" class="w-full border rounded-lg px-3 py-2" placeholder="E-mail" />
          <input v-model="registerForm.whatsapp" class="w-full border rounded-lg px-3 py-2" placeholder="WhatsApp" />
          <input v-model="registerForm.password" type="password" class="w-full border rounded-lg px-3 py-2" placeholder="Senha" />
          <div class="grid grid-cols-2 gap-2">
            <input v-model="registerForm.zip" class="border rounded-lg px-3 py-2" placeholder="CEP" />
            <input v-model="registerForm.state" class="border rounded-lg px-3 py-2" placeholder="UF" />
          </div>
          <input v-model="registerForm.street" class="w-full border rounded-lg px-3 py-2" placeholder="Rua" />
          <div class="grid grid-cols-2 gap-2">
            <input v-model="registerForm.number" class="border rounded-lg px-3 py-2" placeholder="Número" />
            <input v-model="registerForm.complement" class="border rounded-lg px-3 py-2" placeholder="Complemento" />
          </div>
          <input v-model="registerForm.neighborhood" class="w-full border rounded-lg px-3 py-2" placeholder="Bairro" />
          <input v-model="registerForm.city" class="w-full border rounded-lg px-3 py-2" placeholder="Cidade" />
          <input v-model="registerForm.reference" class="w-full border rounded-lg px-3 py-2" placeholder="Referencia" />
          <textarea v-model="registerForm.notes" class="w-full border rounded-lg px-3 py-2" rows="3" placeholder="Observações"></textarea>
          <button class="w-full rounded-lg text-white py-2" :style="{ backgroundColor: storeThemeColor }" @click="submitRegister">Criar conta</button>
        </div>

        <div v-if="isAuthenticated && activeTab === 'orders'" class="space-y-3">
          <div class="p-3 rounded-lg bg-slate-50 border text-sm text-slate-700">
            Logado como <strong>{{ customer?.name }}</strong>
            <button class="ml-2 text-rose-600" @click="emit('logout')">Sair</button>
          </div>

          <div v-if="!orders?.length" class="text-sm text-slate-500">Nenhum pedido encontrado.</div>

          <article v-for="order in orders" :key="order.id" class="border rounded-xl p-3 space-y-2">
            <div class="flex items-center justify-between">
              <p class="font-semibold text-sm">{{ order.order_number }}</p>
              <span class="text-xs px-2 py-1 rounded-full bg-slate-100">{{ order.status }}</span>
            </div>
            <p class="text-sm text-slate-600">{{ formatCurrency(order.total) }} • {{ formatDate(order.created_at, { hour: '2-digit', minute: '2-digit' }) }}</p>
            <div class="flex gap-2">
              <button class="px-3 py-1.5 text-xs rounded-lg border" @click="emit('repeat-order', order.id)">Repetir pedido</button>
              <button class="px-3 py-1.5 text-xs rounded-lg border" @click="emit('open-whatsapp-order', order.id)">Enviar no WhatsApp</button>
            </div>
          </article>
        </div>

        <div v-if="isAuthenticated && activeTab === 'favorites'" class="space-y-2">
          <div v-if="!favorites?.length" class="text-sm text-slate-500">Sem favoritos.</div>
          <article v-for="fav in favorites" :key="fav.id" class="border rounded-xl p-3">
            <p class="text-sm font-semibold">{{ fav.product?.name ?? 'Produto' }}</p>
            <p class="text-xs text-slate-500">{{ formatCurrency(fav.product?.discount_price > 0 ? fav.product?.discount_price : fav.product?.price ?? 0) }}</p>
          </article>
        </div>

        <div v-if="isAuthenticated && activeTab === 'addresses'" class="space-y-3">
          <form class="space-y-2" @submit.prevent="submitAddress">
            <input v-model="addressForm.label" class="w-full border rounded-lg px-3 py-2" placeholder="Rotulo" />
            <div class="grid grid-cols-2 gap-2">
              <input v-model="addressForm.zip" class="border rounded-lg px-3 py-2" placeholder="CEP" />
              <input v-model="addressForm.state" class="border rounded-lg px-3 py-2" placeholder="UF" />
            </div>
            <input v-model="addressForm.street" class="w-full border rounded-lg px-3 py-2" placeholder="Rua" />
            <div class="grid grid-cols-2 gap-2">
              <input v-model="addressForm.number" class="border rounded-lg px-3 py-2" placeholder="Número" />
              <input v-model="addressForm.complement" class="border rounded-lg px-3 py-2" placeholder="Complemento" />
            </div>
            <input v-model="addressForm.neighborhood" class="w-full border rounded-lg px-3 py-2" placeholder="Bairro" />
            <input v-model="addressForm.city" class="w-full border rounded-lg px-3 py-2" placeholder="Cidade" />
            <input v-model="addressForm.reference" class="w-full border rounded-lg px-3 py-2" placeholder="Referencia" />
            <textarea v-model="addressForm.notes" class="w-full border rounded-lg px-3 py-2" rows="2" placeholder="Observações"></textarea>
            <label class="flex items-center gap-2 text-sm">
              <input v-model="addressForm.is_default" type="checkbox" /> Endereço padrão
            </label>
            <button class="w-full rounded-lg text-white py-2" :style="{ backgroundColor: storeThemeColor }">Salvar endereço</button>
          </form>

          <div class="space-y-2">
            <article v-for="address in addresses" :key="address.id" class="border rounded-xl p-3 text-sm">
              <p class="font-semibold">{{ address.label || 'Endereço' }} <span v-if="address.is_default" class="text-xs text-emerald-700">(Padrão)</span></p>
              <p class="text-slate-600">{{ address.street }}, {{ address.number }} - {{ address.neighborhood }}</p>
              <p class="text-slate-500">{{ address.city }}/{{ address.state }} - {{ address.zip }}</p>
              <div class="mt-2 flex gap-2">
                <button class="px-3 py-1 text-xs border rounded" @click="editAddress(address)">Editar</button>
                <button class="px-3 py-1 text-xs border rounded text-rose-600" @click="emit('delete-address', address.id)">Excluir</button>
              </div>
            </article>
          </div>
        </div>
      </div>
    </aside>
  </div>
</template>
