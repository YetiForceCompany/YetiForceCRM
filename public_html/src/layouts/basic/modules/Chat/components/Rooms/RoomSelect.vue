<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<div class="full-width">
		<q-select
			ref="selectedOption"
			v-model="selectedOption"
			class="full-width"
			:hint="translate('JS_CHAT_ADD_FAVORITE_ROOM_FROM_MODULE')"
			:options="options"
			:option-value="optionValue"
			:option-label="optionLabel"
			:input-debounce="debounce"
			dense
			use-input
			fill-input
			hide-selected
			emit-value
			map-options
			hide-bottom-space
			popup-content-class="quasar-reset"
			@input="callbackInput($event)"
			@filter="filter"
		>
			<template #no-option>
				<q-item>
					<q-item-section class="text-grey"> {{ translate('JS_NO_RESULTS_FOUND') }} </q-item-section>
				</q-item>
			</template>
			<template #prepend>
				<slot name="prepend" :selected="selectedOption"></slot>
			</template>
			<template #append>
				<q-icon class="cursor-pointer" name="mdi-close" @click.prevent="hideSelect()" />
				<q-tooltip anchor="top middle">{{ translate('JS_CHAT_HIDE_ADD_PANEL') }}</q-tooltip>
			</template>
			<template v-if="hasOptionSlot" #option="scope">
				<slot name="option" :scope="scope"></slot>
			</template>
		</q-select>
	</div>
</template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations } = createNamespacedHelpers('Chat')

export default {
	name: 'RoomSelect',
	props: {
		isVisible: {
			type: Boolean,
		},
		options: {
			type: Array,
		},
		filter: {
			type: Function,
			required: false,
		},
		optionValue: {
			type: String,
			default: 'id',
		},
		optionLabel: {
			type: String,
			default: 'label',
		},
		debounce: {
			type: Number,
			default: 0,
		},
	},
	data() {
		return {
			selectedOption: null,
			computedOptions: [],
		}
	},
	computed: {
		hasOptionSlot() {
			return !!this.$scopedSlots.option
		},
	},
	watch: {
		isVisible(val) {
			if (val) {
				setTimeout(() => {
					this.$refs.selectedOption.showPopup()
				}, 100)
			} else {
				this.selectedOption = null
				this.$refs.selectedOption.hidePopup()
			}
		},
	},
	methods: {
		callbackInput(e) {
			this.$emit('input', e)
		},
		hideSelect() {
			this.$emit('update:isVisible', false)
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
