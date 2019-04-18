<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="column">
    <div class="col-auto self-center q-pb-lg">
      <img class :src="env.publicDir + '/statics/Logo/logo'" />
    </div>
    <keep-alive>
      <router-view />
    </keep-alive>
  </div>
</template>

<script>
import getters from '/store/getters.js'
/**
 * @vue-data     {String}    activeComponent - component name
 * @vue-data     {Boolean}   showReminderForm - form data
 * @vue-data     {Boolean}   showLoginForm - form data
 * @vue-computed {Object}    env - env variables
 */
const moduleName = 'Core.Users.Layouts.Login'
export default {
  name: moduleName,
  data() {
    return {
      showReminderForm: false,
      showLoginForm: true
    }
  },
  computed: {
    ...Vuex.mapGetters({
      env: getters.Core.Env.all
    })
  }
}
</script>

<style scoped>
.card-shadow {
  box-shadow: 0 1px 5px rgba(0, 0, 0, 0.2), 0 2px 2px rgba(0, 0, 0, 0.14), 0 3px 1px -2px rgba(0, 0, 0, 0.12);
}
img {
  width: 100px;
}
</style>
