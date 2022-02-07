<!--
/**
 * NoResults component
 *
 * @description Global component
 * @license YetiForce Public License 5.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
-->
<template>
	<div class="flex items-center q-px-lg q-py-sm">
		<q-icon class="q-mr-sm" name="mdi-alert-outline"></q-icon>
		{{ translate('JS_NO_RESULTS_FOUND') }}
	</div>
</template>
<script>
export default {
	name: 'NoResults',
}
</script>
<style scoped></style>
