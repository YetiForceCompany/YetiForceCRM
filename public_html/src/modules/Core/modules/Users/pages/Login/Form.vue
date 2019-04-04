<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div>
    <form
      @submit.prevent.stop="onSubmit"
      class="col q-gutter-md q-mx-lg"
      :autocomplete="loginPageRememberCredentials ? 'on' : 'off'"
    >
      <q-input
        type="text"
        ref="user"
        v-model="user"
        :label="$t('LBL_USER')"
        lazy-rules
        :rules="[val => (val && val.length > 0) || 'Please type something']"
      >
        <template v-slot:prepend>
          <q-icon name="mdi-account" />
        </template>
      </q-input>
      <q-input
        type="password"
        ref="password"
        v-model="password"
        :label="$t('Password')"
        lazy-rules
        :rules="[val => (val && val.length > 0) || 'Please type something']"
      >
        <template v-slot:prepend>
          <q-icon name="mdi-lock" />
        </template>
      </q-input>
      <q-select
        v-if="langInLoginView"
        v-model="language"
        :options="$store.state.Core.Language.langs"
        :label="$t('LBL_CHOOSE_LANGUAGE')"
      >
        <template v-slot:prepend>
          <q-icon name="mdi-translate" />
        </template>
      </q-select>
      <q-select v-if="layoutInLoginView" v-model="layout" :label="$t('LBL_SELECT_LAYOUT')">
        <template v-slot:prepend>
          <q-icon name="mdi-looks" />
        </template>
      </q-select>
      <q-btn size="lg" :label="$t('LBL_SIGN_IN')" type="submit" color="secondary" class="full-width q-mt-lg" />
      <router-link
        v-if="resetLoginPassword"
        class="text-secondary float-right"
        :to="{ name: 'Core.Users.Login.Reminder' }"
        >{{ $t('ForgotPassword') }}</router-link
      >
    </form>
  </div>
</template>
<script>
import actions from '/src/store/actions.js'
import getters from '/src/store/getters.js'
/**
 * @vue-data     {String} user - form data
 * @vue-data     {String} password - form data
 * @vue-data     {String} language - form data
 * @vue-data     {String} layout - form data
 * @vue-event    {Object} onSubmit - submit form event
 */
export default {
  name: 'Login',
  data() {
    return {
      user: '',
      password: '',
      language: this.$store.state.Core.Language.lang,
      layout: ''
    }
  },
  computed: {
    ...Vuex.mapGetters({
      loginPageRememberCredentials: getters.Core.Users.loginPageRememberCredentials,
      layoutInLoginView: getters.Core.Users.layoutInLoginView,
      defaultLayout: getters.Core.Users.defaultLayout,
      langInLoginView: getters.Core.Users.langInLoginView,
      resetLoginPassword: getters.Core.Users.resetLoginPassword
    })
  },
  methods: {
    onSubmit() {
      this.$refs.user.validate()
      this.$refs.password.validate()
      if (this.$refs.user.hasError || this.$refs.password.hasError) {
        this.formHasError = true
      } else {
        this.$store.dispatch(actions.Core.Users.login, {
          formData: {
            username: this.user,
            password: this.password,
            fingerPrint: ''
          },
          vm: this
        })
      }
    }
  }
}
</script>

<style scoped></style>
