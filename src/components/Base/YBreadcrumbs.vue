<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template functional>
  <q-breadcrumbs active-color="info" :class="[$style.breadcrumbs, '']">
    <q-breadcrumbs-el icon="mdi-home" to="/" />
    <q-breadcrumbs-el label="Docs" icon="mdi-widgets" to="/docs" />
    <q-breadcrumbs-el label="Breadcrumbs" icon="mdi-navigation" to="/vue-components/breadcrumbs" />
    <q-breadcrumbs-el label="Build" icon="mdi-build" />
  </q-breadcrumbs>
</template>

<script>
export default {
  name: 'YBreadcrumbs'
}
</script>

<style module>
.breadcrumbs * {
  flex-wrap: nowrap;
}
</style>
