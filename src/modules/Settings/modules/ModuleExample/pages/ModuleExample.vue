<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div>
    <hook name="before"/>Layout -> Settings -> ModuleExample page
    <hook name="after"/>
  </div>
</template>
<script>
import Hook from 'modules/Base/modules/Hooks/components/Hook.vue'
import mutations from 'store/mutations.js'

const moduleName = 'Settings.ModuleExample.Pages.ModuleExample'
export default {
  name: moduleName,
  components: { Hook },
  beforeMount() {
    this.$store.commit(mutations.Base.Hooks.add, [
      {
        hookName: moduleName + '.before',
        component: {
          name: 'test',
          render(createElement, context) {
            return createElement('div', null, ['Before hook works!'])
          }
        }
      },
      {
        hookName: moduleName + '.after',
        component: {
          name: 'test2',
          render(createElement, context) {
            return createElement('div', null, ['After hook works!'])
          }
        }
      }
    ])
  }
}
</script>
