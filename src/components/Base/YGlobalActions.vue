<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div>
    <q-btn round :size="iconSize" flat icon="mdi-plus" />
    <q-btn round :size="iconSize" flat icon="mdi-email-outline" />
    <q-btn round :size="iconSize" flat icon="mdi-bell-ring-outline" />
    <q-btn round :size="iconSize" flat icon="mdi-calendar-multiselect" />
    <q-btn round :size="iconSize" flat icon="mdi-history" />
    <q-btn round :size="iconSize" flat icon="mdi-settings-outline" />
    <q-btn round :size="iconSize" flat icon="mdi-power-standby" @click="logout" />
  </div>
</template>
<script>
import { mapActions } from 'vuex'
import actions from 'store/actions.js'
export default {
  name: 'YGlobalActions',
  data() {
    return {
      iconSize: '.75rem'
    }
  },
  methods: {
    ...mapActions({
      logout: actions.App.Core.Users.logout
    })
  }
}
</script>

<style module lang="stylus"></style>
