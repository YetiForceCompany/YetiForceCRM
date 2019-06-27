<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="q-px-sm" ref="textContainer">
    <picker
      v-show="emojiPanel"
      @select="addEmoji"
      native
      :title="translate('JS_CHAT_PICK_EMOJI')"
      emoji="point_up"
      :i18n="emojiTranslations"
    />
    <q-separator />
    <q-input
      borderless
      v-model="text"
      type="textarea"
      autogrow
      :placeholder="translate('JS_CHAT_MESSAGE')"
      class="overflow-hidden"
    >
      <template v-slot:append>
        <q-btn :loading="sending" round color="secondary" icon="mdi-send" @click="simulateSubmit" />
      </template>
    </q-input>
  </div>
</template>
<script>
import Emoji from 'emoji-mart-vue'

import { createNamespacedHelpers } from 'vuex'
const Picker = Emoji.Picker
const { mapGetters, mapActions } = createNamespacedHelpers('Chat')
console.log(Emoji)
export default {
  name: 'ChatMessages',
  components: { Picker },
  data() {
    return {
      emojiPanel: false,
      text: '',
      sending: false,
      emojiTranslations: {
        search: this.translate('JS_CHAT_SEARCH_EMOJI'),
        notfound: 'No Emoji Found',
        categories: {
          search: 'Search Results',
          recent: 'Frequently Used',
          people: 'Smileys & People',
          nature: 'Animals & Nature',
          foods: 'Food & Drink',
          activity: 'Activity',
          places: 'Travel & Places',
          objects: 'Objects',
          symbols: 'Symbols',
          flags: 'Flags',
          custom: 'Custom'
        }
      }
    }
  },
  computed: {
    ...mapGetters(['maximizedDialog', 'historyTab', 'data'])
  },
  methods: {
    ...mapActions(['sendMessage']),
    simulateSubmit() {
      if (this.text.length < this.data.maxLengthMessage) {
        // clearTimeout(this.timerMessage)
        this.sendMessage(this.text)
        this.text = ''
      } else {
        Vtiger_Helper_Js.showPnotify({
          text: app.vtranslate('JS_MESSAGE_TOO_LONG'),
          type: 'error',
          animation: 'show'
        })
      }
    },
    addEmoji(val) {
      console.log(val)
    }
  }
}
</script>
<style module lang="stylus"></style>
