<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<div class="flex column q-px-sm q-pt-xs">
		<div class="flex no-wrap justify-between">
			<q-btn dense flat round :color="leftPanel ? 'info' : 'grey'" @click="toggleLeftPanel()">
				<YfIcon icon="yfi-menu-group-room" />
				<q-tooltip>{{ translate('JS_CHAT_ROOMS_MENU') }}</q-tooltip>
			</q-btn>
			<q-btn :color="rightPanel ? 'info' : 'grey'" dense flat round @click="toggleRightPanel()">
				<YfIcon icon="yfi-menu-entrant" />
				<q-tooltip>{{ translate('JS_CHAT_PARTICIPANTS_MENU') }}</q-tooltip>
			</q-btn>
		</div>
		<div class="flex flex-center text-muted">
			<h5>{{ translate('JS_CHAT_ROOM_NO_SELECTED') }}</h5>
		</div>
	</div>
</template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('Chat')
export default {
	name: 'TabChatNoRoom',
	data() {
		return {
			inputSearch: '',
			isSearchActive: false,
			searching: false,
			fetchingEarlier: false,
			scrollbarHidden: false,
			dataReady: false,
			roomId: null,
			roomType: null,
		}
	},
	computed: {
		...mapGetters(['leftPanel', 'rightPanel']),
	},
	methods: {
		...mapActions(['toggleLeftPanel', 'toggleRightPanel']),
	},
}
</script>
<style lang="sass">
.scrollbarHidden
	.q-scrollarea__thumb
		visibility: hidden
</style>
