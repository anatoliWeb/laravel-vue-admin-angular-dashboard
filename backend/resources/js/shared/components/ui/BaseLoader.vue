<template>
  <div class="inline-flex items-center gap-2 text-slate-600">
    <span
      :class="spinnerClass"
      class="inline-block animate-spin rounded-full border-2 border-current border-r-transparent"
      aria-hidden="true"
    />
    <span class="text-sm">{{ label }}</span>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  label?: string;
  size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
  label: 'Loading...',
  size: 'md',
});

const spinnerClass = computed(() => {
  if (props.size === 'sm') return 'h-3 w-3';
  if (props.size === 'lg') return 'h-6 w-6';
  return 'h-4 w-4';
});
</script>

