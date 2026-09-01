<template>
  <div class="space-y-4 rounded-xl p-4 text-foreground md:border md:border-border md:bg-card md:shadow-sm">
    <!-- Header -->
    <h2
      class="flex items-center gap-2 rounded-xl border border-border bg-muted/50 p-3 text-2xl font-extrabold"
    >
      <component :is="getIcon('SquareCheckBig')" class="h-6 w-6 text-sky-400" />
      <span
        class="text-transparent bg-clip-text bg-gradient-to-r to-emerald-600 from-sky-400"
      >
        Conteúdo da página
      </span>
    </h2>

    <!-- Título, Ícone e Status -->
    <div>
      <label class="block text-sm font-medium mb-1">Título e ícone</label>

      <div class="flex items-center gap-3">
        <!-- Ícone atual -->
        <component
          v-if="page.icon"
          :is="getIcon(page.icon)"
          class="h-6 w-6 text-sky-500 cursor-pointer hover:scale-110"
          @click="toggleIconPicker"
        />

        <!-- Título -->
        <input
          v-model="page.title"
          type="text"
          placeholder="Título da página"
          class="flex-1 rounded-xl border border-input bg-background px-3 py-2 text-foreground"
        />

        <!-- Toggle de status -->
        <PageStatusToggle v-model="page.is_active" />
      </div>

      <!-- Icon Picker -->
      <div
        v-if="showIconPicker"
        class="mt-2 grid grid-cols-3 gap-2 rounded-xl border border-border bg-popover p-3 text-popover-foreground shadow-lg md:grid-cols-6"
      >
        <button
          v-for="opt in iconOptions"
          :key="opt.name"
          type="button"
          @click="selectIcon(opt.name)"
          class="rounded-xl p-2 text-center hover:bg-accent hover:text-accent-foreground"
        >
          <component :is="getIcon(opt.name)" class="h-6 w-6 mx-auto" />
          <div class="mt-1 text-center text-xs text-muted-foreground">
            {{ opt.label }}
          </div>
        </button>
      </div>
    </div>

    <!-- Conteúdos dinâmicos -->
    <slot />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import PageStatusToggle from '@/components/dashboard/pages/PageStatusToggle.vue'
import { getIcon } from '@/lib/iconMap'

const props = defineProps<{
  page: any
  iconOptions: Array<{ name: string; label: string }>
}>()

const emit = defineEmits(['update:page'])

const page = props.page
const iconOptions = props.iconOptions

const showIconPicker = ref(false)

function toggleIconPicker() {
  showIconPicker.value = !showIconPicker.value
}

function selectIcon(icon: string) {
  page.icon = icon
  showIconPicker.value = false
}
</script>
