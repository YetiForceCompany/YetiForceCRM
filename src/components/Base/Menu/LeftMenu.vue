<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-list>
    <q-item header class="bg-black text-white q-toolbar q-px-md">
      <q-item-section avatar>
        <q-icon name="person" />
      </q-item-section>
      <q-item-section>
        <q-item-label>{{ userName }}</q-item-label>
      </q-item-section>
    </q-item>
    <component
      v-for="position in positions"
      :is="position.component"
      :key="position.key"
      v-bind="position.props"
    ></component>
  </q-list>
</template>

<script>
import RoutePush from './Positions/RoutePush'

export default {
  data() {
    return {
      userName: 'User Name',
      companyName: 'Company Name'
    }
  },
  name: 'BaseLeftMenu',
  components: {
    RoutePush
  },
  computed: {
    positions() {
      return this.$store.state.Menu.items
    }
  }
}
</script>
<style module></style>
