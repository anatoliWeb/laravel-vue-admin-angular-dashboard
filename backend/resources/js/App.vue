<template>
  <!--
    Root app shell.
    Routing + layout composition happens under router views.
  -->
  <router-view />
  <ToastContainer />
  <ConfirmContainer />
</template>

<script setup lang="ts">
import ConfirmContainer from './shared/confirm/components/ConfirmContainer.vue';
import ToastContainer from './shared/toast/components/ToastContainer.vue';
</script>
