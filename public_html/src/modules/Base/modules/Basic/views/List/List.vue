<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <list :moduleName="moduleName"></list>
</template>

<script>
import List from './components/List.vue.js'
const moduleName = 'Base.Basic.List'
export default {
  name: moduleName,
  data() {
    return {
      moduleName: 'Basic'
    }
  },
  components: { List },
  beforeRouteEnter(to, from, next) {
    next(vm => {
      vm.moduleName = to.meta.moduleName ? to.meta.moduleName : vm.moduleName
    })
  }
}
</script>
