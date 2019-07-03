<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-btn
    @click="toggleDesktopNotification()"
    dense
    round
    flat
    :loading="isWaitingForPermission"
    :icon="data.isDesktopNotification ? 'mdi-bell-outline' : 'mdi-bell-off-outline'"
    :color="data.isDesktopNotification ? 'info' : ''"
  />
</template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations } = createNamespacedHelpers('Chat')

export default {
  name: 'NotifyBtn',
  data() {
    return {
      isWaitingForPermission: false
    }
  },
  computed: {
    ...mapGetters(['data']),
    isNotificationPermitted() {
      return PNotify.modules.Desktop.checkPermission() === 0
    }
  },
  methods: {
    ...mapMutations(['setDesktopNotification']),
    toggleDesktopNotification() {
      if (!this.data.isDesktopNotification && !this.isNotificationPermitted) {
        this.isWaitingForPermission = true
        PNotify.modules.Desktop.permission()
        setTimeout(() => {
          if (!this.isNotificationPermitted) {
            Vtiger_Helper_Js.showPnotify({
              text: app.vtranslate('JS_NO_DESKTOP_PERMISSION'),
              type: 'info',
              animation: 'show'
            })
          } else {
            this.setDesktopNotification(!this.data.isDesktopNotification)
            app.setCookie('chat-isDesktopNotification', true, 365)
          }
          this.isWaitingForPermission = false
        }, 3000)
      } else {
        this.setDesktopNotification(!this.data.isDesktopNotification)
        app.setCookie('chat-isDesktopNotification', !this.data.isDesktopNotification, 365)
      }
    }
  },
  created() {
    if (this.data.isDesktopNotification && !this.isNotificationPermitted) {
      this.setDesktopNotification(false)
      app.setCookie('chat-isDesktopNotification', false, 365)
    }
  }
}
</script>
<style lang="sass">
</style>
