/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
<template>
	<div class="d-flex flex-wrap">
		<Picker v-model="newColor" style="width: 320px; box-shadow: none"></Picker>
		<Palette v-model="newColor" style="height: 100%; overflow: auto; flex-grow: 1; box-shadow: none"></Palette>
	</div>
</template>

<script>
import VueColor from 'vue-color'

export default {
	components: { Picker: VueColor.Chrome, Palette: VueColor.Swatches },
	props: {
		currentColor: {
			type: String,
		},
	},
	data() {
		return {
			newColor: '',
		}
	},
	methods: {
		getColor() {
			return this.newColor
		},
	},
	mounted() {
		this.newColor = this.currentColor
	},
}
</script>

<style lang="scss" scoped></style>
