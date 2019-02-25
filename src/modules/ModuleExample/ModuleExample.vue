<template>
  <div class="ModuleExample"></div>
</template>
<script>
export default {
  name: "ModuleExample",
  mounted() {
    const positions = this.$store.state.Base.menu.positions.map(
      position => position
    );
    positions.push({
      component: "RoutePush",
      props: {
        path: "/home/module-example",
        icon: "home",
        label: "Module Example"
      }
    });
    this.$store.commit("Base/updateMenuPositions", positions);
  }
};
</script>
