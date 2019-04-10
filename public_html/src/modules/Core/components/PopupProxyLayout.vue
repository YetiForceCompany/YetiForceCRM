<!--
/**
 * PopupProxyLayout
 *
 * @description This is automatic component chooser based on QPopupProxy,
 * used to display context menu on large screens or dialog on mobile devices.
 * @license YetiForce Public License 3.0
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */
-->
<template>
  <q-popup-proxy>
    <hook-wrapper>
      <q-layout style="min-height:10px;">
        <q-header>
          <slot name="header">
            <div class="row bg-primary q-pa-sm" v-if="title">
              <div class="col">
                <div class="text-caption text-truncate"><q-icon :name="icon" left v-if="icon" />{{ title }}</div>
              </div>
            </div>
          </slot>
        </q-header>
        <q-page-container>
          <q-page style="min-height:10px">
            <hook-wrapper name="page">
              <slot name="page">
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
              </slot>
            </hook-wrapper>
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
/**
 * PopupProxyLayout component
 *
 * @vue-prop {array} items [optional if you want just list]
 * @vue-prop {string} itemKey [optional if you want just list]
 * @vue-prop {string} title [optional]
 * @vue-prop {string} icon [optional]
 *
 * @vue-slot header
 * @vue-slot page
 * @vue-slot footer
 */
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
