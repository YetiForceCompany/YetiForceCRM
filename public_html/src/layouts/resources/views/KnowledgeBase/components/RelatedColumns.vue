<!--
/**
 * RelatedColumns component
 *
 * @description Records columns component
 * @license YetiForce Public License 3.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
-->
<template>
  <div
    class="featured__container"
    :style="{
      '-webkit-column-count': columnBlocks.length,
      '-moz-column-count': columnBlocks.length,
      'column-count': columnBlocks.length
    }"
  >
    <template v-for="relatedBlock in columnBlocks">
      <div class="featured__block" :key="relatedBlock">
        <slot :relatedBlock="relatedBlock"></slot>
      </div>
    </template>
  </div>
</template>

<script>
export default {
  props: {
    columnBlocks: {
      type: Array,
      required: true
    }
  }
}
</script>

<style scoped>
.featured__container {
  -webkit-column-width: 27rem;
  -moz-column-width: 27rem;
  column-width: 27rem;
}
.featured__block {
  width: 100%;
  padding-bottom: 16px;
  -webkit-column-break-inside: avoid;
  page-break-inside: avoid;
  break-inside: avoid-column;
}
</style>
