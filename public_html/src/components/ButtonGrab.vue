<!--
/**
 * Button grab component
 *
 * @description Global component
 * @license YetiForce Public License 5.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
-->
<template>
	<a :class="['grabbable text-decoration-none', linkClass]" href="#">
		<q-icon :class="[grabClass]" :size="size" name="mdi-drag" />
	</a>
</template>

<script>
export default {
	name: 'ButtonGrab',
	props: {
		grabClass: {
			type: String,
		},
		size: {
			type: String,
			required: false,
		},
		linkClass: {
			type: String,
			default: 'q-px-xs',
		},
	},
}
</script>

<style scoped>
.grabbable:hover {
	cursor: move;
	cursor: grab;
	cursor: -moz-grab;
	cursor: -webkit-grab;
}
.grabbable:active {
	cursor: grabbing;
	cursor: -moz-grabbing;
	cursor: -webkit-grabbing;
}
</style>
