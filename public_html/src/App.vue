<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div id="app">
    <component v-for="module in modules" :key="module.fullName" :is="module.component" />
    <router-view />
  </div>
</template>

<script>
const moduleName = 'App'

export default {
  name: moduleName,
  props: ['modules'],
  provide() {
    const provider = {}
    const self = this
    Object.defineProperty(provider, 'App', {
      enumerable: true,
      get: () => self
    })
    return provider
  }
}
</script>

<style></style>
