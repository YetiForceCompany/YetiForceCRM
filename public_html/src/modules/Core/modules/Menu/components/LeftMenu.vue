<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-list>
    <menu-item v-for="item in items" :item="item" :key="item.name" />
  </q-list>
</template>

<script>
import MenuItem from './Items/Item.vue.js'

const moduleName = 'Core.Left.Menu'
export default {
  name: moduleName,
  components: {
    MenuItem
  },
  data() {
    return {
      userName: 'User Name',
      companyName: 'Company Name'
    }
  },
  computed: {
    items() {
      return this.$store.state.Core.Menu.items
    }
  }
}
</script>
<style module></style>
