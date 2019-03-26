<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import mutations from 'src/store/mutations.js'
const moduleName = 'Settings.Installed'
export default {
  name: moduleName,
  beforeMount() {
    this.$store.commit(mutations.Core.Menu.addItem, {
      path: '/settings/installed',
      icon: 'mdi-cube',
      label: 'Installed',
      children: []
    })
    this.$store.commit(mutations.Core.Hooks.add, [
      {
        hookName: 'Settings.Installed.Pages.Installed.before',
        component: {
          name: 'test-before',
          render(createElement, context) {
            return createElement('div', null, ['Before hook works!'])
          }
        }
      },
      {
        hookName: 'Settings.Installed.Pages.Installed.after',
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
