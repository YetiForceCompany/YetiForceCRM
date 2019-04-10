<!--
/**
* IconSelector component
*
* @description icon selector - all icons included
* @license YetiForce Public License 3.0
* @author Rafal Pospiech <r.pospiech@yetiforce.com>
*/
-->
<template>
  <hook-wrapper>
    <!--<q-avatar :icon="choosenIcon.className" color="grey-10" text-color="white" />-->
    <q-btn :icon="choosenIcon.className" :label="$t('LBL_SELECT_ICON')">
      <popup-proxy-layout :title="$t('LBL_SELECT_ICON')" icon="mdi-emoticon-happy-outline">
        <template v-slot:page>
          <div class="row" v-for="(row, index) in rows" :key="index">
            <q-btn
              flat
              v-for="item in row"
              :key="item.name"
              :icon="item.icon.className"
              @click="chooseIcon(item.icon)"
              v-close-popup
            />
          </div>
        </template>
      </popup-proxy-layout>
    </q-btn>
  </hook-wrapper>
</template>

<script>
import getters from '/store/getters.js'
import PopupProxyLayout from '/Core/components/PopupProxyLayout.vue.js'
const moduleName = 'Core.Icons.Components.IconSelector'

export default {
  name: moduleName,
  components: {
    PopupProxyLayout
  },
  model: {
    prop: 'icon',
    event: 'change'
  },
  props: {
    columns: {
      type: Number,
      default: 8
    }
  },
  data() {
    return {
      choosenIcon: {
        name: 'dots-horizontal',
        className: 'mdi-dots-horizontal',
        keywords: ['mdi-dots-horizontal']
      }
    }
  },
  computed: {
    allIconsAsArray() {
      const icons = this.$store.getters[getters.Core.Icons.get]
      const iconsAsArray = []
      for (let name in icons) {
        iconsAsArray.push({ name, icon: icons[name] })
      }
      return iconsAsArray
    },
    rows() {
      const rows = []
      let currentRowIndex = 0
      let currentRow = []
      for (let i = 0, len = this.allIconsAsArray.length; i < len; i++) {
        let calculatedRowIndex = Math.floor(i / this.columns)
        if (calculatedRowIndex !== currentRowIndex) {
          currentRowIndex = calculatedRowIndex
          rows.push(currentRow)
          currentRow = []
        }
        currentRow.push(this.allIconsAsArray[i])
      }
      return rows
    }
  },
  methods: {
    chooseIcon(icon) {
      this.choosenIcon = icon
      this.$emit('change', icon)
    }
  },
  created() {
    this.icon = this.choosenIcon
  }
}
</script>

<style></style>
