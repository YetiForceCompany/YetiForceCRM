<template>
  <div id="q-app">
    <router-view/>
  </div>
</template>

<script>
  export default {
    preFetch({store, redirect}) {
      console.log(store.state.login.isLoggedIn);
      if (!store.state.login.isLoggedIn) {
        redirect('/login')
      }
    },
    name: 'App'
  }
</script>

<style>
</style>
