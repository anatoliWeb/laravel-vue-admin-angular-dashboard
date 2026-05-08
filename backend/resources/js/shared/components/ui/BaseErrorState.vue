<template>
  <div class="rounded-lg border border-red-200 bg-red-50 p-6">
    <h3 class="text-base font-semibold text-red-800">{{ title }}</h3>
    <p class="mt-1 text-sm text-red-700">{{ description }}</p>
    <div v-if="$slots.default" class="mt-4">
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  title?: string;
  description?: string;
}

withDefaults(defineProps<Props>(), {
  title: 'Something went wrong',
  description: 'Please try again in a moment.',
});
</script>

