<template>
  <Teleport to="body">
    <div class="floating-panel-root">
      <TransitionGroup name="floating-panel-stack" tag="div">
        <section
          v-for="item in items"
          :key="item.id"
          :ref="(el) => setPanelRef(item.id, el as HTMLElement | null)"
          class="floating-panel-layer"
          :style="panelStyle(item.id)"
          @click.stop
        >
          <BaseFloatingPanel :item="item" @close="close(item.id)">
            <component :is="item.component" v-bind="item.props" :panel-id="item.id" :close-panel="() => close(item.id)" />
          </BaseFloatingPanel>
        </section>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, watch } from 'vue';

import BaseFloatingPanel from './BaseFloatingPanel.vue';
import { floatingPanelStore } from '../stores/floating-panel.store';
import { computeFloatingPanelPosition } from '../utils/floating-panel.utils';

/**
 * Floating panel container is a dedicated contextual overlay layer.
 *
 * It sits above dropdown menus for richer preview content, but below drawers and
 * dialogs so heavyweight workflows keep visual priority.
 */
const items = computed(() => floatingPanelStore.items.value);

const panelRefs = reactive<Record<string, HTMLElement | null>>({});
const panelStyles = reactive<Record<string, Record<string, string>>>({});
let rafId: number | null = null;

const close = (id?: string): void => {
  floatingPanelStore.close(id);
};

const setPanelRef = (id: string, el: HTMLElement | null): void => {
  panelRefs[id] = el;
};

const panelStyle = (id: string): Record<string, string> => {
  return panelStyles[id] ?? { opacity: '0' };
};

const updatePositions = (): void => {
  items.value.forEach((item, index) => {
    const el = panelRefs[item.id];
    if (!el) return;

    const width = el.offsetWidth;
    const height = el.offsetHeight;
    if (!width || !height) return;

    const point = computeFloatingPanelPosition(item, width, height);

    panelStyles[item.id] = {
      position: 'fixed',
      left: `${Math.round(point.x)}px`,
      top: `${Math.round(point.y)}px`,
      zIndex: String(1700 + index),
    };
  });
};

const queueReposition = (): void => {
  if (rafId !== null) {
    cancelAnimationFrame(rafId);
  }

  rafId = window.requestAnimationFrame(() => {
    updatePositions();
    rafId = null;
  });
};

const onDocumentClick = (event: MouseEvent): void => {
  const target = event.target as Node;
  const top = items.value[items.value.length - 1];
  if (!top) return;

  const panelEl = panelRefs[top.id];
  if (panelEl?.contains(target)) {
    return;
  }

  if (top.trigger?.contains(target)) {
    return;
  }

  if (!top.persistent) {
    close(top.id);
  }
};

const onKeydown = (event: KeyboardEvent): void => {
  const top = items.value[items.value.length - 1];
  if (!top) return;

  if (event.key === 'Escape' && !top.persistent) {
    event.preventDefault();
    close(top.id);
  }
};

watch(
  () => items.value.map((entry) => entry.id).join('|'),
  async () => {
    await nextTick();
    queueReposition();
  },
);

onMounted(() => {
  document.addEventListener('click', onDocumentClick);
  document.addEventListener('keydown', onKeydown);
  window.addEventListener('resize', queueReposition);
  window.addEventListener('scroll', queueReposition, true);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', onDocumentClick);
  document.removeEventListener('keydown', onKeydown);
  window.removeEventListener('resize', queueReposition);
  window.removeEventListener('scroll', queueReposition, true);
  if (rafId !== null) {
    cancelAnimationFrame(rafId);
  }
});
</script>

<style scoped>
.floating-panel-root{position:fixed;inset:0;pointer-events:none}
.floating-panel-layer{pointer-events:auto}
.floating-panel-stack-enter-active,.floating-panel-stack-leave-active{transition:opacity .16s ease, transform .16s ease}
.floating-panel-stack-enter-from,.floating-panel-stack-leave-to{opacity:0;transform:translateY(4px) scale(.99)}
</style>
