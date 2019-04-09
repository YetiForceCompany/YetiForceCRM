<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-header class="bg-grey-10">
    <q-toolbar>
      <div class="col-6 text-left">
        <q-btn dense flat round icon="mdi-menu" @click="leftPanel(!left)" />
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
        <q-btn dense round flat icon="mdi-window-minimize" @click="maximized(false)" :disable="!maximizedToggle">
          <q-tooltip v-if="maximizedToggle" content-class="bg-white text-primary">Minimize</q-tooltip>
        </q-btn>
        <q-btn dense round flat icon="mdi-crop-square" @click="maximized(true)" :disable="maximizedToggle">
          <q-tooltip v-if="!maximizedToggle" content-class="bg-white text-primary">Maximize</q-tooltip>
        </q-btn>
        <q-btn round flat icon="mdi-close" @click="setDialog(false)">
          <q-tooltip content-class="bg-white text-primary">Close</q-tooltip>
        </q-btn>
        <q-btn dense flat round icon="mdi-menu" @click="rightPanel(!right)" />
      </div>
    </q-toolbar>
  </q-header>
</template>
<script>
import actions from '/store/actions.js'

export default {
  name: 'ChatHeader',
  props: {
    maximizedToggle: { type: Boolean, required: false },
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
  methods: {
    ...Vuex.mapActions({
      setDialog: actions.Base.Chat.setDialog
    }),
    maximized: function(value) {
      this.$emit('maximized', value)
    },
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
