<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script>
const moduleName = 'App'

export default {
  name: moduleName,
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
