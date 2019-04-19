<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="fixed-center text-center">
    <p class="text-faded">
      <strong>{{ code }}</strong>
    </p>
    <p>{{ message }}</p>
    <q-btn round color="secondary" icon="mdi-reload" @click="$router.go()"><q-tooltip>Reload</q-tooltip></q-btn>
  </div>
</template>

<script>
import { Router } from '/src/router/index.js'
export default {
  name: 'Exception',
  props: {
    code: { default: window.env.exception ? window.env.exception.code : 0, type: Number },
    message: { default: window.env.exception ? window.env.exception.message : '', type: String }
  },
  beforeRouteEnter(to, from, next) {
    if (to.params.code || window.env.exception !== undefined) {
      next()
    } else {
      Router.afterHooks[0]()
      next('/')
    }
  }
}
</script>
