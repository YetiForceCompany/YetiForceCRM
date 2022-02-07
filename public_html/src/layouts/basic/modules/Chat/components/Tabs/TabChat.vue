<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<ChatContent ref="chat" :roomData="roomData || {}" :isVisible="isSetCurrentRoom" @onContentLoaded="$emit('onContentLoaded', true)">
		<template #searchPrepend>
			<q-btn dense flat round :color="leftPanel ? 'info' : 'grey'" @click="toggleLeftPanel()">
				<YfIcon icon="yfi-menu-group-room" />
				<q-tooltip>{{ translate('JS_CHAT_ROOMS_MENU') }}</q-tooltip>
			</q-btn>
		</template>
		<template #searchAppend>
			<q-btn :color="rightPanel ? 'info' : 'grey'" dense flat round @click="toggleRightPanel()">
				<YfIcon icon="yfi-menu-entrant" />
				<q-tooltip>{{ translate('JS_CHAT_PARTICIPANTS_MENU') }}</q-tooltip>
			</q-btn>
		</template>
		<template #noRoom>
			<TabChatNoRoom />
		</template>
	</ChatContent>
</template>
<script>
import ChatContent from '../ChatContent.vue'
import TabChatNoRoom from './TabChatNoRoom.vue'

import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions, mapMutations } = createNamespacedHelpers('Chat')
export default {
	name: 'TabChat',
	components: { ChatContent, TabChatNoRoom },
	props: {
		roomData: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
			roomId: null,
			roomType: null,
			dataReady: false,
		}
	},
	computed: {
		...mapGetters(['data', 'currentRoomData', 'dialog', 'leftPanel', 'rightPanel']),
		isSetCurrentRoom() {
			return !!Object.keys(this.currentRoomData).length
		},
	},
	watch: {
		roomData() {
			if (this.isSetCurrentRoom && this.dataReady && (this.roomData.recordid !== this.roomId || this.roomData.roomType !== this.roomType)) {
				this.disableNewMessagesListener()
				this.updateComponentsRoom()
				this.enableNewMessagesListener()
			}
		},
		dialog() {
			if (this.dialog) {
				this.onShowTabChatEvent()
			} else {
				this.disableNewMessagesListener()
			}
		},
	},
	mounted() {
		if (this.dialog && this.isSetCurrentRoom) {
			this.onShowTabChatEvent()
		}
		this.updateComponentsRoom()
		this.dataReady = true
	},
	beforeDestroy() {
		this.disableNewMessagesListener()
	},
	methods: {
		...mapActions(['fetchRoom', 'addActiveRoom', 'toggleLeftPanel', 'toggleRightPanel', 'removeActiveRoom']),
		registerPostLoadEvents() {
			this.$refs.chat.registerMountedEvents()
			this.enableNewMessagesListener()
		},
		updateComponentsRoom() {
			this.roomId = this.roomData.recordid
			this.roomType = this.roomData.roomType
		},
		enableNewMessagesListener() {
			this.addActiveRoom({ recordId: this.roomId, roomType: this.roomType })
		},
		onShowTabChatEvent() {
			if (this.currentRoomData.recordid) {
				this.fetchRoom({
					id: this.roomData.recordid,
					roomType: this.roomData.roomType,
				}).then((result) => {
					if (result) {
						this.registerPostLoadEvents()
					} else {
						this.$emit('onContentLoaded', true)
					}
				})
			} else {
				this.registerPostLoadEvents()
			}
		},
		disableNewMessagesListener() {
			if (this.data.roomList[this.roomType] && this.data.roomList[this.roomType][this.roomId]) {
				this.removeActiveRoom({
					recordId: this.roomId,
					roomType: this.roomType,
				})
			}
		},
	},
}
</script>
<style lang="scss"></style>
