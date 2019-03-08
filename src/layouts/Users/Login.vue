<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-layout>
    <q-page-container>
      <q-page class="row">
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 fixed-center">
          <div class="card-shadow q-pa-xl column">
            <div class="col-auto self-center q-pb-lg">
              <img class :src="env.publicDir + '/statics/Logo/logo'" />
            </div>
            <keep-alive>
              <router-view />
            </keep-alive>
            <q-banner v-if="$store.state.Users.message" :class="[msgClass, 'q-mt-lg', 'text-white']">
              <p>{{ $store.state.Users.message }}</p>
            </q-banner>
          </div>
        </div>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<style>
.card-shadow {
  box-shadow: 0 1px 5px rgba(0, 0, 0, 0.2), 0 2px 2px rgba(0, 0, 0, 0.14), 0 3px 1px -2px rgba(0, 0, 0, 0.12);
}
</style>

<script>
import { openURL } from 'quasar'
import actions from 'src/store/actions.js'
import getters from 'src/store/getters.js'
import { mapGetters } from 'vuex'
/**
 * @vue-data     {String}    activeComponent - component name
 * @vue-data     {Boolean}   showReminderForm - form data
 * @vue-data     {Boolean}   showLoginForm - form data
 * @vue-computed {Object}    env - env variables
 * @vue-computed {String}    msgClass - additional message class
 * @vue-event    {Object}    openURL
 */
export default {
  name: 'Users',
  data() {
    return {
      showReminderForm: false,
      showLoginForm: true
    }
  },
  computed: {
    ...mapGetters({
      env: getters.Env.all
    }),
    msgClass: function() {
      return {
        'bg-positive': this.$store.Users.messageType === 'success',
        'bg-negative': this.$store.Users.messageType === 'error',
        'bg-warning': this.$store.Users.messageType === ''
      }
    }
  },
  methods: {
    openURL
  },
  created() {
    this.$i18n.locale = 'Users'
  }
}
</script>
