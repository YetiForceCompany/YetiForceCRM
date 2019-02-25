<template>
  <q-list>
    <q-item-label header>YetiForceCRM</q-item-label>
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
  name: 'BaseLeftMenu',
  components: {
    RoutePush
  },
  computed: {
    positions() {
      return this.$store.state.Base.menu.positions
    }
  }
}
</script>
