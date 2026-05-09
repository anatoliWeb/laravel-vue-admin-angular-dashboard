<template>
  <div ref="root" class="base-dropdown" :class="{ 'is-open': isOpen }">
    <div class="base-dropdown__trigger" @click="toggle">
      <slot name="trigger" :is-open="isOpen" />
    </div>

    <transition
      enter-active-class="base-dropdown-enter-active"
      enter-from-class="base-dropdown-enter-from"
      enter-to-class="base-dropdown-enter-to"
      leave-active-class="base-dropdown-leave-active"
      leave-from-class="base-dropdown-leave-from"
      leave-to-class="base-dropdown-leave-to"
    >
      <div v-if="isOpen" class="base-dropdown__menu" role="menu">
        <slot :close="close" />
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Centralized dropdown primitive for admin shell controls.
 *
 * WHY CENTRALIZE:
 * User menu, locale switcher, and future notification/message panels should
 * share identical behavior and visual rhythm. Keeping interaction logic in one
 * component prevents inconsistent close behavior and styling divergence.
 */
const isOpen = ref(false);
const root = ref<HTMLElement | null>(null);

const close = (): void => {
  isOpen.value = false;
};

const toggle = (): void => {
  isOpen.value = !isOpen.value;
};

const onDocumentClick = (event: MouseEvent): void => {
  const target = event.target as Node;
  if (!root.value || root.value.contains(target)) {
    return;
  }
  close();
};

const onEscape = (event: KeyboardEvent): void => {
  if (event.key === 'Escape') {
    close();
  }
};

onMounted(() => {
  document.addEventListener('click', onDocumentClick);
  document.addEventListener('keydown', onEscape);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', onDocumentClick);
  document.removeEventListener('keydown', onEscape);
});
</script>

<style scoped>
.base-dropdown {
  position: relative;
  display: inline-flex;
}

.base-dropdown__trigger {
  display: inline-flex;
}

.base-dropdown__menu {
  position: absolute;
  right: 0;
  top: calc(100% + 8px);
  min-width: 180px;
  overflow: hidden;
  border-radius: 10px;
  border: 1px solid rgba(71, 85, 105, 0.7);
  background: rgba(15, 23, 42, 0.98);
  box-shadow: 0 14px 30px rgba(2, 6, 23, 0.55);
  z-index: 40;
  padding: 6px;
}

.base-dropdown-enter-active,
.base-dropdown-leave-active {
  transition: opacity 0.14s ease, transform 0.14s ease;
}

.base-dropdown-enter-from,
.base-dropdown-leave-to {
  opacity: 0;
  transform: translateY(4px);
}

.base-dropdown-enter-to,
.base-dropdown-leave-from {
  opacity: 1;
  transform: translateY(0);
}
</style>
