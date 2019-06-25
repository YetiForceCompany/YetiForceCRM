<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-header class="bg-grey-10">
    <q-toolbar>
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
        <q-btn dense round flat icon="mdi-window-minimize" @click="maximizedDialog = false" :disable="!maximizedDialog">
          <q-tooltip content-class="bg-white text-primary">Minimize</q-tooltip>
        </q-btn>
        <q-btn dense round flat icon="mdi-crop-square" @click="maximizedDialog = true" :disable="maximizedDialog">
          <q-tooltip content-class="bg-white text-primary">Maximize</q-tooltip>
        </q-btn>
        <q-btn round flat icon="mdi-close" @click="setDialog(false)">
          <q-tooltip content-class="bg-white text-primary">Close</q-tooltip>
        </q-btn>
        <q-btn dense flat round icon="mdi-menu" @click="toggleRightPanel()" />
      </div>
    </q-toolbar>
  </q-header>
</template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapActions } = createNamespacedHelpers('Chat')
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
    }
  }
}
</script>
<style module lang="stylus"></style>
