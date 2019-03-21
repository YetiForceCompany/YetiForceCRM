<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <hook-wrapper class="Core-Component-YfBreadcrumbs">
    <q-breadcrumbs active-color="info" :class="[$style.breadcrumbs, '']">
      <q-breadcrumbs-el icon="mdi-home-outline" :to="'/'" />
      <q-breadcrumbs-el
        v-for="route in $route.matched"
        :key="route.name"
        v-if="route.path && route.path.split('/').pop()"
        :label="route.path.split('/').pop()"
        :to="route.path"
      />
    </q-breadcrumbs>
  </hook-wrapper>
</template>

<script>
export default {
  name: 'YfBreadcrumbs'
}
</script>
<style module>
.breadcrumbs * {
  flex-wrap: nowrap;
}
</style>
