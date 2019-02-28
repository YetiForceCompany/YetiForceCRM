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
            <div>
              <form
                v-if="!CONFIG.IS_BLOCKED_IP"
                @submit.prevent.stop="onSubmit"
                class="col q-gutter-md q-mx-lg"
                :autocomplete="CONFIG.LOGIN_PAGE_REMEMBER_CREDENTIALS ? 'on' : 'off'"
              >
                <q-input
                  ref="user"
                  v-model="user"
                  :label="$t('LBL_USER')"
                  lazy-rules
                  :rules="[val => (val && val.length > 0) || 'Please type something']"
                  :autocomplete="CONFIG.LOGIN_PAGE_REMEMBER_CREDENTIALS ? 'on' : 'off'"
                />
                <q-input
                  ref="password"
                  type="password"
                  v-model="password"
                  :label="$t('Password')"
                  lazy-rules
                  :rules="[val => (val && val.length > 0) || 'Please type something']"
                  :autocomplete="CONFIG.LOGIN_PAGE_REMEMBER_CREDENTIALS ? 'on' : 'off'"
                />
                <q-select
                  v-if="CONFIG.LANGUAGE_SELECTION"
                  v-model="language"
                  :options="CONFIG.languages"
                  :label="$t('LBL_CHOOSE_LANGUAGE')"
                >
                  <template v-slot:prepend>
                    <q-icon name="translate" />
                  </template>
                </q-select>
                <q-select
                  v-if="CONFIG.LAYOUT_SELECTION"
                  v-model="layout"
                  :options="CONFIG.LAYOUTS"
                  :label="$t('LBL_SELECT_LAYOUT')"
                >
                  <template v-slot:prepend>
                    <q-icon name="looks" />
                  </template>
                </q-select>
                <q-btn
                  size="lg"
                  :label="$t('LBL_SIGN_IN')"
                  type="submit"
                  color="secondary"
                  class="full-width q-mt-lg"
                />
                <a v-if="CONFIG.FORGOT_PASSWORD" class="text-secondary float-right" href="#"
                  >{{ $t('ForgotPassword') }}?</a
                >
              </form>
              <q-banner v-else class="bg-negative q-mt-lg text-white">
                <div class="text-center">
                  <q-icon name="remove_circle_outline" class="text-h1 q-pb-md"></q-icon>
                </div>
                <p>{{ $t('LBL_IP_IS_BLOCKED') }}</p>
              </q-banner>
              <q-banner v-if="CONFIG.MESSAGE" :class="[msgClass, 'q-mt-lg', 'text-white']">
                <p>{{ CONFIG.MESSAGE }}</p>
              </q-banner>
            </div>
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
import actions from '../store/actions.js'
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
      user: '',
      password: '',
      language: CONFIG.DEFAULT_LANGUAGE, //AppConfig::main('default_language')
      layout: '',
      CONFIG: CONFIG
    }
  },
  computed: {
    msgClass: function() {
      return {
        'bg-positive': this.CONFIG.MESSAGE_TYPE === 'success',
        'bg-negative': this.CONFIG.MESSAGE_TYPE === 'error',
        'bg-warning': this.CONFIG.MESSAGE_TYPE === ''
      }
    }
  },
  methods: {
    onSubmit() {
      this.$refs.user.validate()
      this.$refs.password.validate()
      if (this.$refs.user.hasError || this.$refs.password.hasError) {
        this.formHasError = true
      } else {
        this.$store.dispatch(actions.Login.login, { user: this.user, password: this.password })
      }
    }
  }
}
</script>
