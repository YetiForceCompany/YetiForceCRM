<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<RoomSelect
		class="q-pb-xs"
		:options="asyncOptions"
		:filter="asyncFilter"
		:isVisible.sync="getIsVisible"
		:debounce="searchDebounce"
		option-value="recordid"
		option-label="name"
		@input="pinRoom({ recordId: $event, roomType })"
	>
		<template #option="{ scope }">
			<q-item dense v-bind="scope.itemProps" v-on="scope.itemEvents">
				<q-item-section>
					{{ scope.opt.name }}
				</q-item-section>
			</q-item>
		</template>
	</RoomSelect>
</template>
<script>
import RoomSelect from './RoomSelect.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
	name: 'RoomSelectAsync',
	components: { RoomSelect },
	props: {
		isVisible: {
			type: Boolean,
		},
		roomType: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			asyncOptions: [],
			allOptions: [],
			searchDebounce: 0,
		}
	},
	computed: {
		getIsVisible: {
			get() {
				return this.isVisible
			},
			set(isVisible) {
				this.$emit('update:isVisible', isVisible)
			},
		},
	},
	methods: {
		...mapActions(['fetchRoomsUnpinned', 'pinRoom']),
		asyncFilter(val, update) {
			if (val === '') {
				this.fetchRoomsUnpinned({ roomType: this.roomType }).then((data) => {
					let result = data ? Object.values(data) : []
					update(() => {
						this.allOptions = this.asyncOptions = result
					})
				})
			} else {
				update(() => {
					const needle = val.toLowerCase()
					this.asyncOptions = this.allOptions.filter((v) => v.name.toLowerCase().indexOf(needle) > -1)
				})
			}
		},
	},
}
</script>
<style lang="sass" scoped></style>
