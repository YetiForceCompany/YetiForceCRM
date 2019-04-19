<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import ModuleLoader from '/src/ModuleLoader.js'
import moduleStore from './store/index.js'
import mutations from '/store/mutations.js'
let mstore = new moduleStore()
console.log(mstore)
const moduleName = mstore.state.moduleName
const fullModuleName = 'Base.' + mstore.state.moduleName

export function initialize({ store, router }) {
	store.registerModule(fullModuleName.split('.'), ModuleLoader.prepareStoreNames(fullModuleName, mstore))
	console.log('12341234', mstore.state.menu)
	// if (moduleStore.state.menu) {
	//   store.commit(mutations.Core.Menu.addItem, {
	//     path: `/${moduleName}`,
	//     icon: 'mdi-cube',
	//     label: moduleName,
	//     children: []
	//   })
	// }
}

export default {
	name: fullModuleName
}
</script>
