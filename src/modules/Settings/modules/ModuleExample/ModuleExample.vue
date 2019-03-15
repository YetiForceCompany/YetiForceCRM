<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="ModuleExample"></div>
</template>
<script>
import mutations from 'src/store/mutations.js'
const moduleName = 'Settings.ModuleExample'
export default {
  name: moduleName,
  beforeMount() {
    this.$store.commit(mutations.App.Menu.addItem, {
      component: 'RoutePush',
      props: {
        path: '/settings/module-example',
        icon: 'home',
        label: 'Settings Example'
      }
    })
    this.$store.commit(mutations.App.Hooks.add, [
      {
        hookName: 'Settings.ModuleExample.Pages.ModuleExample.before',
        component: {
          name: 'test-before',
          render(createElement, context) {
            return createElement('div', null, ['Before hook works!'])
          }
        }
      },
      {
        hookName: 'Settings.ModuleExample.Pages.ModuleExample.after',
        component: {
          name: 'test-after',
          render(createElement, context) {
            return createElement('div', null, ['After hook works!'])
          }
        }
      }
    ])
  }
}
</script>
