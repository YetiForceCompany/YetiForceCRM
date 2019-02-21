<template>
  <q-page class="flex flex-center column">
    <form @submit.prevent.stop="onSubmit" class="q-gutter-md">
      <q-input
              ref="user"
              v-model="user"
              label="User Name"
              lazy-rules
              :rules="[ val => val && val.length > 0 || 'Please type something']"
      />

      <q-input
              ref="password"
              type="password"
              v-model="password"
              label="Password"
              lazy-rules
              :rules="[ val => val && val.length > 0 || 'Please type something']"
      />
      <div>
        <q-btn label="Submit" type="submit" color="primary"/>
      </div>
    </form>
  </q-page>
</template>

<style>
</style>

<script>
  export default {
    name: 'Login',
    data() {
      return {
        user: '',
        password: ''
      }
    },
    methods: {
      onSubmit() {
        this.$refs.user.validate()
        this.$refs.password.validate()

        if (this.$refs.user.hasError || this.$refs.password.hasError) {
          this.formHasError = true
        } else {
          this.$store.commit('login/isLoggedIn', true)
          let user = this.user
          let password = this.password
          this.$router.push('/')
        }
      }
    }
  }
</script>
