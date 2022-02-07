<!--
/**
 * ChatButtonDialog component
 *
 * @description Chat button for toggling chat window.
 * @license YetiForce Public License 5.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
-->
<template>
	<YfDrag :active="config.draggableButton" :coordinates.sync="buttonCoordinates">
		<transition :enter-active-class="buttonAnimationClasses" mode="out-in">
			<q-btn
				ref="chatBtn"
				unelevated
				:key="parseInt(data.amountOfNewMessages)"
				:loading="dialogLoading"
				color="primary"
				:class="buttonClass"
				style="z-index: 99999999999"
				@mouseup="showDialog"
				:round="config.draggableButton"
				:size="buttonSize"
			>
				<q-badge v-if="hasCurrentRecordChat" :class="addBadgeClass" color="white" :floating="config.draggableButton" @mouseup="addRecordRoomToChat()">
					<q-icon name="mdi-plus" size="1rem" />
					<q-tooltip>{{ translate('JS_CHAT_ROOM_ADD_CURRENT') }}</q-tooltip>
				</q-badge>
				<YfIcon icon="yfi-branding-chat" />
				<q-badge v-if="config.showNumberOfNewMessages" v-show="data.amountOfNewMessages > 0" color="danger" floating>
					<div>{{ data.amountOfNewMessages }}</div>
				</q-badge>
			</q-btn>
		</transition>
	</YfDrag>
</template>
<script>
import YfDrag from 'components/YfDrag.vue'
import isEqual from 'lodash.isequal'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
	name: 'ChatButtonDialog',
	components: { YfDrag },
	data() {
		return {
			dragging: false,
			windowConfig: CONFIG,
			addingRoom: false,
			dragTimeout: 300,
		}
	},
	props: {
		dialogLoading: {
			type: Boolean,
			required: true,
		},
	},
	computed: {
		...mapGetters(['miniMode', 'data', 'config', 'getDetailPreview']),
		buttonClass() {
			return ['glossy animation-duration', this.config.draggableButton ? 'btn-absolute' : 'q-px-sm']
		},
		buttonSize() {
			return this.config.draggableButton ? '' : '0.826rem'
		},
		addBadgeClass() {
			return ['shadow-3 text-primary btn-badge', this.config.draggableButton ? 'btn-badge--left-top' : 'q-mr-xs']
		},
		dialog: {
			get() {
				return this.$store.getters['Chat/dialog']
			},
			set(isOpen) {
				this.setDialog(isOpen)
			},
		},
		buttonCoordinates: {
			get() {
				return this.$store.getters['Chat/buttonCoordinates']
			},
			set(coords) {
				if (!isEqual({ left: coords.left, top: coords.top }, { ...this.$store.getters['Chat/buttonCoordinates'] })) {
					this.setDragState()
					this.setButtonCoordinates(coords)
				}
			},
		},
		buttonAnimationClasses() {
			return this.data.amountOfNewMessages ? 'animate__animated animate__shakeX' : ''
		},
		hasCurrentRecordChat() {
			if (!this.config.activeRoomTypes.includes('crm')) {
				return false
			}
			let id = false
			if (this.isDetail) {
				id = app.getRecordId()
			}
			if (this.getDetailPreview && this.config.chatModules.some((el) => el.id === this.getDetailPreview.module)) {
				id = this.getDetailPreview.id
			}
			if (id && !this.data.roomList.crm[id]) {
				return true
			} else {
				return false
			}
		},
		isDetail() {
			return this.windowConfig.view === 'Detail' && this.config.chatModules.some((el) => el.id === this.windowConfig.module)
		},
	},
	methods: {
		...mapMutations(['setDialog', 'setButtonCoordinates', 'updateRooms', 'setTab']),
		...mapActions(['fetchRoom']),
		showDialog() {
			this.dragging = false
			setTimeout((_) => {
				if (!this.dragging && !this.addingRoom) {
					this.dialog = !this.dialog
				}
			}, this.dragTimeout)
		},
		addRecordRoomToChat() {
			setTimeout((_) => {
				this.addingRoom = true
				AppConnector.request({
					module: 'Chat',
					action: 'Room',
					mode: 'addToFavorites',
					roomType: 'crm',
					recordId: this.isDetail ? app.getRecordId() : this.getDetailPreview.id,
				}).done(({ result }) => {
					this.addingRoom = false
					this.updateRooms(result)
					this.$q.notify({
						position: 'top',
						color: 'success',
						message: this.translate('JS_CHAT_ROOM_ADDED'),
						icon: 'mdi-check',
					})
					this.fetchRoom({ id: result.record_id, roomType: 'crm' }, false).then((_) => {
						this.dialog = true
						this.setTab('chat')
					})
				})
			}, this.dragTimeout)
		},
		setDragState() {
			this.dragging = true
		},
	},
}
</script>
<style scoped lang="scss">
$btn-badge-size: 23px;

.btn-absolute {
	width: 100%;
	height: 100%;
	position: absolute;
	top: 0;
	left: 0;
}

.btn-badge {
	pointer-events: all;
	justify-content: center;
	align-items: center;
	width: $btn-badge-size;
	height: $btn-badge-size;
	border-radius: 100%;
	transition: all 0.2s ease-in-out;

	&:hover {
		transform: scale(1.5);
	}

	&--left-top {
		top: -8px;
		left: -7px;
	}
}

.animation-duration {
	animation-duration: 0.8s;
}
</style>
