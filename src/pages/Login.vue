<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <!-- <q-layout>
    <div class="row">
      <div class="col-xs-12 col-sm-6 col-md-4">
        <q-card class="fixed-center">
          <q-card-main></q-card-main>
        </q-card>
      </div>
    </div>
  </q-layout>-->
  <q-layout>
    <q-page-container>
      <q-page class="row">
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 fixed-center">
          <div class="card-shadow q-pa-xl column">
            <div class="col-auto self-center q-pb-lg">
              <img class src="statics/Logo/logo" width="100" />
            </div>
            <div>
              <form @submit.prevent.stop="onSubmit" class="col q-gutter-md q-mx-lg">
                <q-input
                  ref="user"
                  v-model="user"
                  label="User Name"
                  lazy-rules
                  :rules="[val => (val && val.length > 0) || 'Please type something']"
                />
                <q-input
                  ref="password"
                  type="password"
                  v-model="password"
                  label="Password"
                  lazy-rules
                  :rules="[val => (val && val.length > 0) || 'Please type something']"
                />
                <q-select v-model="language" :options="languages" label="Language">
                  <template v-slot:prepend>
                    <q-icon name="translate" />
                  </template>
                </q-select>
                <q-btn label="Submit" type="submit" color="secondary" class="full-width q-mt-lg" />
              </form>
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
export default {
  name: 'Login',
  data() {
    return {
      user: '',
      password: '',
      languages: ['polish', 'english', 'german'],
      language: ''
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
