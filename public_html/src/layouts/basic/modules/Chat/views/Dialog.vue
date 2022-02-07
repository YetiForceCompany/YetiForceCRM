<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<div v-if="config.isChatAllowed">
		<ChatButtonDialog :dialogLoading="dialogLoading"></ChatButtonDialog>
		<q-dialog
			v-model="dialogModel"
			:maximized="!computedMiniMode"
			:content-class="dialogClasses"
			transition-show="slide-up"
			transition-hide="slide-down"
			seamless
			@show="dialogLoading = false"
			@hide="dialogLoading = false"
		>
			<DragResize :coordinates.sync="coordinates" :maximized="!computedMiniMode" @dragstop="onDragstop">
				<ChatContainer :parentRefs="$refs" container />
			</DragResize>
		</q-dialog>
	</div>
</template>
<script>
import ChatButtonDialog from '../components/ChatButtonDialog.vue'
import ChatContainer from '../components/ChatContainer.vue'
import DragResize from 'components/DragResize.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
	name: 'Dialog',
	components: { ChatButtonDialog, ChatContainer, DragResize },
	data() {
		return {
			dragStopped: true,
			addingRoom: false,
			dialogLoading: false,
			dialogModel: false,
		}
	},
	computed: {
		...mapGetters(['miniMode', 'data', 'config', 'dialog']),
		coordinates: {
			get() {
				return this.$store.getters['Chat/coordinates']
			},
			set(coords) {
				this.setDragState()
				this.setCoordinates(coords)
			},
		},
		computedMiniMode() {
			return this.$q.platform.is.desktop ? this.miniMode : false
		},
		dialogClasses() {
			return {
				'quasar-reset': true,
				animated: true,
				animate__slideOutDown: !this.dialog,
				animate__slideInUp: this.dialog,
				'all-pointer-events': !this.dragStopped,
			}
		},
	},
	created() {
		this.startUpdatesListener()
	},
	updated() {
		this.initDialogModel()
	},
	methods: {
		...mapMutations(['setCoordinates']),
		...mapActions(['startUpdatesListener']),
		initDialogModel() {
			if (!this.dialogModel && this.dialog) {
				this.dialogModel = true
			}
		},
		setDragState() {
			this.dragStopped = false
		},
		onDragstop(e) {
			this.dragStopped = true
		},
	},
}
</script>
<style scoped lang="scss"></style>
