<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<div class="full-width">
		<q-select
			v-model="selectUser"
			ref="selectUser"
			class="full-width"
			dense
			use-input
			fill-input
			hide-selected
			multiple
			input-debounce="0"
			hide-bottom-space
			emit-value
			map-options
			popup-content-class="quasar-reset"
			option-value="id"
			option-label="label"
			option-img="img"
			:options="searchUsers"
			:label="translate('JS_CHAT_FILTER_USERS')"
			:hint="translate('JS_CHAT_ADD_USERS_TO_NEW_ROOM')"
			:error="!isValid"
			:behavior="dialog ? 'dialog' : 'default'"
			@filter="asyncFilter"
			@add="validateParticipant"
			@remove="unpinUser"
			@popup-hide="selectUser = null"
		>
			<template #no-option>
				<q-item>
					<q-item-section class="text-grey"> {{ translate('JS_NO_RESULTS_FOUND') }} </q-item-section>
				</q-item>
			</template>
			<template #append>
				<q-icon name="mdi-close" @click.prevent="$emit('update:isVisible', false), (isValid = true)" class="cursor-pointer" />
				<q-tooltip :anchor="tooltipAnchor">{{ translate('JS_CHAT_HIDE_ADD_PANEL') }}</q-tooltip>
			</template>
			<template #option="scope">
				<q-item dense v-bind="scope.itemProps" v-on="scope.itemEvents">
					<q-item-section avatar>
						<img v-if="scope.opt.img" :src="scope.opt.img" :alt="scope.opt.label" style="height: 1.7rem" />
						<q-icon v-else name="mdi-account" />
					</q-item-section>
					<q-item-section>
						{{ scope.opt.label }}
					</q-item-section>
				</q-item>
			</template>
			<template #error>
				{{ errorMessage }}
			</template>
		</q-select>
	</div>
</template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions, mapMutations } = createNamespacedHelpers('Chat')
const roomType = 'private'
export default {
	name: 'RoomPrivateUserSelect',
	props: {
		roomId: {
			type: Number,
			required: true,
		},
		isVisible: {
			type: Boolean,
			default: false,
		},
		dialog: {
			type: Boolean,
			default: false,
		},
	},
	data() {
		return {
			selectUser: null,
			users: [],
			searchUsers: [],
			isValid: true,
			errorMessage: '',
			addedUsers: [],
		}
	},
	watch: {
		isVisible(val) {
			if (val) {
				setTimeout(() => {
					this.$refs.selectUser.showPopup()
				}, 100)
			} else {
				this.$refs.selectUser.hidePopup()
			}
		},
	},
	computed: {
		...mapGetters(['layout']),
		tooltipAnchor() {
			return `${this.dialog ? 'bottom' : 'top'} middle`
		},
	},
	methods: {
		...mapActions(['fetchPrivateRoomUnpinnedUsers', 'addParticipant', 'removeUserFromRoom']),
		...mapMutations(['updateParticipants']),
		unpinUser({ value }) {
			let userId = value.pop()
			let recordId = this.roomId
			this.removeUserFromRoom({ roomType, recordId, userId })
		},
		validateParticipant({ value }) {
			this.addParticipant({
				recordId: this.roomId,
				userId: value,
			}).then(({ result }) => {
				if (result.message) {
					this.errorMessage = this.translate(result.message)
					this.isValid = false
				} else {
					this.isValid = true
					this.updateParticipants({
						recordId: this.roomId,
						roomType,
						data: result,
					})
					this.$q.notify({
						position: 'top',
						color: 'success',
						message: this.translate('JS_CHAT_PARTICIPANT_ADDED'),
						icon: 'mdi-check',
					})
				}
			})
		},
		asyncFilter(val, update) {
			if (val === '') {
				this.fetchPrivateRoomUnpinnedUsers(this.roomId).then((users) => {
					update(() => {
						this.users = this.searchUsers = users
					})
				})
			} else {
				update(() => {
					const needle = val.toLowerCase()
					this.searchUsers = this.users.filter((v) => v.label.toLowerCase().indexOf(needle) > -1)
				})
			}
		},
	},
}
</script>
<style lang="sass" scoped>
.select-dense
	.q-item
		min-height: 32px
		padding: 2px 16px
</style>
