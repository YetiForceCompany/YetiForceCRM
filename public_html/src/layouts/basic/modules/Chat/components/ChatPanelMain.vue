<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<q-page-container>
		<q-page>
			<q-tab-panels v-model="tab" class="chat-panels" style="min-height: inherit" animated>
				<q-tab-panel name="chat" style="min-height: inherit">
					<TabChat :roomData="currentRoomData" @onContentLoaded="isLoading = false" />
				</q-tab-panel>
				<q-tab-panel name="unread">
					<TabUnread class="q-pa-md" @onContentLoaded="isLoading = false" />
				</q-tab-panel>
				<q-tab-panel name="history">
					<TabHistory @onContentLoaded="isLoading = false" />
				</q-tab-panel>
			</q-tab-panels>
			<q-inner-loading :showing="isLoading">
				<q-spinner-cube color="primary" size="50px" />
			</q-inner-loading>
		</q-page>
	</q-page-container>
</template>
<script>
import TabChat from './Tabs/TabChat.vue'
import TabUnread from './Tabs/TabUnread.vue'
import TabHistory from './Tabs/TabHistory.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('Chat')

export default {
	name: 'ChatPanelMain',
	components: { TabUnread, TabHistory, TabChat },
	data() {
		return {
			isLoading: true,
		}
	},
	computed: {
		...mapGetters(['tab', 'currentRoomData']),
	},
	watch: {
		tab() {
			this.isLoading = true
		},
	},
}
</script>
<style lang="sass">
.chat-panels.q-tab-panels.q-panel-parent
	.q-panel.scroll
		min-height: inherit
		overflow: hidden
</style>
