<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-header class="bg-grey-10">
    <q-bar>
      <div class="flex items-center no-wrap full-width justify-between">
        <div class="">
          <q-btn dense flat round icon="mdi-menu" @click="toggleLeftPanel()" />
          <q-btn dense round flat icon="mdi-keyboard-outline" />

          <q-btn dense round flat icon="mdi-history" @click="toggleHistoryTab()" />
          <q-btn dense round flat icon="mdi-comment-multiple-outline" />
          <q-btn dense round flat icon="mdi-bell-off-outline" />
          <q-btn dense round flat icon="mdi-volume-high" />
        </div>
        <q-tabs v-model="tab" dense active-color="info" inline-label indicator-color="info">
          <q-tab name="chat" icon="mdi-forum-outline" :label="translate('JS_CHAT')" />
          <q-tab name="unread" icon="mdi-email-alert" :label="translate('JS_CHAT_UNREAD')" />
          <q-tab name="history" icon="mdi-history" :label="translate('JS_CHAT_HISTORY_CHAT')" />
        </q-tabs>
        <div>
          <template v-if="$q.platform.is.desktop">
            <q-btn
              dense
              flat
              :icon="maximizedDialog ? 'mdi-window-restore' : 'mdi-window-maximize'"
              @click="toggleSize()"
            >
              <q-tooltip>{{ maximizedDialog ? translate('JS_KB_MINIMIZE') : translate('JS_KB_MAXIMIZE') }}</q-tooltip>
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
const { mapActions, mapMutations } = createNamespacedHelpers('Chat')
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
    ...mapMutations(['setLeftPanel', 'setRightPanel'])
  }
}
</script>
<style module lang="stylus"></style>
