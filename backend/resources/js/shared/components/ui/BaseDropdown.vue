<template>
  <div ref="root" class="base-dropdown" :class="[{ 'is-open': isOpen }, `is-${verticalDirection}`, `is-${horizontalAlign}`]">
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
      <div
        v-if="isOpen"
        ref="menu"
        class="base-dropdown__menu"
        role="menu"
        :style="menuStyle"
      >
        <slot :close="close" />
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

/**
 * Centralized dropdown engine for admin UI.
 *
 * WHY THIS ENGINE EXISTS:
 * - keeps interaction and visual behavior consistent for every dropdown
 * - prevents viewport clipping by auto-selecting up/down direction
 * - gives one reusable floating-menu strategy for filters, profile menus,
 *   language switcher, rows-per-page, and future table/action popovers
 */
const isOpen = ref(false);
const root = ref<HTMLElement | null>(null);
const menu = ref<HTMLElement | null>(null);

const verticalDirection = ref<'down' | 'up'>('down');
const horizontalAlign = ref<'right' | 'left'>('right');
const menuStyle = ref<Record<string, string>>({});

const close = (): void => {
  isOpen.value = false;
};

const toggle = (): void => {
  isOpen.value = !isOpen.value;
};

const updatePosition = (): void => {
  if (!root.value || !menu.value) {
    return;
  }

  const rootRect = root.value.getBoundingClientRect();
  const menuRect = menu.value.getBoundingClientRect();
  const viewportHeight = window.innerHeight;
  const viewportWidth = window.innerWidth;
  const gap = 8;

  const spaceBelow = viewportHeight - rootRect.bottom;
  const spaceAbove = rootRect.top;

  verticalDirection.value =
    spaceBelow >= menuRect.height + gap || spaceBelow >= spaceAbove ? 'down' : 'up';

  const wouldOverflowRight = rootRect.right - menuRect.width < 0;
  const wouldOverflowLeft = rootRect.left + menuRect.width > viewportWidth;

  if (wouldOverflowLeft && !wouldOverflowRight) {
    horizontalAlign.value = 'right';
  } else if (wouldOverflowRight && !wouldOverflowLeft) {
    horizontalAlign.value = 'left';
  } else {
    horizontalAlign.value = 'right';
  }

  const maxHeight = Math.max((verticalDirection.value === 'down' ? spaceBelow : spaceAbove) - gap - 4, 120);

  menuStyle.value = {
    maxHeight: `${Math.floor(maxHeight)}px`,
    overflowY: 'auto',
  };
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

const onViewportChange = (): void => {
  if (!isOpen.value) {
    return;
  }

  updatePosition();
};

watch(isOpen, async (opened) => {
  if (!opened) {
    return;
  }

  await nextTick();
  updatePosition();
});

onMounted(() => {
  document.addEventListener('click', onDocumentClick);
  document.addEventListener('keydown', onEscape);
  window.addEventListener('resize', onViewportChange);
  window.addEventListener('scroll', onViewportChange, true);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', onDocumentClick);
  document.removeEventListener('keydown', onEscape);
  window.removeEventListener('resize', onViewportChange);
  window.removeEventListener('scroll', onViewportChange, true);
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
  min-width: 180px;
  overflow: hidden;
  border-radius: 10px;
  border: 1px solid rgba(71, 85, 105, 0.7);
  background: rgba(15, 23, 42, 0.98);
  box-shadow: 0 14px 30px rgba(2, 6, 23, 0.55);
  z-index: 40;
  padding: 6px;
}

.base-dropdown.is-down .base-dropdown__menu {
  top: calc(100% + 8px);
}

.base-dropdown.is-up .base-dropdown__menu {
  bottom: calc(100% + 8px);
}

.base-dropdown.is-right .base-dropdown__menu {
  right: 0;
}

.base-dropdown.is-left .base-dropdown__menu {
  left: 0;
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

.base-dropdown.is-up .base-dropdown-enter-from,
.base-dropdown.is-up .base-dropdown-leave-to {
  transform: translateY(-4px);
}

.base-dropdown-enter-to,
.base-dropdown-leave-from {
  opacity: 1;
  transform: translateY(0);
}
</style>
