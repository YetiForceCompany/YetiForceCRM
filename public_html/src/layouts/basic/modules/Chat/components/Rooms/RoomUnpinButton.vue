<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<q-btn dense round flat size="xs" @click.stop="unpinRoom({ roomType, recordId })" icon="mdi-pin-off">
		<q-tooltip>{{ translate('JS_CHAT_UNPIN') }}</q-tooltip>
	</q-btn>
</template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapActions } = createNamespacedHelpers('Chat')
export default {
	name: 'RoomUnpinButton',
	props: {
		roomType: {
			type: String,
			required: true,
		},
		recordId: {
			type: Number,
			required: true,
		},
	},
	methods: {
		...mapActions(['unpinRoom']),
	},
}
</script>
<style lang="sass" scoped></style>
