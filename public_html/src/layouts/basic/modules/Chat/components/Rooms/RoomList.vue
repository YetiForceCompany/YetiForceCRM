<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<q-list v-if="isVisible" class="q-mb-none" dense>
		<q-item-label class="flex items-center text-bold text-muted q-py-sm q-px-md" @click="showAllRoomsButton ? toggleRoomExpanded(roomType) : ''">
			<q-item-section avatar :class="itemAvatarClass">
				<YfIcon :icon="getGroupIcon(roomType)" :size="layout.drawer.fs" :style="roomType === 'user' ? { 'margin-left': '-2px', 'margin-right': '2px' } : {}" />
				<q-btn v-show="showAllRoomsButton" :icon="isRoomExpanded ? 'mdi-chevron-up' : 'mdi-chevron-down'" dense flat round color="primary" class="q-mx-xs">
					<q-tooltip>{{ translate(isRoomExpanded ? 'JS_CHAT_HIDE_ROOMS' : 'JS_CHAT_SHOW_ROOMS') }}</q-tooltip>
				</q-btn>
			</q-item-section>
			{{ translate(`JS_CHAT_ROOM_${roomType.toUpperCase()}`) }}
			<div class="q-ml-auto" @click.stop>
				<slot name="labelRight"> </slot>
				<slot v-if="selectRoom" name="selectRoomButton">
					<q-btn dense flat round size="sm" color="primary" icon="mdi-magnify" @click="toggleRoomSelect()">
						<q-tooltip>{{ translate(`JS_CHAT_ADD_ROOM_${roomType.toUpperCase()}`) }}</q-tooltip>
					</q-btn>
				</slot>
				<q-icon class="q-pr-xs" :size="layout.drawer.fs" name="mdi-information">
					<q-tooltip>{{ translate(`JS_CHAT_ROOM_DESCRIPTION_${roomType.toUpperCase()}`) }}</q-tooltip>
				</q-icon>
			</div>
		</q-item-label>
		<slot name="aboveItems"> </slot>
		<slot v-if="selectRoom" name="selectRoom">
			<q-item v-show="isSelectRoomVisible">
				<RoomSelectAsync class="q-pb-xs" :roomType="roomType" :isVisible.sync="isSelectRoomVisible" />
			</q-item>
		</slot>
		<template v-for="(room, roomId) of roomData">
			<q-item
				v-show="isRoomExpanded && room.name !== undefined"
				class="q-pl-sm u-hover-container"
				clickable
				v-ripple
				:key="roomId"
				:active="data.currentRoom.recordId === room.recordid && data.currentRoom.roomType === roomType"
				active-class="bg-teal-1 text-grey-8"
				@click="onRoomClick({ id: room.recordid, roomType })"
			>
				<div class="full-width flex items-center justify-between no-wrap">
					<div class="ellipsis-2-lines">
						<slot name="itemAvatar" :room="room" />
						{{ room.name }}
					</div>
					<div class="flex items-center justify-end no-wrap">
						<div class="text-no-wrap">
							<transition appear enter-active-class="animate__animated animate__flash" mode="out-in">
								<q-badge
									v-if="room.cnt_new_message !== undefined && room.cnt_new_message > 0"
									color="danger"
									:label="room.cnt_new_message"
									:key="room.cnt_new_message"
								/>
							</transition>
							<slot name="itemRight" :room="room"></slot>
							<RoomUnpinButton v-if="selectRoom" :roomType="roomType" :recordId="room.recordid" />
							<q-btn
								@click.stop="toggleRoomSoundNotification({ roomType, id: room.recordid })"
								dense
								round
								flat
								size="xs"
								:icon="isSoundActive(roomType, room.recordid) ? 'mdi-volume-high' : 'mdi-volume-off'"
								:color="isSoundActive(roomType, room.recordid) ? 'primary' : ''"
								:disable="!isSoundNotification"
							>
								<q-tooltip>{{ translate(isSoundActive(roomType, room.recordid) ? 'JS_CHAT_SOUND_ON' : 'JS_CHAT_SOUND_OFF') }}</q-tooltip>
							</q-btn>
						</div>
					</div>
				</div>
			</q-item>
		</template>
		<slot name="belowItems"></slot>
	</q-list>
</template>
<script>
import RoomSelectAsync from './RoomSelectAsync.vue'
import RoomUnpinButton from './RoomUnpinButton.vue'
import { getGroupIcon } from '../../utils/utils.js'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
	name: 'RoomList',
	components: { RoomSelectAsync, RoomUnpinButton },
	props: {
		roomType: {
			type: String,
			required: true,
		},
		roomData: {
			type: Array,
			required: true,
		},
		isVisible: {
			type: Boolean,
			default: true,
		},
		selectRoom: {
			type: Boolean,
			default: true,
		},
	},
	data() {
		return {
			showAllRooms: true,
			isSelectRoomVisible: false,
		}
	},
	computed: {
		...mapGetters(['data', 'isSoundNotification', 'roomSoundNotificationsOff', 'layout', 'roomsExpanded']),
		itemAvatarClass() {
			return [this.showAllRoomsButton ? 'flex-row items-center q-pr-none' : '']
		},
		showAllRoomsButton() {
			return this.roomData.length
		},
		isRoomExpanded() {
			return this.roomsExpanded.includes(this.roomType)
		},
	},
	watch: {
		isSelectRoomVisible(value) {
			this.$emit('toggleSelectRoom', value)
		},
	},
	methods: {
		...mapActions(['fetchRoom', 'toggleRoomSoundNotification', 'toggleRoomExpanded', 'mobileMode']),
		...mapMutations(['setLeftPanelMobile']),
		getGroupIcon,
		isSoundActive(roomType, id) {
			return this.isSoundNotification && !this.roomSoundNotificationsOff[roomType].includes(id)
		},
		onRoomClick({ id, roomType }) {
			this.fetchRoom({ id, roomType })
			if (this.mobileMode) {
				this.setLeftPanelMobile(false)
			}
		},
		toggleRoomSelect() {
			this.isSelectRoomVisible = !this.isSelectRoomVisible
		},
	},
}
</script>
<style lang="sass" scoped></style>
