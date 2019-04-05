<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <hook-wrapper class="Main">
    <component v-for="module in modules" :key="module.fullName" :is="module.component"></component>
    <router-view />
  </hook-wrapper>
</template>

<script>
const moduleName = 'Main'
export default {
  name: moduleName,
  props: ['modules']
}
</script>

<style></style>
