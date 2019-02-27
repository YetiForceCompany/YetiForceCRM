<template>
  <div class="ModuleExample"></div>
</template>
<script>
import mutations from '../../store/mutations.js'
import getters from '../../store/getters.js'
import { mapGetters } from 'vuex'

export default {
  name: 'ModuleExample',
  computed: {
    ...mapGetters({
      menuPositions: getters.Base.menuPositions
    })
  },
  mounted() {
    console.log(this.menuPositions)
    const positions = this.menuPositions.map(position => position)
    positions.push({
      component: 'RoutePush',
      props: {
        path: '/module-example',
        icon: 'home',
        label: 'Module Example'
      }
    })
    this.$store.commit(mutations.Base.updateMenuPositions, positions)
  }
}
</script>
