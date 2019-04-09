<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-popup-proxy>
    <hook-wrapper>
      <q-layout style="min-height:10px">
        <q-header>
          <slot name="header">
            <q-toolbar class="bg-blue-grey-10">
              <q-avatar v-if="icon">
                <q-icon :name="icon" />
              </q-avatar>
              <q-toolbar-title>{{ title }}</q-toolbar-title>
            </q-toolbar>
          </slot>
        </q-header>
        <q-page-container>
          <q-page style="min-height:10px">
            <slot name="page">
              <hook-wrapper name="page">
                <q-list class="bg-white" bordered separator>
                  <hook-wrapper name="items">
                    <q-item
                      clickable
                      ripple
                      v-close-popup
                      v-for="item in items"
                      :key="item[itemKey]"
                      @click="select(item)"
                    >
                      <q-item-section avatar v-if="item.icon"><q-icon :name="item.icon"/></q-item-section>
                      <q-item-section>
                        <q-item-label>{{ item.label }}</q-item-label>
                        <q-item-label caption v-if="item.description">{{ item.description }}</q-item-label>
                      </q-item-section>
                    </q-item>
                  </hook-wrapper>
                </q-list>
              </hook-wrapper>
            </slot>
          </q-page>
        </q-page-container>
        <q-footer>
          <slot name="footer" />
        </q-footer>
      </q-layout>
    </hook-wrapper>
  </q-popup-proxy>
</template>

<script>
const moduleName = 'Core.Components.PopupProxyLayout'
export default {
  name: moduleName,
  props: {
    items: Array,
    title: String,
    icon: String,
    itemKey: String
  },
  methods: {
    select(option) {
      this.$emit('select', option)
    }
  }
}
</script>

<style></style>
