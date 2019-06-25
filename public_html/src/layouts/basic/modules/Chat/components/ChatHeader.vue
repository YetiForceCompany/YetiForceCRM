<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-header class="bg-grey-10">
    <q-bar>
      <div class="col-6 text-left">
        <q-btn dense flat round icon="mdi-menu" @click="toggleLeftPanel()" />
        <q-btn round :size="iconSize" flat icon="mdi-keyboard-outline" />

        <q-btn
          round
          :size="iconSize"
          flat
          icon="mdi-history"
          @click="visibleInputSearch(false), showTabHistory(true)"
        />
        <q-btn round :size="iconSize" flat icon="mdi-comment-multiple-outline" />
        <q-btn round :size="iconSize" flat icon="mdi-bell-off-outline" />
        <q-btn round :size="iconSize" flat icon="mdi-volume-high" />
      </div>
      <div class="col-6 text-right">
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
      moduleName: 'Chat',
      dense: false
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
    }
  },
  methods: {
    ...mapActions(['setDialog', 'toggleRightPanel', 'toggleLeftPanel', 'maximize']),
    visibleInputSearch: function(value) {
      this.$emit('visibleInputSearch', value)
    },
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
