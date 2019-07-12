<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div v-if="config.isChatAllowed">
    <q-btn
      round
      color="white"
      text-color="black"
      icon="mdi-message-text-outline"
      class="text-muted"
      @click="dialog = !dialog"
      ref="chatBtn"
    >
      <q-badge v-if="config.showNumberOfNewMessages" v-show="data.amountOfNewMessages > 0" color="danger" floating>
        <transition appear enter-active-class="animated flash" mode="out-in">
          <div :key="data.amountOfNewMessages">
            {{ data.amountOfNewMessages }}
          </div>
        </transition>
      </q-badge>
    </q-btn>
    <q-dialog
      v-model="dialog"
      seamless
      :maximized="!miniMode"
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
    ...mapGetters(['miniMode', 'data', 'config']),
    dialog: {
      get() {
        return this.$store.getters['Chat/dialog']
      },
      set(isOpen) {
        this.setDialog(isOpen)
      }
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
    ...mapActions(['setDialog', 'fetchChatConfig', 'updateAmountOfNewMessages']),
    initTimer() {
      this.timerGlobal = setTimeout(this.trackNewMessages, this.config.refreshTimeGlobal)
    },
    trackNewMessages() {
      AppConnector.request({
        module: 'Chat',
        action: 'ChatAjax',
        mode: 'trackNewMessages'
      }).done(({ result }) => {
        console.log('trackNewMessages')

        this.updateAmountOfNewMessages(result)
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
