<!-- resources/js/components/dashboard/pages/PageFormActions.vue -->
<template>
  <div class="flex items-center gap-3">
    <BaseButton :disabled="sending" variant="secondary" size="sm" @click="$emit('cancel')">
      Cancelar
    </BaseButton>

    <BaseButton :loading="sending" type="submit" variant="primary" size="md">
      {{ sending ? 'Salvando...' : 'Salvar Alterações' }}
    </BaseButton>
  </div>
</template>

<script setup lang="ts">
import BaseButton from '@/components/ui/button/BaseButton.vue'

const props = defineProps<{
  sending?: boolean
}>()

const emit = defineEmits<{
  (e: 'cancel'): void
}>()
</script>
