<template>
  <!--
    Root app shell.
    Routing + layout composition happens under router views.
  -->
  <router-view />
  <ToastContainer />
</template>

<script setup lang="ts">
import ToastContainer from './shared/toast/components/ToastContainer.vue';
</script>
