<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<div class="q-px-sm" ref="textContainer">
		<picker
			v-if="emojiPanel"
			:style="{ position: 'absolute', bottom: containerHeight }"
			:title="translate('JS_CHAT_PICK_EMOJI')"
			:data="emojiIndex"
			:i18n="emojiTranslations"
			emoji="point_up"
			native
			@select="addEmoji"
		/>
		<div class="flex no-wrap justify-between">
			<div class="c-completions flex items-center q-gutter-x-sm js-completions__actions">
				<q-icon
					class="cursor-pointer js-emoji-trigger"
					:name="emojiPanel ? 'mdi-emoticon-happy' : 'mdi-emoticon-happy-outline'"
					size="18px"
					@click="emojiPanel = !emojiPanel"
				/>
				<span class="c-completions__item js-completions__users fas yfi-hash-user">
					<q-tooltip>{{ translate('JS_CHAT_TAG_USER') }}</q-tooltip>
				</span>
				<span class="c-completions__item js-completions__records fas fa-hashtag">
					<q-tooltip>{{ translate('JS_CHAT_TAG_RECORD') }}</q-tooltip>
				</span>
			</div>
			<ChatButtonEnter dense flat />
		</div>
		<q-separator class="q-mb-xs" />
		<div class="d-flex flex-nowrap">
			<div class="full-width">
				<div
					ref="input"
					class="u-fs-13px js-completions full-height u-outline-none"
					contenteditable="true"
					data-completions-buttons="true"
					:placeholder="translate('JS_CHAT_MESSAGE')"
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
import ChatButtonEnter from '../ChatButtonEnter.vue'
import Emoji from '~/../libraries/emoji-mart-vue-fast/dist/emoji-mart'
import data from '~/../libraries/emoji-mart-vue-fast/data/all'
import { createNamespacedHelpers } from 'vuex'
const Picker = Emoji.Picker
const EmojiIndex = Emoji.EmojiIndex
const { mapGetters, mapActions } = createNamespacedHelpers('Chat')
let emojiIndex = new EmojiIndex(data)
export default {
	name: 'TabChatInput',
	components: { Picker, ChatButtonEnter },
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
		...mapGetters(['config', 'sendByEnter', 'currentRoomData']),
		containerHeight() {
			if (this.$refs.textContainer !== undefined) return this.$refs.textContainer.clientHeight + 'px'
		}
	},
	watch: {
		currentRoomData() {
			this.focusInput()
		}
	},
	mounted() {
		this.$nextTick(() => {
			new App.Fields.Text.Completions(this.$refs.input)
			this.registerEmojiPanelClickOutside()
		})
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
				app.showNotify({
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
		focusInput() {
			this.$refs.input.focus()
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
	}
}
</script>
<style>
.emoji-mart-category-label h3 {
	font-size: 16px !important;
	font-weight: 500 !important;
}
.emoji-mart,
.emoji-mart * {
	box-sizing: border-box !important;
	line-height: 1.15 !important;
}
</style>
