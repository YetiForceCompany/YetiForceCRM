<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <component :is="component" :icon="item.icon" :label="item.label" :path="item.path" :children="item.children" />
</template>
<script>
import RoutePush from './RoutePush.js'
import Expander from './Expander.js'

const moduleName = 'Core.Menu.Items.Item'
export default {
  name: moduleName,
  components: {
    RoutePush,
    Expander
  },
  props: ['item'],
  computed: {
    component() {
      if (this.item.children.length) {
        return 'Expander'
      } else {
        return 'RoutePush'
      }
    }
  }
}
</script>
