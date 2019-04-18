<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="fixed-center text-center">
    <p class="text-faded">
      <strong>{{ code }}</strong>
    </p>
    <p>{{ message }}</p>
    <q-btn color="secondary" style="width:200px;" @click="$router.go()">Reload</q-btn>
  </div>
</template>

<script>
import { Router } from '/src/router/index.js'
export default {
  name: 'Exception',
  data: function() {
    return {
      code: window.env.exception.code,
      message: window.env.exception.message
    }
  },
  beforeRouteEnter(to, from, next) {
    if (window.env.exception !== undefined) {
      next()
    } else {
      Router.afterHooks[0]()
      next('/')
    }
  }
}
</script>
