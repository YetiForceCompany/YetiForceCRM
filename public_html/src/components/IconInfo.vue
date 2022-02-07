<!--
/**
 * IconInfo component
 *
 * @description Global component
 * @license YetiForce Public License 5.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
-->
<template>
	<div class="flex">
		<q-icon
			:class="['cursor-pointer', tooltipId]"
			:name="searchInfoShow ? 'mdi-information' : 'mdi-information-outline'"
			:style="`font-size: ${options.iconSize};`"
			@click.prevent="searchInfoShow = !searchInfoShow"
		/>
		<div>
			<q-tooltip
				v-model="searchInfoShow"
				:content-style="`font-size: ${options.tooltipFont}`"
				:content-class="[options.backgroundClass, tooltipId, 'all-pointer-events']"
			>
				<slot></slot>
			</q-tooltip>
		</div>
	</div>
</template>

<script>
export default {
	name: 'IconInfo',
	props: {
		customOptions: {
			type: Object,
			default: () => {
				return {}
			},
		},
	},
	data() {
		return {
			searchInfoShow: false,
			options: {
				iconSize: 'inherit',
				tooltipFont: '14px',
				backgroundClass: 'bg-primary',
			},
			tooltipId: `tooltip-id-${Quasar.utils.uid()}`,
		}
	},
	created() {
		this.options = Object.assign(this.options, this.customOptions)
		document.addEventListener('click', (e) => {
			if (this.searchInfoShow && !e.target.offsetParent.classList.contains(this.tooltipId) && !e.target.classList.contains(this.tooltipId)) {
				this.searchInfoShow = false
			}
		})
	},
}
</script>
<style scoped></style>
