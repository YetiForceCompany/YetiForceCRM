<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <hook-wrapper class="Core-Component-YfGlobalActions">
    <div>
      <q-btn round :size="iconSize" flat icon="mdi-plus" @click="setDialog(true)" />
      <q-btn round :size="iconSize" flat icon="mdi-email-outline" />
      <q-btn round :size="iconSize" flat icon="mdi-bell-ring-outline" />
      <q-btn round :size="iconSize" flat icon="mdi-calendar-multiselect" />
      <chat-modal></chat-modal>
      <q-btn round :size="iconSize" flat icon="mdi-history" />
      <q-btn round :size="iconSize" flat icon="mdi-settings-outline" />
      <q-btn round :size="iconSize" flat icon="mdi-power-standby" @click="logout" />
    </div>
  </hook-wrapper>
</template>
<script>
import actions from 'store/actions.js'
import ChatModal from '/Base/modules/Chat/components/Modal.vue.js'
export default {
  name: 'YfGlobalActions',
  components: {
    ChatModal
  },
  data() {
    return {
      iconSize: '.75rem'
    }
  },
  methods: {
    ...Vuex.mapActions({
      logout: actions.Core.Users.logout
    })
  }
}
</script>

<style module lang="stylus"></style>
