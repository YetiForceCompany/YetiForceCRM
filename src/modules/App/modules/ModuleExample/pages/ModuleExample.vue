<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div>
    Layout -> App -> ModuleExample page
    <button @click="updateVariable">{{testVariable}}</button>
  </div>
</template>

<style>
</style>

<script>
export default {
  name: 'App.ModuleExample.Pages.ModuleExample',
  methods: {
    updateVariable() {
      this.$store.commit('App/ModuleExample/updateTestVariable', 'changed!')
    }
  },
  computed: {
    testVariable() {
      return this.$store.getters['App/ModuleExample/testVariable']
    }
  }
}
</script>
