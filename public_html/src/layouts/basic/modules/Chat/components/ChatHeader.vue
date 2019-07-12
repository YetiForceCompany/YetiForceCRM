<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-header class="bg-grey-10">
    <q-bar>
      <div class="flex items-center no-wrap full-width justify-between">
        <div class="flex no-wrap">
          <q-btn dense flat round icon="mdi-menu" @click="toggleLeftPanel()" />
          <q-btn
            @click="toggleEnter()"
            dense
            round
            flat
            icon="mdi-keyboard-outline"
            :color="config.sendByEnter ? 'info' : ''"
          />
          <notify-btn />
          <q-btn
            @click="toggleSoundNotification()"
            dense
            round
            flat
            :icon="config.isSoundNotification ? 'mdi-volume-high' : 'mdi-volume-off'"
            :color="config.isSoundNotification ? 'info' : ''"
          />
        </div>
        <q-tabs
          @input="toggleRoomTimer"
          v-model="tab"
          class="chat-tabs"
          dense
          shrink
          inline-label
          narrow-indicator
          indicator-color="info"
          active-color="info"
        >
          <q-tab
            name="chat"
            icon="mdi-forum-outline"
            :label="isSmall ? '' : translate('JS_CHAT')"
            :style="{ 'min-width': '40px' }"
          />
          <q-tab name="unread" icon="mdi-email-alert" :label="isSmall ? '' : translate('JS_CHAT_UNREAD')" />
          <q-tab name="history" icon="mdi-history" :label="isSmall ? '' : translate('JS_CHAT_HISTORY_CHAT')" />
        </q-tabs>
        <div class="flex no-wrap">
          <template v-if="$q.platform.is.desktop">
            <q-btn dense flat :icon="miniMode ? 'mdi-window-maximize' : 'mdi-window-restore'" @click="toggleSize()">
              <q-tooltip>{{ miniMode ? translate('JS_MAXIMIZE') : translate('JS_MINIMIZE') }}</q-tooltip>
            </q-btn>
          </template>
          <q-btn dense flat icon="mdi-close" @click="setDialog(false)">
            <q-tooltip>{{ translate('JS_CLOSE') }}</q-tooltip>
          </q-btn>
          <q-btn dense flat round icon="mdi-menu" @click="toggleRightPanel()" />
        </div>
      </div>
    </q-bar>
  </q-header>
</template>
<script>
import NotifyBtn from './NotifyBtn.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapActions, mapMutations, mapGetters } = createNamespacedHelpers('Chat')
export default {
  name: 'ChatHeader',
  components: {
    NotifyBtn
  },
  props: {
    inputSearchVisible: { type: Boolean, required: false },
    tabHistoryShow: { type: Boolean, required: false },
    right: { type: Boolean, required: false },
    left: { type: Boolean, required: false }
  },
  data() {
    return {
      iconSize: '.75rem',
      moduleName: 'Chat',
      timerRoom: false
    }
  },
  computed: {
    ...mapGetters(['config']),
    miniMode: {
      get() {
        return this.$store.getters['Chat/miniMode']
      },
      set(isMini) {
        this.maximize(isMini)
      }
    },
    tab: {
      get() {
        return this.$store.getters['Chat/tab']
      },
      set(tab) {
        this.$store.commit('Chat/setTab', tab)
      }
    },
    isSmall() {
      return this.miniMode || !this.$q.platform.is.desktop
    }
  },
  methods: {
    ...mapActions(['setDialog', 'toggleRightPanel', 'toggleLeftPanel', 'toggleHistoryTab', 'maximize']),
    ...mapMutations(['setLeftPanel', 'setRightPanel', 'setSendByEnter', 'setSoundNotification', 'updateRooms']),
    showTabHistory: function(value) {
      this.$emit('showTabHistory', value)
    },
    toggleRoomTimer(tabName) {
      if (tabName === 'chat' && this.timerRoom) {
        clearTimeout(this.timerRoom)
        this.timerRoom = false
      } else if (!this.timerRoom) {
        this.initTimer()
      }
    },
    initTimer() {
      this.timerRoom = setTimeout(() => {
        AppConnector.request({
          module: 'Chat',
          action: 'ChatAjax',
          mode: 'getRooms'
        }).done(({ result }) => {
          this.updateRooms(result.roomList)
          this.initTimer()
        })
      }, this.config.refreshRoomTime)
    },
    rightPanel(value) {
      this.$emit('rightPanel', value)
    },
    leftPanel(value) {
      this.$emit('leftPanel', value)
    },
    toggleSize() {
      if (!this.miniMode) {
        this.miniMode = true
        this.setLeftPanel(false)
        this.setRightPanel(false)
      } else {
        this.miniMode = false
      }
    },
    toggleEnter() {
      app.setCookie('chat-notSendByEnter', !this.config.sendByEnter, 365)
      this.setSendByEnter(!this.config.sendByEnter)
    },
    toggleSoundNotification() {
      app.setCookie('chat-isSoundNotification', !this.config.isSoundNotification, 365)
      this.setSoundNotification(!this.config.isSoundNotification)
    }
  },
  beforeDestroy() {
    clearTimeout(this.timerRoom)
  }
}
</script>
<style lang="sass">
.chat-tabs
	.q-tab__content
		min-width: 40px
</style>
