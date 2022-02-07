<!--
/**
 * ColumnsGrid component
 *
 * @description Records columns component
 * @license YetiForce Public License 5.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
-->
<template>
	<div
		class="columns-grid__container"
		:style="{
			'-webkit-column-count': columnBlocks.length,
			'-moz-column-count': columnBlocks.length,
			'column-count': columnBlocks.length,
		}"
	>
		<template v-for="relatedBlock in columnBlocks">
			<div :key="relatedBlock" class="columns-grid__block">
				<slot :relatedBlock="relatedBlock"></slot>
			</div>
		</template>
	</div>
</template>

<script>
export default {
	name: 'ColumnsGrid',
	props: {
		columnBlocks: {
			type: Array,
			required: true,
		},
	},
}
</script>

<style scoped>
.columns-grid__container {
	-webkit-column-width: 27rem;
	-moz-column-width: 27rem;
	column-width: 27rem;
}
.columns-grid__block {
	width: 100%;
	padding-bottom: 16px;
	-webkit-column-break-inside: avoid;
	page-break-inside: avoid;
	break-inside: avoid-column;
}
</style>
