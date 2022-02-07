<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<q-btn :color="sendByEnter ? 'info' : ''" v-bind="$attrs" @click="toggleEnter()">
		<YfIcon :icon="sendByEnter ? 'yfi-enter-on' : 'yfi-enter-off'" />
		<q-tooltip>{{ translate('JS_CHAT_ENTER') }}</q-tooltip>
	</q-btn>
</template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations } = createNamespacedHelpers('Chat')

export default {
	name: 'ChatButtonEnter',
	computed: {
		...mapGetters(['sendByEnter']),
	},
	methods: {
		...mapMutations(['setSendByEnter']),
		toggleEnter() {
			this.setSendByEnter(!this.sendByEnter)
		},
	},
}
</script>
<style lang="scss" scoped></style>
