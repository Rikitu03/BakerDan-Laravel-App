<template>
  <CustomerLayout
    :page-component="route.component"
    :page-props="route.props"
    :layout-props="route.layoutProps"
  />
</template>

<script setup>
import { onMounted } from 'vue';
import CustomerLayout from './components/layout/CustomerLayout.vue';
import { useSpaRoute } from './router';

const route = useSpaRoute();

onMounted(() => {
  console.log('BAKERDAN customer SPA mounted.');
});
</script>
