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
  <transition :enter-active-class="'animated ' + animationEnter" :leave-active-class="'animated ' + animationLeave">
    <q-list v-show="show">
      <q-item v-show="activeCategoryWait === ''" active>
        <q-item-section avatar>
          <q-icon :name="tree.topCategory.icon" :size="iconSize" />
        </q-item-section>
        <q-item-section>{{ translate(tree.topCategory.label) }}</q-item-section>
      </q-item>
      <q-item v-if="activeCategoryWait !== ''" clickable active @click="fetchParentCategoryData()">
        <q-item-section avatar>
          <icon :size="iconSize" :icon="tree.categories[activeCategoryWait].icon || defaultTreeIcon" />
        </q-item-section>
        <q-item-section>{{ tree.categories[activeCategoryWait].label }}</q-item-section>
        <q-item-section avatar>
          <q-icon name="mdi-chevron-left" />
        </q-item-section>
      </q-item>
      <q-item
        v-for="(categoryValue, categoryKey) in data.categories"
        :key="categoryKey"
        clickable
        v-ripple
        @click="fetchChildCategoryData(categoryValue)"
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
  </transition>
</template>
<script>
import Icon from '../../../../../components/Icon.vue'

import { createNamespacedHelpers } from 'vuex'
// import { setTimeout } from 'timers'
const { mapGetters } = createNamespacedHelpers('KnowledgeBase')
export default {
  name: 'CategoriesList',
  components: { Icon },
  data() {
    return {
      show: false,
      animationEnter: 'slideInLeft',
      animationLeave: 'slideOutRight',
      animationParentClassIn: 'slideInLeft',
      animationParentClassOut: 'slideOutRight',
      activeCategoryWait: ''
    }
  },
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
  watch: {
    data() {
      this.activeCategoryWait = this.activeCategory
      this.show = true
    }
  },
  methods: {
    fetchParentCategoryData() {
      this.show = false
      this.data.categories = []
      let parentCategory = ''
      const parentTreeArray = this.tree.categories[this.activeCategory].parentTree
      if (parentTreeArray.length !== 1) {
        parentCategory = parentTreeArray[parentTreeArray.length - 2]
      }
      this.$emit('fetchData', parentCategory)
    },
    fetchChildCategoryData(categoryValue) {
      this.show = false
      this.data.categories = []
      this.$emit('fetchData', categoryValue)
    }
  }
}
</script>
<style>
</style>
