<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div>
    Layout -> Base -> ModuleExample page
    <button @click="updateVariable">{{ testVariable }}</button>
  </div>
</template>

<script>
import getters from 'src/store/getters.js'
import mutations from 'src/store/mutations.js'
import { mapGetters } from 'vuex'

const moduleName = 'Base.ModuleExample.Pages.ModuleExample'
export default {
  name: moduleName,
  methods: {
    updateVariable() {
      this.$store.commit(mutations.Base.ModuleExample.updateTestVariable, 'changed!')
    }
  },
  computed: {
    ...mapGetters({
      testVariable: getters.Base.ModuleExample.testVariable
    })
  }
}
</script>
