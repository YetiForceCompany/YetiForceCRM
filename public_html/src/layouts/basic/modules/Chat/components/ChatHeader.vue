<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-header class="bg-grey-10">
    <q-bar>
      <div class="flex items-center no-wrap full-width justify-between">
        <div class="flex no-wrap">
          <q-btn dense flat round icon="mdi-menu" @click="toggleLeftPanel()" />
          <q-btn
            dense
            round
            flat
            icon="mdi-keyboard-outline"
            @click="toggleEnter()"
            :color="data.sendByEnter ? 'info' : ''"
          />
          <q-btn dense round flat icon="mdi-bell-off-outline" />
          <q-btn dense round flat icon="mdi-volume-high" />
        </div>
        <q-tabs
          class="chat-tabs"
          v-model="tab"
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
            <q-btn
              dense
              flat
              :icon="maximizedDialog ? 'mdi-window-restore' : 'mdi-window-maximize'"
              @click="toggleSize()"
            >
              <q-tooltip>{{ maximizedDialog ? translate('JS_MINIMIZE') : translate('JS_MAXIMIZE') }}</q-tooltip>
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
import { createNamespacedHelpers } from 'vuex'
const { mapActions, mapMutations, mapGetters } = createNamespacedHelpers('Chat')
export default {
  name: 'ChatHeader',
  props: {
    inputSearchVisible: { type: Boolean, required: false },
    tabHistoryShow: { type: Boolean, required: false },
    right: { type: Boolean, required: false },
    left: { type: Boolean, required: false }
  },
  data() {
    return {
      iconSize: '.75rem',
      moduleName: 'Chat'
    }
  },
  computed: {
    ...mapGetters(['data']),
    maximizedDialog: {
      get() {
        return this.$store.getters['Chat/maximizedDialog']
      },
      set(isMax) {
        this.maximize(isMax)
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
      return !this.maximizedDialog || !this.$q.platform.is.desktop
    }
  },
  methods: {
    ...mapActions(['setDialog', 'toggleRightPanel', 'toggleLeftPanel', 'toggleHistoryTab', 'maximize']),
    showTabHistory: function(value) {
      this.$emit('showTabHistory', value)
    },
    rightPanel: function(value) {
      this.$emit('rightPanel', value)
    },
    leftPanel: function(value) {
      this.$emit('leftPanel', value)
    },
    toggleSize() {
      if (this.maximizedDialog) {
        this.maximizedDialog = false
        this.setLeftPanel(false)
        this.setRightPanel(false)
      } else {
        this.maximizedDialog = true
      }
    },
    toggleEnter() {
      this.setSendByEnter(!this.data.sendByEnter)
      app.setCookie('chat-notSendByEnter', !this.data.sendByEnter, 365)
    },
    ...mapMutations(['setLeftPanel', 'setRightPanel', 'setSendByEnter'])
  }
}
</script>
<style lang="sass">
.chat-tabs
	.q-tab__content
		min-width: 40px
</style>
