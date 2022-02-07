<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<q-header class="bg-grey-10">
		<q-bar>
			<div class="flex items-center no-wrap full-width justify-between js-drag">
				<div class="flex no-wrap">
					<ChatButtonNotify />
					<q-btn
						:icon="isSoundNotification ? 'mdi-volume-high' : 'mdi-volume-off'"
						:color="isSoundNotification ? 'info' : ''"
						dense
						round
						flat
						@click="toggleSoundNotification()"
					>
						<q-tooltip>{{ translate(isSoundNotification ? 'JS_CHAT_SOUND_ON' : 'JS_CHAT_SOUND_OFF') }}</q-tooltip>
					</q-btn>
				</div>
				<q-tabs v-model="tab" class="chat-tabs" indicator-color="info" active-color="info" dense shrink inline-label narrow-indicator>
					<q-tab :style="{ 'min-width': '40px' }" name="chat">
						<YfIcon class="q-icon q-tab__icon" size="20px" icon="yfi-branding-chat" />
						<span class="q-tab__label">{{ isSmall ? '' : translate('JS_CHAT') }}</span>
						<q-tooltip>{{ translate('JS_CHAT_DESC') }}</q-tooltip>
					</q-tab>
					<q-tab name="unread">
						<YfIcon class="q-icon q-tab__icon" size="20px" icon="yfi-unread-messages" />
						<span class="q-tab__label">{{ isSmall ? '' : translate('JS_CHAT_UNREAD') }}</span>
						<q-tooltip>{{ translate('JS_CHAT_UNREAD_DESC') }}</q-tooltip>
					</q-tab>
					<q-tab name="history" :label="isSmall ? '' : translate('JS_CHAT_HISTORY')" icon="mdi-history">
						<q-tooltip>{{ translate('JS_CHAT_HISTORY_DESC') }}</q-tooltip>
					</q-tab>
				</q-tabs>
				<div class="flex no-wrap">
					<template v-if="$q.platform.is.desktop">
						<ButtonGrab v-show="miniMode" class="text-white flex flex-center" linkClass="" grabClass="js-drag" size="19px" />
						<q-btn :icon="miniMode ? 'mdi-window-maximize' : 'mdi-window-restore'" dense flat round @click="toggleSize()">
							<q-tooltip>{{ miniMode ? translate('JS_MAXIMIZE') : translate('JS_MINIMIZE') }}</q-tooltip>
						</q-btn>
					</template>
					<q-btn dense flat round icon="mdi-close" @click="setDialog(false)">
						<q-tooltip>{{ translate('JS_CLOSE') }}</q-tooltip>
					</q-btn>
				</div>
			</div>
		</q-bar>
	</q-header>
</template>
<script>
import ChatButtonNotify from './ChatButtonNotify.vue'
import ButtonGrab from 'components/ButtonGrab.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapActions, mapMutations, mapGetters } = createNamespacedHelpers('Chat')

export default {
	name: 'ChatHeader',
	components: { ChatButtonNotify, ButtonGrab },
	props: {
		inputSearchVisible: { type: Boolean, required: false },
		tabHistoryShow: { type: Boolean, required: false },
		right: { type: Boolean, required: false },
		left: { type: Boolean, required: false },
	},
	data() {
		return {
			moduleName: 'Chat',
			timerRoom: false,
		}
	},
	computed: {
		...mapGetters(['config', 'isSoundNotification', 'leftPanel', 'rightPanel']),
		miniMode: {
			get() {
				return this.$store.getters['Chat/miniMode']
			},
			set(isMini) {
				this.maximize(isMini)
			},
		},
		tab: {
			get() {
				return this.$store.getters['Chat/tab']
			},
			set(tab) {
				this.$store.commit('Chat/setTab', tab)
			},
		},
		isSmall() {
			return this.miniMode || !this.$q.platform.is.desktop
		},
	},
	methods: {
		...mapActions(['maximize']),
		...mapMutations(['setDialog', 'setSoundNotification']),
		showTabHistory: function (value) {
			this.$emit('showTabHistory', value)
		},
		toggleSize() {
			let classList = this.$parent.$el.parentElement.parentElement.classList
			this.miniMode ? classList.add('fit') : classList.remove('fit')
			this.miniMode = !this.miniMode
		},
		toggleSoundNotification() {
			this.setSoundNotification(!this.isSoundNotification)
		},
	},
	beforeDestroy() {
		clearTimeout(this.timerRoom)
	},
}
</script>
<style lang="sass">
.chat-tabs
	.q-tab__content
		min-width: 40px
</style>
