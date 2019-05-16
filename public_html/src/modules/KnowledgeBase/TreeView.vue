/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

<template>
  <div class="h-100">
    <q-layout view="hHh lpr fFf" container class="absolute">
      <q-header elevated class="bg-primary text-white">
        <q-toolbar class="justify-center">
          <q-toolbar-title>
            Knowledge Base
          </q-toolbar-title>
        </q-toolbar>
        <q-toolbar class="justify-center q-py-md">
          <q-input
            v-model="filter"
            placeholder="Search"
            square
            outlined
            type="search"
            bg-color="grey-1"
            class="tree-search"
          >
            <template v-slot:append>
              <q-icon name="mdi-magnify" />
            </template>
          </q-input>
        </q-toolbar>
        <q-toolbar>
          <q-btn dense flat round icon="mdi-menu" @click="left = !left"></q-btn>
        </q-toolbar>
      </q-header>

      <q-drawer v-model="left" side="left" elevated :width="250" :breakpoint="700">
        <q-scroll-area class="fit">
          <q-list>
            <q-item
              clickable
              :active="active === 'mainCategories'"
              v-ripple
              @click="
                active = 'mainCategories'
                record = false
              "
            >
              <q-item-section avatar>
                <q-icon name="mdi-home" />
              </q-item-section>
              <q-item-section>
                Home
              </q-item-section>
            </q-item>
            <q-item
              v-for="(categoryValue, categoryKey) in activeCategories.categories"
              :key="categoryKey"
              clickable
              v-ripple
              @click="
                tree[categoryKey] !== undefined ? (active = categoryKey) : ''
                record = false
              "
            >
              <q-item-section avatar>
                <q-icon v-if="/^mdi|^fa/.test(categoryValue.icon)" :name="categoryValue.icon" />
                <q-icon v-else :class="[categoryValue.icon, 'q-icon']" />
              </q-item-section>
              <q-item-section>
                {{ categoryValue.label }}
              </q-item-section>
            </q-item>

            <q-separator v-if="activeCategories.records.length" />
            <q-item
              v-for="(recordValue, index) in activeCategories.records"
              :key="index"
              clickable
              v-ripple
              :active="record === recordValue"
              @click="record = recordValue"
            >
              <q-item-section avatar>
                <q-icon name="mdi-text" />
              </q-item-section>
              <q-item-section>
                {{ recordValue.subject }}
              </q-item-section>
            </q-item>
          </q-list>
        </q-scroll-area>
      </q-drawer>

      <q-page-container>
        <q-page class="q-pa-md">
          <div v-show="!record">
            <div class="q-pa-md row items-start q-gutter-md">
              <q-list
                bordered
                padding
                dense
                v-for="(categoryValue, categoryKey) in activeCategories.categories"
                :key="categoryKey"
                class="home-card"
              >
                <q-item-label header>{{ categoryValue.label }}</q-item-label>

                <q-item
                  clickable
                  v-for="featuredValue in activeCategories.featured[categoryKey]"
                  :key="featuredValue.id"
                  class="text-subtitle2"
                  v-ripple
                  @click="record = featuredValue"
                >
                  <q-item-section avatar>
                    <q-icon name="mdi-text"></q-icon>
                  </q-item-section>
                  <q-item-section> {{ featuredValue.subject }} </q-item-section>
                </q-item>
              </q-list>
            </div>

            <div class="q-pa-md row items-start q-gutter-md">
              <q-table
                v-if="activeCategories.records.length"
                title="Articles"
                :data="activeCategories.records"
                :columns="columns"
                row-key="subject"
                :filter="filter"
                grid
                hide-header
              >
                <template v-slot:item="props">
                  <q-list padding @click="record = props.row">
                    <q-item clickable>
                      <q-item-section>
                        <q-item-label overline>{{ props.row.subject }}</q-item-label>
                        <q-item-label>Single line item</q-item-label>
                        <q-item-label caption
                          >Secondary line text. Lorem ipsum dolor sit amet, consectetur adipiscit elit.</q-item-label
                        >
                      </q-item-section>
                      <q-item-section side top>
                        <q-item-label caption>{{
                          tree.mainCategories.categories[props.row.category].label
                        }}</q-item-label>
                      </q-item-section>
                    </q-item>
                  </q-list>
                </template>
              </q-table>
            </div>
          </div>
          <div v-show="record">
            <h5>{{ record.subject }}</h5>
            {{ (record.content + '').repeat(100) }}
          </div>
        </q-page>
      </q-page-container>
    </q-layout>
  </div>
</template>
<script>
export default {
  name: 'TreeView',
  data() {
    return {
      left: true,
      filter: '',
      record: false,
      columns: [],
      active: 'mainCategories',
      tree: {
        mainCategories: {
          categories: {},
          featured: {},
          records: []
        }
      }
    }
  },
  computed: {
    activeCategories: {
      get: function() {
        return this.tree[this.active]
      },
      set: function(newValue) {
        this.active = newValue
      }
    }
  }
}
</script>
<style scoped>
.tree-search {
  width: 50%;
}
.home-card {
  width: 100%;
  max-width: 250px;
}
</style>
