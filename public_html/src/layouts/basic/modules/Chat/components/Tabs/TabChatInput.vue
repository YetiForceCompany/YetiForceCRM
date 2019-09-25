<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="q-px-sm" ref="textContainer">
    <picker
      v-if="emojiPanel"
      @select="addEmoji"
      native
      :title="translate('JS_CHAT_PICK_EMOJI')"
      emoji="point_up"
      :data="emojiIndex"
      :i18n="emojiTranslations"
      :style="{ position: 'absolute', bottom: containerHeight }"
    />
    <div class="c-completions flex items-center q-gutter-x-sm js-completions__actions">
      <q-icon
        :name="emojiPanel ? 'mdi-emoticon-happy' : 'mdi-emoticon-happy-outline'"
        size="18px"
        class="cursor-pointer js-emoji-trigger"
        @click="emojiPanel = !emojiPanel"
      />
      <span class="c-completions__item js-completions__users fas yfi-hash-user">
        <q-tooltip>{{ translate('JS_CHAT_TAG_USER') }}</q-tooltip>
      </span>
      <span class="c-completions__item js-completions__records fas fa-hashtag">
        <q-tooltip>{{ translate('JS_CHAT_TAG_RECORD') }}</q-tooltip>
      </span>
    </div>
    <q-separator class="q-my-xs" />
    <div class="d-flex flex-nowrap">
      <div class="full-width">
        <div
          class="u-font-size-13px js-completions full-height u-outline-none"
          contenteditable="true"
          data-completions-buttons="true"
          :placeholder="translate('JS_CHAT_MESSAGE')"
          ref="input"
          @keydown.enter="onEnter"
        ></div>
      </div>
      <q-btn :loading="sending" flat round color="primary" icon="mdi-send" @click="send">
        <template #loading>
          <q-spinner-facebook />
        </template>
      </q-btn>
    </div>
  </div>
</template>
<script>
import Emoji from '~/../libraries/emoji-mart-vue-fast/dist/emoji-mart'
import data from '~/../libraries/emoji-mart-vue-fast/data/all'
import { createNamespacedHelpers } from 'vuex'
const Picker = Emoji.Picker
const EmojiIndex = Emoji.EmojiIndex
const { mapGetters, mapActions } = createNamespacedHelpers('Chat')
let emojiIndex = new EmojiIndex(data)
export default {
  name: 'TabChatInput',
  components: { Picker },
  props: {
    roomData: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      sending: false,
      emojiPanel: false,
      emojiTranslations: {
        search: this.translate('JS_EMOJI_SEARCH'),
        notfound: this.translate('JS_EMOJI_NOTFOUND'),
        categories: {
          search: this.translate('JS_EMOJI_SEARCHRESULT'),
          recent: this.translate('JS_EMOJI_RECENT'),
          people: this.translate('JS_EMOJI_PEOPLE'),
          nature: this.translate('JS_EMOJI_NATURE'),
          foods: this.translate('JS_EMOJI_FOODS'),
          activity: this.translate('JS_EMOJI_ACTIVITY'),
          places: this.translate('JS_EMOJI_PLACES'),
          objects: this.translate('JS_EMOJI_OBJECTS'),
          symbols: this.translate('JS_EMOJI_SYMBOLS'),
          flags: this.translate('JS_EMOJI_FLAGS'),
          custom: this.translate('JS_EMOJI_CUSTOM')
        }
      },
      emojiIndex: emojiIndex
    }
  },
  computed: {
    ...mapGetters(['config', 'sendByEnter']),
    containerHeight() {
      if (this.$refs.textContainer !== undefined) return this.$refs.textContainer.clientHeight + 'px'
    }
  },
  methods: {
    ...mapActions(['sendMessage']),
    send(e) {
      e.preventDefault()
      if (this.sending || !this.$refs.input.innerText.length) return
      if (this.$refs.input.innerText.length < this.config.maxLengthMessage) {
        this.sending = true
        this.sendMessage({
          text: this.$refs.input.innerHTML,
          roomType: this.roomData.roomType,
          recordId: this.roomData.recordid
        }).then(e => {
          this.$refs.input.innerText = ''
          this.sending = false
          this.$emit('onSended')
        })
      } else {
        Vtiger_Helper_Js.showPnotify({
          text: app.vtranslate('JS_MESSAGE_TOO_LONG'),
          type: 'error',
          animation: 'show'
        })
      }
    },
    addEmoji(emoji) {
      this.$refs.input.insertAdjacentHTML('beforeend', emoji.native)
    },
    onEnter(e) {
      if (this.sendByEnter && !e.shiftKey) {
        e.preventDefault()
        this.send(e)
      }
    },
    registerEmojiPanelClickOutside() {
      document.addEventListener('click', e => {
        try {
          if (
            this.emojiPanel &&
            !e.target.parentNode.className.split(' ').some(c => /emoji-mart.*/.test(c)) &&
            !e.target.className.split(' ').some(c => /emoji-mart.*/.test(c)) &&
            !e.target.classList.contains('js-emoji-trigger')
          ) {
            this.emojiPanel = false
          }
        } catch (error) {}
      })
    }
  },
  mounted() {
    this.$nextTick(() => {
      new App.Fields.Text.Completions(this.$refs.input, { emojiPanel: false })
      this.registerEmojiPanelClickOutside()
    })
  }
}
</script>
<style>
</style>
