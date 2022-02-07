<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<RoomList isVisible :filterRooms="filterRooms" :roomData="roomData" :roomType="roomType">
		<template #labelRight>
			<q-btn dense flat round size="sm" color="primary" icon="mdi-plus" @click="toggleAddInput()">
				<q-tooltip>{{ translate('JS_CHAT_ADD_PRIVATE_ROOM') }}</q-tooltip>
			</q-btn>
		</template>
		<template #itemRight="{ room }">
			<q-btn
				v-if="isUserModerator(room)"
				:class="{
					'u-hover-display-inline': isHiddenOnHover(room.name),
				}"
				dense
				round
				flat
				:key="room.name"
				size="xs"
				@click.stop="showArchiveDialog(room)"
				color="negative"
				icon="mdi-delete"
				:loading="isArchiving && roomToArchive.name === room.name"
			>
				<q-tooltip>{{ translate('JS_CHAT_ROOM_ARCHIVE') }}</q-tooltip>
			</q-btn>
		</template>
		<template #aboveItems>
			<q-item v-show="isAddInputVisible">
				<RoomPrivateInput :showAddPrivateRoom.sync="isAddInputVisible" />
			</q-item>
		</template>
		<template #belowItems>
			<q-dialog v-if="arePrivateRooms" v-model="confirm" persistent content-class="quasar-reset">
				<q-card>
					<q-card-section class="row items-center">
						<q-avatar icon="mdi-alert-circle-outline" text-color="negative" />
						<span class="q-ml-sm">{{ translate('JS_CHAT_ROOM_ARCHIVE_MESSAGE').replace('${roomToArchive}', roomToArchive.name) }}</span>
					</q-card-section>
					<q-card-actions align="right">
						<q-btn flat :label="translate('JS_CANCEL')" color="black" @click="isArchiving = false" v-close-popup />
						<q-btn @click="archive(roomToArchive)" flat :label="translate('JS_ARCHIVE')" color="negative" v-close-popup />
					</q-card-actions>
				</q-card>
			</q-dialog>
		</template>
	</RoomList>
</template>
<script>
import RoomPrivateInput from './RoomPrivateInput.vue'
import RoomList from './RoomList.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
	name: 'RoomPrivate',
	components: { RoomPrivateInput, RoomList },
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
	data() {
		return {
			confirm: false,
			isArchiving: false,
			isAddInputVisible: false,
			roomToArchive: {},
		}
	},
	computed: {
		...mapGetters(['data', 'config']),
		arePrivateRooms() {
			return Object.keys(this.data.roomList.private).length
		},
		isUserModerator() {
			return (room) => {
				return room.creatorid === CONFIG.userId || this.config.isAdmin
			}
		},
		isHiddenOnHover() {
			return (roomName) => {
				return this.$q.platform.is.desktop && (!this.isArchiving || this.roomToArchive.name !== roomName)
			}
		},
	},
	methods: {
		...mapActions(['archivePrivateRoom']),
		showArchiveDialog(room) {
			this.isArchiving = true
			this.confirm = true
			this.roomToArchive = room
		},
		archive(roomToArchive) {
			this.archivePrivateRoom({ recordId: roomToArchive.recordid }).then((e) => {
				this.isArchiving = false
			})
		},
		toggleAddInput() {
			this.isAddInputVisible = !this.isAddInputVisible
		},
	},
}
</script>
<style lang="sass" scoped></style>
