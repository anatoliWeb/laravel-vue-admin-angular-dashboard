<template>
  <!--
    Root app shell.
    Routing + layout composition happens under router views.
  -->
  <router-view />
  <ToastContainer />
  <ConfirmContainer />
  <ModalContainer />
  <DrawerContainer />
</template>

<script setup lang="ts">
import ConfirmContainer from './shared/confirm/components/ConfirmContainer.vue';
import DrawerContainer from './shared/drawer/components/DrawerContainer.vue';
import ModalContainer from './shared/modal/components/ModalContainer.vue';
import ToastContainer from './shared/toast/components/ToastContainer.vue';
</script>
