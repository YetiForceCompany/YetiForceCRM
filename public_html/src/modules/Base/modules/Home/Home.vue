<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import mutations from '/store/mutations.js'

const moduleName = 'Base.Home'

export function initialize({ store, router }) {
  store.commit(mutations.Core.Menu.addItem, {
    path: '/home',
    icon: 'mdi-home',
    label: 'Home',
    children: []
  })
}

export default {
  name: moduleName
}
</script>
