<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<div class="fit bg-grey-11">
		<slot name="top"></slot>
		<div class="bg-grey-11">
			<q-input v-model="filterParticipants" class="q-px-sm" :placeholder="translate('JS_CHAT_FILTER_PARTICIPANTS')" dense>
				<template #prepend>
					<q-icon name="mdi-magnify" />
				</template>
				<template #append>
					<q-icon v-show="filterParticipants.length > 0" class="cursor-pointer" name="mdi-close" @click="filterParticipants = ''" />
				</template>
			</q-input>
			<q-list :style="{ 'font-size': layout.drawer.fs }">
				<q-item-label class="flex items-center text-bold text-muted q-py-sm q-px-md">
					<q-item-section avatar>
						<YfIcon icon="yfi-entrant-chat" :size="layout.drawer.fs" />
					</q-item-section>
					{{ translate('JS_CHAT_PARTICIPANTS') }}
					<div class="q-ml-auto">
						<q-btn v-if="isUserModerator" dense flat round size="sm" color="primary" icon="mdi-plus" @click="showAddPanel = !showAddPanel">
							<q-tooltip>{{ translate('JS_CHAT_ADD_PARTICIPANT') }}</q-tooltip>
						</q-btn>
						<q-icon name="mdi-information" class="q-pr-xs">
							<q-tooltip>{{ translate(`JS_CHAT_PARTICIPANTS_DESCRIPTION`) }}</q-tooltip>
						</q-icon>
					</div>
				</q-item-label>
				<q-item v-if="isUserModerator" v-show="showAddPanel && isUserModerator">
					<RoomPrivateUserSelect class="q-pb-xs" :isVisible.sync="showAddPanel" :roomId="currentRoomData.recordid" />
				</q-item>
				<template v-for="participant in participantsList">
					<q-item
						v-if="participant.user_name === participant.user_name"
						:key="participant.user_id"
						class="q-py-xs opacity-5"
						:active="!!participant.message"
						active-class="opacity-1 text-black"
					>
						<q-item-section avatar>
							<q-avatar>
								<img v-if="participant.image" key="avatar" :src="participant.image" alt="participant image" />
								<q-icon v-else key="icon" name="mdi-account" size="40px" />
							</q-avatar>
						</q-item-section>
						<q-item-section>
							<div class="row line-height-small">
								<span class="col-12 ellipsis-1-line" :title="participant.user_name"
									>{{ participant.user_name }}
									<span v-if="participant.isAdmin && config.showRoleName">
										<q-icon class="align-baseline" name="mdi-crown" />
										<q-tooltip>{{ translate(`JS_CHAT_PARTICIPANT_ADMIN`) }}</q-tooltip>
									</span>
								</span>
								<span
									v-if="config.showRoleName"
									class="col-12 text-caption text-blue-6 text-weight-medium ellipsis-1-line"
									:title="participant.role_name"
									v-html="participant.role_name"
								></span>
								<span
									class="col-12 text-caption text-grey-5 ellipsis-1-line"
									:title="participant.message ? stripHtml(participant.message) : ''"
									v-html="participant.message"
								></span>
							</div>
						</q-item-section>
						<q-item-section side>
							<q-btn
								v-if="isUserModerator && participant.user_id !== userId"
								:size="itemActionsIconSize"
								dense
								round
								flat
								color="primary"
								icon="mdi-pin"
								@click.stop="
									removeUserFromRoom({
										roomType: currentRoomData.roomType,
										recordId: currentRoomData.recordid,
										userId: participant.user_id,
									})
								"
							>
								<q-tooltip>{{ translate(`JS_CHAT_PARTICIPANT_UNPIN`) }}</q-tooltip>
							</q-btn>
						</q-item-section>
					</q-item>
				</template>
			</q-list>
		</div>
	</div>
</template>
<script>
import RoomPrivateUserSelect from './Rooms/RoomPrivateUserSelect.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
	name: 'ChatPanelRight',
	components: { RoomPrivateUserSelect },
	props: {
		participants: {
			type: Array,
			required: true,
		},
	},
	data() {
		return {
			filterParticipants: '',
			userId: CONFIG.userId,
			showAddPanel: false,
			itemActionsIconSize: 'xs',
		}
	},
	computed: {
		...mapGetters(['currentRoomData', 'config', 'layout']),
		isUserModerator() {
			return this.currentRoomData.roomType === 'private' && (this.currentRoomData.creatorid === this.userId || this.config.isAdmin)
		},
		participantsList() {
			if (this.filterParticipants === '') {
				return this.participants.length ? this.participants.concat().sort(this.sortByCurrentUserMessagesName) : []
			} else {
				return this.participants.filter((participant) => {
					return (
						participant.user_name.toLowerCase().includes(this.filterParticipants.toLowerCase()) ||
						participant.role_name.toLowerCase().includes(this.filterParticipants.toLowerCase())
					)
				})
			}
		},
	},
	methods: {
		...mapActions(['removeUserFromRoom']),
		sortByCurrentUserMessagesName(a, b) {
			if (a.user_id === this.userId) {
				return -1
			}
			if (!a.message && b.message) {
				return 1
			} else if (a.message && !b.message) {
				return -1
			}
			if (a.user_name > b.user_name) {
				return 1
			} else {
				return -1
			}
		},
		stripHtml(html) {
			return app.stripHtml(html)
		},
	},
}
</script>
<style lang="scss" scoped>
.opacity-5 {
	opacity: 0.5;
}
.opacity-1 {
	opacity: 1;
}
.line-height-small {
	span {
		line-height: 1.4;
	}
}
</style>
