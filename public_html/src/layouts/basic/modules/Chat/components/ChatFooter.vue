<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<q-footer class="bg-blue-grey-10 text-white">
		<q-bar class="q-bar--fit justify-between">
			<q-breadcrumbs gutter="none">
				<q-breadcrumbs-el class="text-white">
					<YfIcon
						class="q-breadcrumbs__el-icon q-breadcrumbs__el-icon--with-label q-icon"
						:icon="currentTab.icon"
						:size="currentTab.icon.startsWith('yfi') ? '16px' : ''"
					/>
					{{ currentTab.label }}
				</q-breadcrumbs-el>
				<q-breadcrumbs-el v-if="tab !== 'unread'" class="text-white">
					<YfIcon class="q-breadcrumbs__el-icon q-breadcrumbs__el-icon--with-label q-icon" :icon="roomType.icon" size="16px" />
					{{ roomType.label }}
				</q-breadcrumbs-el>
				<q-breadcrumbs-el v-if="isChatTabRoomName" class="text-white text-cyan-9 text-bold u-ellipsis-2-lines" :label="tabChatRoomName" />
				<template #separator>
					<div class="q-breadcrumbs__separator q-mx-sm">/</div>
				</template>
			</q-breadcrumbs>
		</q-bar>
	</q-footer>
</template>
<script>
import { getGroupIcon } from '../utils/utils.js'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters } = createNamespacedHelpers('Chat')
export default {
	name: 'ChatFooter',
	computed: {
		...mapGetters(['currentRoomData', 'tab', 'historyTab']),
		currentTab() {
			switch (this.tab) {
				case 'chat':
					return {
						label: this.translate('JS_CHAT'),
						icon: 'yfi-branding-chat',
					}
				case 'unread':
					return {
						label: this.translate('JS_CHAT_UNREAD'),
						icon: 'yfi-unread-messages',
					}
				case 'history':
					return {
						label: this.translate('JS_CHAT_HISTORY'),
						icon: 'mdi-history',
					}
			}
		},
		roomType() {
			if (this.tab !== 'unread' && this.currentRoomData.roomType !== undefined) {
				const roomType = this.tab === 'chat' ? this.currentRoomData.roomType : this.historyTab
				return {
					label: this.translate(`JS_CHAT_ROOM_${roomType.toUpperCase()}`),
					icon: this.getGroupIcon(roomType),
				}
			} else {
				return { label: '', icon: '' }
			}
		},
		tabChatRoomName() {
			return this.currentRoomData.name
		},
		isChatTabRoomName() {
			return this.tab === 'chat' && this.currentRoomData.name
		},
	},
	methods: {
		getGroupIcon,
	},
}
</script>
<style scoped>
.q-bar--fit {
	height: auto;
	min-height: 32px;
}
</style>
