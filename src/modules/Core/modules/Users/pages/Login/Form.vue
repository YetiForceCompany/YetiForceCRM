<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div>
    <form
      @submit.prevent.stop="onSubmit"
      class="col q-gutter-md q-mx-lg"
      :autocomplete="$store.state.Core.Users.loginPageRememberCredentials ? 'on' : 'off'"
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
        v-if="$store.state.Core.Users.languageSelection"
        v-model="language"
        :options="$store.state.Core.Language.langs"
        :label="$t('LBL_CHOOSE_LANGUAGE')"
      >
        <template v-slot:prepend>
          <q-icon name="mdi-translate" />
        </template>
      </q-select>
      <q-select
        v-if="$store.state.Core.Users.layoutSelection"
        v-model="layout"
        :options="$store.state.Env.layouts"
        :label="$t('LBL_SELECT_LAYOUT')"
      >
        <template v-slot:prepend>
          <q-icon name="mdi-looks" />
        </template>
      </q-select>
      <q-btn size="lg" :label="$t('LBL_SIGN_IN')" type="submit" color="secondary" class="full-width q-mt-lg" />
      <router-link
        v-if="$store.state.Core.Users.forgotPassword"
        class="text-secondary float-right"
        :to="{ name: 'Reminder' }"
        >{{ $t('ForgotPassword') }}</router-link
      >
    </form>
  </div>
</template>
<script>
import actions from 'src/store/actions.js'
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
      language: this.$store.state.Core.Language.defaultLanguage,
      layout: ''
    }
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
