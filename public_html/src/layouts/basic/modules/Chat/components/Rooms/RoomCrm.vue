<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<RoomList
		ref="roomList"
		:isVisible="config.dynamicAddingRooms"
		:roomData="roomData"
		:roomType="roomType"
		:hideUnpinned="false"
		:filterRooms="filterRooms"
		@toggleSelectRoom="onToggleSelectRoom"
	>
		<template #itemRight="{ room }">
			<q-btn
				size="xs"
				dense
				round
				flat
				color="primary"
				:class="[room && room.moduleName && room.recordid ? 'js-popover-tooltip--record' : '', 'ellipsis']"
				@click.stop
				icon="mdi-link-variant"
				:href="`index.php?module=${room.moduleName}&view=Detail&record=${room.recordid}`"
			/>
		</template>
		<template #itemAvatar="{ room }">
			<YfIcon class="inline-block" :icon="'yfm-' + room.moduleName" style="vertical-align: text-bottom" size="inherit" />
		</template>
		<template #selectRoom>
			<q-item v-if="config.dynamicAddingRooms" v-show="isSelectVisible">
				<RoomSelect
					class="q-pb-xs"
					:options="modulesList"
					:isVisible.sync="isSelectVisible"
					:filter="filter"
					@input="showRecordsModal"
					@update:isVisible="updateRoomListSelect"
				>
					<template #prepend="{ selected }">
						<q-icon class="cursor-pointer" name="mdi-magnify" @click.prevent="showRecordsModal(selected)" />
						<q-tooltip anchor="top middle">{{ translate('JS_CHAT_SEARCH_RECORDS_OF_THE_SELECTED_MODULE') }}</q-tooltip>
					</template>
					<template #option="{ scope }">
						<q-item dense v-bind="scope.itemProps" v-on="scope.itemEvents">
							<q-item-section avatar>
								<YfIcon :icon="`yfm-${scope.opt.id}`" />
							</q-item-section>
							<q-item-section>
								{{ scope.opt.label }}
							</q-item-section>
						</q-item>
					</template>
				</RoomSelect>
			</q-item>
		</template>
	</RoomList>
</template>
<script>
import RoomSelect from './RoomSelect.vue'
import RoomList from './RoomList.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('Chat')
export default {
	name: 'RoomCrm',
	components: { RoomSelect, RoomList },
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
			showAddRoomPanel: false,
			isSelectVisible: false,
			modulesList: [],
		}
	},
	computed: {
		...mapGetters(['config']),
	},
	created() {
		this.modulesList = this.config.chatModules
	},
	methods: {
		updateRoomListSelect(val) {
			this.$refs.roomList.toggleRoomSelect()
		},
		...mapActions(['fetchRoomList']),
		showRecordsModal(val) {
			app.showRecordsList({ module: val, src_module: val }, (_modal, instance) => {
				instance.setSelectEvent((responseData) => {
					AppConnector.request({
						module: 'Chat',
						action: 'Room',
						mode: 'addToFavorites',
						roomType: 'crm',
						recordId: responseData.id,
					}).done(({ result }) => {
						this.fetchRoomList()
					})
				})
			})
		},
		filter(val, update) {
			if (val === '') {
				update(() => {
					this.modulesList = this.config.chatModules
				})
				return
			}
			update(() => {
				const needle = val.toLowerCase()
				this.modulesList = this.config.chatModules.filter((v) => v.label.toLowerCase().indexOf(needle) > -1)
			})
		},
		onToggleSelectRoom(val) {
			this.isSelectVisible = val
		},
	},
}
</script>
<style lang="sass" scoped></style>
