<!--
/**
 * IconInfo component
 *
 * @description Global component
 * @license YetiForce Public License 3.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
-->
<template>
  <div class="flex">
    <q-icon
      class="cursor-pointer"
      :name="searchInfoShow ? 'mdi-information' : 'mdi-information-outline'"
      :style="`font-size: ${options.iconSize};`"
      @click="searchInfoShow = !searchInfoShow"
    />
    <div>
      <q-tooltip
        v-model="searchInfoShow"
        :content-style="`font-size: ${options.tooltipFont}`"
        :content-class="options.backgroundClass"
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
      }
    }
  },
  data() {
    return {
      searchInfoShow: false,
      options: {
        iconSize: 'inherit',
        tooltipFont: '14px',
        backgroundClass: 'bg-primary'
      }
    }
  },
  created() {
    this.options = Object.assign(this.options, this.customOptions)
  }
}
</script>
<style scoped>
</style>
