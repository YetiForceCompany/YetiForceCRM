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
    <q-btn :icon="choosenIcon.className" :label="$t('LBL_SELECT_ICON')" @click="showIcons" />
    <q-dialog v-model="iconsVisible" style="width:50hw">
      <q-layout container class="bg-white">
        <q-header class="bg-white">
          <q-toolbar class="bg-grey-10">
            <q-avatar>
              <q-icon name="mdi-image-search" />
            </q-avatar>
            <q-toolbar-title>{{ $t('LBL_SELECT_ICON') }}</q-toolbar-title>
            <q-btn flat v-close-popup round dense icon="mdi-close" />
          </q-toolbar>
          <div class="row q-pa-md">
            <div class="col">
              <q-input outlined :label="$t('LBL_SEARCH')" v-model="search">
                <template v-slot:prepend>
                  <q-icon name="mdi-magnify" />
                </template>
              </q-input>
            </div>
          </div>
        </q-header>
        <q-page-container>
          <q-page style="height: 50vh" class="q-pa-md scroll" ref="page">
            <q-scroll-area style="height:100%">
              <div class="row q-mt-md" v-for="(row, index) in rows" :key="index">
                <div
                  v-ripple
                  :class="[
                    'col',
                    'relative-position',
                    'container',
                    'text-lowercase',
                    'text-caption',
                    'text-center',
                    'q-pa-sm',
                    $style.icon
                  ]"
                  v-for="icon in row"
                  :key="icon.name"
                  @click.stop.prevent="chooseIcon(icon)"
                >
                  <q-icon top size="36px" :name="icon.className" />
                  <div class="q-mt-sm">{{ icon.name }}</div>
                </div>
              </div>
            </q-scroll-area>
          </q-page>
        </q-page-container>
      </q-layout>
    </q-dialog>
  </hook-wrapper>
</template>

<script>
import getters from '/store/getters.js'
const moduleName = 'Core.Icons.Components.IconSelector'

export default {
  name: moduleName,
  model: {
    prop: 'icon',
    event: 'change'
  },
  props: {
    columns: {
      type: Number,
      default: Quasar.plugins.Screen.win ? 6 : 4
    }
  },
  data() {
    return {
      iconsVisible: false,
      search: '',
      iconsHeight: '0px',
      choosenIcon: {
        name: 'image-search',
        className: 'mdi-image-search',
        keywords: ['image', 'search']
      }
    }
  },
  computed: {
    allIcons() {
      const icons = this.$store.getters[getters.Core.Icons.get]
      const filtered = []
      for (let name in icons) {
        const icon = icons[name]
        if (this.search) {
          const hasKeyWord = icon.keywords.filter(keyword => {
            return keyword.indexOf(this.search) !== -1
          })
          if (hasKeyWord.length) {
            filtered.push(icon)
          }
        } else {
          filtered.push(icon)
        }
      }
      return filtered
    },

    rows() {
      const rows = []
      let currentRowIndex = 0
      let currentRow = []
      for (let i = 0, len = this.allIcons.length; i < len; i++) {
        let calculatedRowIndex = Math.floor(i / this.columns)
        if (calculatedRowIndex !== currentRowIndex) {
          currentRowIndex = calculatedRowIndex
          rows.push(currentRow)
          currentRow = []
        }
        currentRow.push(this.allIcons[i])
      }
      return rows
    }
  },
  methods: {
    chooseIcon(icon) {
      this.choosenIcon = icon
      this.$emit('change', icon)
      this.iconsVisible = false
    },
    showIcons() {
      this.iconsVisible = true
    }
  },
  created() {
    this.icon = this.choosenIcon
  }
}
</script>

<style module lang="stylus">
@import '../../../../../css/app.variables.styl';

.icon {
  cursor: pointer;

  &:hover {
    background: $grey-2;
  }
}
</style>
