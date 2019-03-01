<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-layout>
    <q-page-container>
      <q-page class="row">
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 fixed-center">
          <div class="card-shadow q-pa-xl column">
            <div class="col-auto self-center q-pb-lg">
              <img class src="statics/Logo/logo" width="100" />
            </div>
            <keep-alive>
              <component
                v-if="!CONFIG.IS_BLOCKED_IP"
                :is="activeComponent"
                :CONFIG="CONFIG"
                :toggleActiveComponent="toggleActiveComponent"
              />
            </keep-alive>
            <q-banner v-if="CONFIG.IS_BLOCKED_IP" class="bg-negative q-mt-lg text-white">
              <p>{{ $t('LBL_IP_IS_BLOCKED') }}</p>
            </q-banner>
            <q-banner v-if="CONFIG.MESSAGE" :class="[msgClass, 'q-mt-lg', 'text-white']">
              <p>{{ CONFIG.MESSAGE }}</p>
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
import Form from '../components/Login/Form.vue'
import Reminder from '../components/Login/Reminder.vue'
const CONFIG = {
  // component config loaded from server
  LANGUAGES: ['polish', 'english', 'german'],
  IS_BLOCKED_IP: false, //bruteforce check,
  MESSAGE: '', //\App\Session::get('UserLoginMessageType'),
  MESSAGE_TYPE: '',
  LOGIN_PAGE_REMEMBER_CREDENTIALS: true, // AppConfig::security('LOGIN_PAGE_REMEMBER_CREDENTIALS')
  FORGOT_PASSWORD: true, //{if AppConfig::security('RESET_LOGIN_PASSWORD') && App\Mail::getDefaultSmtp()}
  LANGUAGE_SELECTION: true,
  DEFAULT_LANGUAGE: 'polish',
  LAYOUT_SELECTION: true,
  LAYOUTS: ['material', 'ios'] //\App\Layout::getAllLayouts()
}
export default {
  name: 'Login',
  data() {
    return {
      activeComponent: 'login-form',
      showReminderForm: false,
      showLoginForm: true,
      CONFIG: CONFIG
    }
  },
  components: {
    loginForm: Form,
    reminderForm: Reminder
  },
  methods: {
    toggleActiveComponent: function(componentName) {
      this.activeComponent = componentName
    }
  }
}
</script>
