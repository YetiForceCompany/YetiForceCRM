<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-breadcrumbs active-color="info" :class="['breadcrumbs', '']">
    <q-breadcrumbs-el icon="mdi-home" :to="'/'" />
    <q-breadcrumbs-el
      v-for="route in matched"
      :key="route.name"
      :label="route.path.split('/').pop()"
      :to="route.path"
    />
  </q-breadcrumbs>
</template>

<script>
export default {
  name: 'YfBreadcrumbs',
  computed: {
    matched() {
      return this.$route.matched.filter(route => route.path && route.path.split('/').pop())
    }
  }
}
</script>
<style module>
.breadcrumbs * {
  flex-wrap: nowrap;
}
</style>
