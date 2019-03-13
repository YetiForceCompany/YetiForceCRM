<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div></div>
</template>
<script>
import moduleStore from './store/index.js'
import store from 'src/store/index.js'

export default {
  name: 'App',
  created() {
    this.$store.registerModule('App', moduleStore)
  }
}
</script>
