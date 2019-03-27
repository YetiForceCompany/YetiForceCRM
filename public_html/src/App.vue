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
