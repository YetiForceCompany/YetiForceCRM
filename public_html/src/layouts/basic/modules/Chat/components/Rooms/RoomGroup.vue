<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<RoomList :filterRooms="filterRooms" :roomData="roomData" :roomType="roomType" />
</template>
<script>
import RoomList from './RoomList.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
	name: 'RoomGroup',
	components: { RoomList },
	props: {
		roomData: {
			type: Array,
			required: true,
		},
		roomType: {
			type: String,
			required: true,
		},
		filterRooms: {
			type: String,
			required: true,
		},
	},
}
</script>
<style lang="sass" scoped></style>
