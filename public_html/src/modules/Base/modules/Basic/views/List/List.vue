<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <list :moduleName="moduleName" :columns="columns" :data="data"></list>
</template>

<script>
import List from './components/List.vue.js'
import actions from '/src/store/actions.js'
import { store } from '/src/store/index.js'

const moduleName = 'Base.Basic.List'
export default {
  name: moduleName,
  data() {
    return {
      moduleName: 'Basic',
      columns: [],
      data: []
    }
  },
  components: { List },
  beforeRouteEnter(to, from, next) {
    let storeModule = to.meta.moduleName ? to.meta.moduleName : 'Basic'
    let actionName = actions.Base.Basic.List.fetchData //TODO actions.Base[to.meta.moduleName].List.getListData | function for choosing right module
    store.dispatch(actionName).then(data => {
      next(vm => {
        vm.columns = data.columns
        vm.data = data.data
        vm.moduleName = storeModule
      })
    })
  }
}
</script>
