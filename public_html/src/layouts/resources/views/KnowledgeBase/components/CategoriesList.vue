
<template>
  <transition
    :enter-active-class="'animate__animated animate__faster ' + animationIn"
    :leave-active-class="'animate__animated animate__faster ' + animationOut"
  >
    <q-list v-show="show">
      <q-item
        v-show="activeCategoryDelayed === ''"
        active
      >
        <q-item-section avatar>
          <q-icon
            :name="tree.topCategory.icon"
            :size="iconSize"
          />
        </q-item-section>
        <q-item-section>{{ translate(tree.topCategory.label) }}</q-item-section>
      </q-item>
      <q-item
        v-if="activeCategoryDelayed !== ''"
        clickable
        active
        @click="fetchParentCategoryData()"
      >
        <q-item-section avatar>
          <YfIcon
            :size="iconSize"
            :icon="tree.categories[activeCategoryDelayed].icon || defaultTreeIcon"
          />
        </q-item-section>
        <q-item-section>{{ tree.categories[activeCategoryDelayed].label }}</q-item-section>
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
          <YfIcon
            :size="iconSize"
            :icon="tree.categories[categoryValue].icon || defaultTreeIcon"
          />
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
import YfIcon from '~/components/YfIcon.vue'

import { createNamespacedHelpers } from 'vuex'
const { mapGetters } = createNamespacedHelpers('KnowledgeBase')
export default {
  name: 'CategoriesList',
  components: { YfIcon },
  data() {
    return {
      show: true,
      animationIn: 'animate__slideInLeft',
      animationOut: 'animate__slideOutRight',
      animationChildClassIn: 'animate__slideInRight',
      animationChildClassOut: 'animate__slideOutLeft',
      animationParentClassIn: 'animate__slideInLeft',
      animationParentClassOut: 'animate__slideOutRight',
      activeCategoryDelayed: ''
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
      this.activeCategoryDelayed = this.activeCategory
      this.show = true
    }
  },
  methods: {
    fetchParentCategoryData() {
      this.animationIn = this.animationParentClassIn
      this.animationOut = this.animationParentClassOut
      this.show = false
      this.data.categories = []
      let parentCategory = ''
      const parentTreeArray = this.tree.categories[this.activeCategory]
        .parentTree
      if (parentTreeArray.length !== 1) {
        parentCategory = parentTreeArray[parentTreeArray.length - 2]
      }
      this.$emit('fetchData', parentCategory)
    },
    fetchChildCategoryData(categoryValue) {
      this.animationIn = this.animationChildClassIn
      this.animationOut = this.animationChildClassOut
      this.show = false
      this.data.categories = []
      this.$emit('fetchData', categoryValue)
    }
  },
  mounted() {
    this.activeCategoryDelayed = this.activeCategory
  }
}
</script>
<style></style>
