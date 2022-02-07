<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<div class="full-width">
		<q-input
			ref="addRoomInput"
			v-model="addRoom"
			:placeholder="translate('JS_CHAT_ADD_PRIVATE_ROOM')"
			:error="!isValid"
			:loading="isValidating"
			@input="isValid = true"
			dense
			@keydown.enter="validateRoomName()"
		>
			<template #prepend>
				<q-btn color="primary" flat dense icon="mdi-plus" @click="validateRoomName()">
					<q-tooltip anchor="top middle">{{ translate('JS_ADD') }}</q-tooltip>
				</q-btn>
			</template>
			<template #append>
				<q-icon class="cursor-pointer" name="mdi-close" @click="$emit('update:showAddPrivateRoom', false)" />
				<q-tooltip anchor="top middle">{{ translate('JS_CHAT_HIDE_ADD_PANEL') }}</q-tooltip>
			</template>
			<template #error>
				{{ errorMessage }}
			</template>
		</q-input>
		<RoomPrivateUserSelect
			v-show="false"
			ref="select"
			class="q-pb-xs"
			:roomId="newRoomId"
			@update:isVisible="$refs.select.$refs.selectUser.hidePopup()"
			:dialog="true"
		/>
	</div>
</template>
<script>
import RoomPrivateUserSelect from './RoomPrivateUserSelect.vue'

import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions, mapMutations } = createNamespacedHelpers('Chat')

export default {
	name: 'RoomPrivateInput',
	components: { RoomPrivateUserSelect },
	props: {
		showAddPrivateRoom: {
			type: Boolean,
		},
	},
	data() {
		return {
			addRoom: '',
			isValid: true,
			errorMessage: '',
			isValidating: false,
			newRoomId: 0,
		}
	},
	watch: {
		showAddPrivateRoom() {
			if (this.showAddPrivateRoom) {
				this.$refs.addRoomInput.focus()
			}
		},
	},
	computed: {
		...mapGetters(['data']),
	},
	methods: {
		...mapActions(['addPrivateRoom']),
		...mapMutations(['updateRooms']),
		validateRoomName() {
			if (this.addRoom.length && !this.isValidating) {
				this.isValidating = true
				let roomExist = false
				for (let room in this.data.roomList.private) {
					if (this.data.roomList.private[room].name === this.addRoom) {
						roomExist = true
						break
					}
				}
				if (!roomExist) {
					this.addPrivateRoom({ name: this.addRoom }).then(({ result }) => {
						this.showUserSelect(Object.keys(result.private).pop())
						this.addRoom = ''
						this.updateRooms(result)
						this.isValidating = false
						this.$q.notify({
							position: 'top',
							color: 'success',
							message: this.translate('JS_CHAT_ROOM_ADDED'),
							icon: 'mdi-check',
						})
					})
				} else {
					this.errorMessage = this.translate('JS_CHAT_ROOM_EXISTS')
					this.isValid = false
					this.isValidating = false
				}
			} else {
				this.errorMessage = this.translate('JS_CHAT_ROOM_NAME_EMPTY')
				this.isValid = false
			}
		},
		showUserSelect(user) {
			this.newRoomId = parseInt(user)
			setTimeout(() => {
				this.$refs.select.$refs.selectUser.showPopup()
			}, 100)
		},
	},
}
</script>
<style lang="sass"></style>
