<template>
  <article class="base-modal" :class="[`is-${item.size}`]" role="dialog" aria-modal="true" :aria-labelledby="headerId">
    <header v-if="item.title || item.subtitle" class="base-modal__header" :class="{ 'is-sticky': item.stickyHeader }">
      <div class="base-modal__titles">
        <h3 :id="headerId" class="base-modal__title">{{ item.title }}</h3>
        <p v-if="item.subtitle" class="base-modal__subtitle">{{ item.subtitle }}</p>
      </div>
      <button type="button" class="base-modal__close" :disabled="item.loading" aria-label="Close modal" @click="emit('close')">?</button>
    </header>

    <section class="base-modal__content">
      <slot />
    </section>

    <footer v-if="item.actions?.length || $slots.footer" class="base-modal__footer" :class="{ 'is-sticky': item.stickyFooter }">
      <slot name="footer">
        <button
          v-for="(action, index) in item.actions"
          :key="`${item.id}-${index}`"
          type="button"
          class="base-modal__action"
          :class="[`is-${action.kind ?? 'secondary'}`]"
          :disabled="item.loading || action.disabled"
          @click="emit('action', index)"
        >
          {{ action.label }}
        </button>
      </slot>
    </footer>
  </article>
</template>

<script setup lang="ts">
import { computed } from 'vue';

import type { ModalItem } from '../types/modal.types';

const props = defineProps<{
  item: ModalItem;
}>();

const emit = defineEmits<{
  close: [];
  action: [index: number];
}>();

const headerId = computed(() => `modal-title-${props.item.id}`);
</script>

<style scoped>
.base-modal{width:min(100%,720px);max-height:calc(100vh - 40px);display:grid;grid-template-rows:auto 1fr auto;border-radius:14px;border:1px solid rgba(71,85,105,.6);background:rgba(15,23,42,.98);box-shadow:0 28px 56px rgba(2,6,23,.62);overflow:hidden}
.base-modal.is-sm{width:min(100%,420px)}
.base-modal.is-md{width:min(100%,620px)}
.base-modal.is-lg{width:min(100%,820px)}
.base-modal.is-xl{width:min(100%,1020px)}
.base-modal.is-fullscreen{width:calc(100vw - 16px);height:calc(100vh - 16px);max-height:none}
.base-modal__header{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;padding:14px 16px;border-bottom:1px solid rgba(71,85,105,.42);background:rgba(15,23,42,.96)}
.base-modal__header.is-sticky{position:sticky;top:0;z-index:1}
.base-modal__title{margin:0;color:#f8fafc;font-size:17px}
.base-modal__subtitle{margin:5px 0 0;color:#94a3b8;font-size:12px;line-height:1.4}
.base-modal__close{border:1px solid rgba(71,85,105,.55);background:rgba(30,41,59,.75);color:#cbd5e1;width:30px;height:30px;border-radius:8px;font-size:18px;line-height:1}
.base-modal__content{padding:14px 16px;overflow:auto}
.base-modal__footer{display:flex;justify-content:flex-end;gap:8px;padding:12px 16px;border-top:1px solid rgba(71,85,105,.42);background:rgba(15,23,42,.96)}
.base-modal__footer.is-sticky{position:sticky;bottom:0;z-index:1}
.base-modal__action{height:34px;padding:0 12px;border-radius:9px;border:1px solid rgba(71,85,105,.55);background:rgba(30,41,59,.8);color:#e2e8f0;font-size:12px}
.base-modal__action.is-primary{border-color:rgba(59,130,246,.55);background:rgba(59,130,246,.2);color:#bfdbfe}
.base-modal__action.is-danger{border-color:rgba(239,68,68,.55);background:rgba(239,68,68,.2);color:#fecaca}
.base-modal__action:disabled,.base-modal__close:disabled{opacity:.6;cursor:not-allowed}
</style>
