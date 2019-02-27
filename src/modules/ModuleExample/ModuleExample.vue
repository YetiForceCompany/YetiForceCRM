<template>
  <div class="ModuleExample"></div>
</template>
<script>
import mutations from '../../store/mutations.js'
export default {
  name: 'ModuleExample',
  mounted() {
    const positions = this.$store.state.Base.menu.positions.map(position => position)
    positions.push({
      component: 'RoutePush',
      props: {
        path: '/module-example',
        icon: 'home',
        label: 'Module Example'
      }
    })
    // this.$store.commit('Base/updateMenuPositions', positions)
    // same as
    this.$store.commit(mutations.Global.update, {
      Base: {
        menu: {
          positions
        }
      }
    })
  }
}
</script>
