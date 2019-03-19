<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-list>
    <q-item header class="bg-black text-white q-toolbar q-px-md">
      <q-item-section avatar>
        <q-icon name="mdi-account" />
      </q-item-section>
      <q-item-section>
        <q-item-label>{{ userName }}</q-item-label>
      </q-item-section>
    </q-item>
    <position v-for="item in items" :item="item" :key="item.id" />
  </q-list>
</template>

<script>
import Position from './Positions/Position.vue'

const moduleName = 'Core.Left.Menu'
export default {
  name: moduleName,
  components: {
    Position
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
