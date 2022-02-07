<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<q-drawer
		v-model="computedModel"
		:class="{ 'backdrop-fix': mobileMode && !computedModel }"
		:breakpoint="layout.drawer.breakpoint"
		:show-if-above="false"
		no-swipe-close
		no-swipe-open
		bordered
		side="left"
	>
		<div class="fit bg-grey-11">
			<slot name="top"></slot>
			<div class="bg-grey-11">
				<q-input v-model="filterRooms" class="q-px-sm" :placeholder="translate('JS_CHAT_FILTER_ROOMS')" dense>
					<template #prepend>
						<q-icon name="mdi-magnify" />
					</template>
					<template #append>
						<q-icon v-show="filterRooms.length > 0" class="cursor-pointer" name="mdi-close" @click="filterRooms = ''" />
					</template>
				</q-input>
				<div v-for="(roomGroup, roomType) of roomList" :key="roomType" :style="{ fontSize: layout.drawer.fs }">
					<component :is="roomComponent(roomType)" :roomType="roomType" :roomData="roomList[roomType]" :filterRooms="filterRooms" />
				</div>
			</div>
		</div>
	</q-drawer>
</template>
<script>
import RoomPrivate from './Rooms/RoomPrivate.vue'
import RoomGroup from './Rooms/RoomGroup.vue'
import RoomGlobal from './Rooms/RoomGlobal.vue'
import RoomCrm from './Rooms/RoomCrm.vue'
import RoomUser from './Rooms/RoomUser.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations } = createNamespacedHelpers('Chat')
export default {
	name: 'ChatPanelLeft',
	components: { RoomPrivate, RoomGroup, RoomGlobal, RoomCrm, RoomUser },
	data() {
		return {
			filterRooms: '',
			roomsMap: {
				private: 'RoomPrivate',
				group: 'RoomGroup',
				global: 'RoomGlobal',
				crm: 'RoomCrm',
				user: 'RoomUser',
			},
		}
	},
	computed: {
		...mapGetters(['data', 'layout', 'miniMode', 'mobileMode', 'leftPanel', 'leftPanelMobile', 'activeRoomTypes']),
		roomComponent() {
			return (roomType) => `Room${roomType.charAt(0).toUpperCase() + roomType.slice(1)}`
		},
		computedModel: {
			get() {
				return this.mobileMode ? this.leftPanelMobile : this.leftPanel
			},
			set(isOpen) {
				if (this.mobileMode) {
					this.setLeftPanelMobile(isOpen)
				}
			},
		},
		roomList() {
			let roomList = {}
			if (this.filterRooms === '') {
				this.activeRoomTypes.forEach((roomName) => {
					roomList[roomName] = Object.values(this.data.roomList[roomName]).sort(this.sortByRoomName)
				})
			} else {
				this.activeRoomTypes.forEach((roomName) => {
					roomList[roomName] = Object.values(this.data.roomList[roomName]).filter(this.filterRoomByName)
				})
			}
			return roomList
		},
	},
	methods: {
		...mapMutations(['setLeftPanelMobile']),
		filterRoomByName(room) {
			return room.name.toLowerCase().includes(this.filterRooms.toLowerCase())
		},
		sortByRoomName(a, b) {
			return a.name > b.name ? 1 : -1
		},
	},
}
</script>
<style lang="scss" scoped></style>
