<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<q-layout :class="['bg-white', miniMode ? 'chat-mini' : '']" view="hHh LpR fFf" container>
		<ChatHeader @visibleInputSearch="inputSearchVisible = $event" @showTabHistory="tabHistoryShow = $event" />
		<ChatPanelLeft>
			<template #top>
				<YfBackdrop v-show="tab !== 'chat'" />
			</template>
		</ChatPanelLeft>
		<q-drawer
			v-model="computedModel"
			:class="{ 'backdrop-fix': mobileMode && !computedModel }"
			:breakpoint="layout.drawer.breakpoint"
			:show-if-above="false"
			no-swipe-close
			no-swipe-open
			bordered
			side="right"
		>
			<ChatPanelRight :participants="currentRoomData.participants || []">
				<template #top>
					<YfBackdrop v-show="tab !== 'chat'" />
				</template>
			</ChatPanelRight>
		</q-drawer>
		<ChatPanelMain />
		<ChatFooter />
	</q-layout>
</template>
<script>
import ChatPanelLeft from './ChatPanelLeft.vue'
import ChatPanelRight from './ChatPanelRight.vue'
import ChatPanelMain from './ChatPanelMain.vue'
import ChatHeader from './ChatHeader.vue'
import ChatFooter from './ChatFooter.vue'
import YfBackdrop from 'components/YfBackdrop.vue'

import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations } = createNamespacedHelpers('Chat')
export default {
	name: 'ChatContainer',
	components: { ChatPanelLeft, ChatPanelRight, ChatPanelMain, ChatHeader, ChatFooter, YfBackdrop },
	props: {
		parentRefs: { type: Object, required: true },
	},
	computed: {
		...mapGetters(['data', 'miniMode', 'mobileMode', 'tab', 'currentRoomData', 'layout', 'rightPanel', 'rightPanelMobile']),
		computedModel: {
			get() {
				return this.mobileMode ? this.rightPanelMobile : this.rightPanel
			},
			set(isOpen) {
				if (this.mobileMode) {
					this.setRightPanelMobile(isOpen)
				}
			},
		},
	},
	methods: {
		...mapMutations(['setRightPanelMobile']),
	},
}
</script>
<style></style>
