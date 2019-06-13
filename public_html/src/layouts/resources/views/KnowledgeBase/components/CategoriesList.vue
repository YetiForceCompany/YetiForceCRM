<!--
/**
 * ArticlePreview component
 *
 * @description Article preview parent component
 * @license YetiForce Public License 3.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
-->
<template>
  <q-list>
    <q-item v-show="activeCategory === ''" active>
      <q-item-section avatar>
        <q-icon :name="tree.topCategory.icon" :size="iconSize" />
      </q-item-section>
      <q-item-section>{{ translate(tree.topCategory.label) }}</q-item-section>
    </q-item>
    <q-item v-if="activeCategory !== ''" clickable active @click="fetchParentCategoryData()">
      <q-item-section avatar>
        <icon :size="iconSize" :icon="tree.categories[activeCategory].icon || defaultTreeIcon" />
      </q-item-section>
      <q-item-section>{{ tree.categories[activeCategory].label }}</q-item-section>
      <q-item-section avatar>
        <q-icon name="mdi-chevron-left" />
      </q-item-section>
    </q-item>
    <q-item
      v-for="(categoryValue, categoryKey) in data.categories"
      :key="categoryKey"
      clickable
      v-ripple
      @click="$emit('fetchData', categoryValue)"
    >
      <q-item-section avatar>
        <icon :size="iconSize" :icon="tree.categories[categoryValue].icon || defaultTreeIcon" />
      </q-item-section>
      <q-item-section>{{ tree.categories[categoryValue].label }}</q-item-section>
      <q-item-section avatar>
        <q-icon name="mdi-chevron-right" />
      </q-item-section>
    </q-item>
  </q-list>
</template>
<script>
import Icon from '../../../../../components/Icon.vue'

import { createNamespacedHelpers } from 'vuex'
const { mapGetters } = createNamespacedHelpers('KnowledgeBase')
export default {
  name: 'CategoriesList',
  components: { Icon },
  props: {
    activeCategory: {
      type: String,
      required: true
    },
    data: {
      type: Object,
      required: true
    }
  },
  computed: {
    ...mapGetters(['tree', 'iconSize', 'defaultTreeIcon'])
  },
  methods: {
    fetchParentCategoryData() {
      let parentCategory = ''
      const parentTreeArray = this.tree.categories[this.activeCategory].parentTree
      if (parentTreeArray.length !== 1) {
        parentCategory = parentTreeArray[parentTreeArray.length - 2]
      }
      this.$emit('fetchData', parentCategory)
    }
  }
}
</script>
<style>
</style>
