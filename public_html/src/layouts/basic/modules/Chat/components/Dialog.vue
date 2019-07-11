<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div v-if="data.isChatAllowed">
    <q-btn
      round
      color="white"
      text-color="black"
      icon="mdi-message-text-outline"
      class="text-muted"
      @click="dialog = !dialog"
      ref="chatBtn"
    >
      <q-badge v-if="data.showNumberOfNewMessages" v-show="amountOfNewMessages > 0" color="danger" floating>
        <transition appear enter-active-class="animated flash" mode="out-in">
          <div :key="amountOfNewMessages">
            {{ this.amountOfNewMessages }}
          </div>
        </transition>
      </q-badge>
    </q-btn>
    <q-dialog
      v-model="dialog"
      seamless
      :maximized="maximizedDialog"
      transition-show="slide-up"
      transition-hide="slide-down"
      content-class="quasar-reset"
    >
      <chat container :parentRefs="$refs" />
    </q-dialog>
  </div>
</template>
<script>
import Chat from './Chat.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('Chat')
export default {
  name: 'Dialog',
  components: { Chat },
  data() {
    return {
      amountOfNewMessages: 0,
      timerGlobal: null
    }
  },
  computed: {
    ...mapGetters(['maximizedDialog', 'data']),
    dialog: {
      get() {
        return this.$store.getters['Chat/dialog']
      },
      set(isOpen) {
        this.setDialog(isOpen)
      }
    },
    desktopPermission() {
      if (this.data.isDesktopNotification) {
        return false
      }
      if (!this.data.isNotificationPermitted) {
        app.setCookie('chat-isDesktopNotification', false, 365)
        return false
      }
      return true
    }
  },
  watch: {
    dialog() {
      if (this.dialog) {
        this.amountOfNewMessages = 0
        clearInterval(this.timerGlobal)
      } else {
        this.trackNewMessages()
      }
    }
  },
  methods: {
    ...mapActions(['setDialog', 'fetchChatConfig']),
    initTimer() {
      this.timerGlobal = setTimeout(this.trackNewMessages, this.data.refreshTimeGlobal)
    },
    trackNewMessages() {
      AppConnector.request({
        module: 'Chat',
        action: 'ChatAjax',
        mode: 'trackNewMessages'
      }).done(({ result }) => {
        if (result > this.amountOfNewMessages) {
          this.amountOfNewMessages = result
          if (app.getCookie('chat-isSoundNotification') === 'true') {
            app.playSound('CHAT')
          }
          if (this.desktopPermission) {
            let message = this.translate('JS_CHAT_NEW_MESSAGE')
            if (this.data.showNumberOfNewMessages) {
              message += ' ' + result
            }
            app.showNotify(
              {
                text: message,
                title: this.translate('JS_CHAT'),
                type: 'success'
              },
              true
            )
          }
        }
        this.initTimer()
      })
    }
  },
  created() {
    this.fetchChatConfig().then(result => {
      if (result.isChatAllowed) this.trackNewMessages()
    })
  }
}
</script>
<style module lang="stylus"></style>
