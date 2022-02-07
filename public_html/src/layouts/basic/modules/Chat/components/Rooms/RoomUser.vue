<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<RoomList :filterRooms="filterRooms" :roomData="roomData" :roomType="roomType" :selectRoom="config.userRoomPin">
		<template #itemAvatar="{ room }">
			<span style="margin-left: -3px">
				<q-avatar v-if="room.image" size="20px">
					<img :src="room.image" />
				</q-avatar>
				<YfIcon v-else class="inline-block" size="20px" icon="mdi-account" />
			</span>
		</template>
	</RoomList>
</template>
<script>
import RoomList from './RoomList.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters } = createNamespacedHelpers('Chat')
export default {
	name: 'RoomUser',
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
	computed: {
		...mapGetters(['config']),
	},
}
</script>
<style lang="sass" scoped></style>
