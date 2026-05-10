<template>
  <!--
    Root app shell.
    Routing + layout composition happens under router views.
  -->
  <router-view />
  <ToastContainer />
  <ConfirmContainer />
  <CommandPaletteContainer />
  <ModalContainer />
  <DrawerContainer />
  <FloatingPanelContainer />
</template>

<script setup lang="ts">
import CommandPaletteContainer from './shared/command-palette/components/CommandPaletteContainer.vue';
import ConfirmContainer from './shared/confirm/components/ConfirmContainer.vue';
import DrawerContainer from './shared/drawer/components/DrawerContainer.vue';
import FloatingPanelContainer from './shared/floating-panel/components/FloatingPanelContainer.vue';
import ModalContainer from './shared/modal/components/ModalContainer.vue';
import ToastContainer from './shared/toast/components/ToastContainer.vue';
import { useCommandPalette } from './shared/command-palette/composables/useCommandPalette';

useCommandPalette();
</script>
