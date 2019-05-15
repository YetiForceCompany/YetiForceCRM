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

      <q-drawer v-model="left" side="left" bordered :width="200" :breakpoint="700">
        <q-scroll-area class="fit">
          <q-list>
            <q-item clickable :active="active === 'mainCategories'" v-ripple @click="active = 'mainCategories'">
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
              <q-card
                v-for="(categoryValue, categoryKey) in activeCategories.categories"
                :key="categoryKey"
                class="home-card"
              >
                <q-card-section>
                  <div class="text-h6">{{ categoryValue.label }}</div>
                  <div
                    v-for="featuredValue in activeCategories.featured[categoryKey]"
                    :key="featuredValue.id"
                    class="text-subtitle2"
                  >
                    {{ featuredValue.subject }}
                  </div>
                </q-card-section>
              </q-card>
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
                  <div class="grid-style-transition">
                    <q-list padding>
                      <q-item>
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
                  </div>
                </template>
              </q-table>
            </div>
          </div>
          <div v-show="record">
            <h5>{{ record.subject }}</h5>
            {{ record.content }}
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
      columns: [
        {
          name: 'desc',
          required: true,
          label: 'Title',
          align: 'left',
          field: row => row.subject,
          format: val => `${val}`,
          sortable: true
        },
        { name: 'category', align: 'center', label: 'Category', field: 'category', sortable: true }
      ],
      active: 'mainCategories',
      tree: {
        mainCategories: {
          categories: {
            T1: { tree: 'T1', parentTree: 'T1', parent: false, label: 'LBL_NONE', icon: '' },
            T2: { tree: 'T2', parentTree: 'T2', parent: false, label: 'aaaaa', icon: 'fas fa-archive' },
            T3: { tree: 'T3', parentTree: 'T3', parent: false, label: 'aaaaaa', icon: 'fas fa-adjust' },
            T6: { tree: 'T6', parentTree: 'T6', parent: false, label: 'ffff', icon: 'AdditionalIcon-Matrixes' },
            T7: { tree: 'T7', parentTree: 'T7', parent: false, label: 'gggg', icon: '' },
            T14: { tree: 'T14', parentTree: 'T14', parent: false, label: 'mmmmmmmmmm', icon: '' }
          },
          featured: {
            '0': [],
            T1: [
              { id: 306, category: 'T1', subject: 'Narz\u0119dzia' },
              { id: 307, category: 'T1', subject: 'Instrukcja dodawania kolor\u00f3w dla modu\u0142\u00f3w' },
              { id: 375, category: 'T1', subject: 'Narz\u0119dzia' },
              { id: 376, category: 'T1', subject: 'Instrukcja dodawania kolor\u00f3w dla modu\u0142\u00f3w' }
            ],
            T14: [
              { id: 372, category: 'T14', subject: 'Narz\u0119dzia' },
              { id: 373, category: 'T14', subject: 'Instrukcja dodawania kolor\u00f3w dla modu\u0142\u00f3w' }
            ]
          },
          records: []
        },
        T14: {
          categories: {
            T12: { tree: 'T12', parentTree: 'T14::T12', parent: 'T14', label: 'bbbbbbbbbbbbb', icon: '' },
            T11: { tree: 'T11', parentTree: 'T14::T11', parent: 'T14', label: 'pppppppppppp', icon: '' },
            T10: { tree: 'T10', parentTree: 'T14::T10', parent: 'T14', label: 'oooooooooooo', icon: '' }
          },
          featured: [[]],
          records: [
            { id: 372, category: 'T14', content: 'Lorem Ipsum dolor sit amet', subject: 'Narz\u0119dzia' },
            {
              id: 373,
              category: 'T14',
              content: 'Lorem Ipsum dolor sit amet',
              subject: 'Instrukcja dodawania kolor\u00f3w dla modu\u0142\u00f3w'
            },
            { id: 3721, category: 'T14', content: 'Lorem Ipsum dolor sit amet', subject: '1111Narz\u0119dzia' },
            {
              id: 3731,
              category: 'T14',
              content: 'Lorem Ipsum dolor sit amet',
              subject: '111111Instrukcja dodawania kolor\u00f3w dla modu\u0142\u00f3w'
            },
            { id: 3721, category: 'T14', content: 'Lorem Ipsum dolor sit amet', subject: '22222Narz\u0119dzia' },
            {
              id: 3731,
              category: 'T14',
              content: 'Lorem Ipsum dolor sit amet',
              subject: '222222Instrukcja dodawania kolor\u00f3w dla modu\u0142\u00f3w'
            },
            { id: 37211, category: 'T14', content: 'Lorem Ipsum dolor sit amet', subject: '222221111Narz\u0119dzia' },
            {
              id: 37311,
              category: 'T14',
              content: 'Lorem Ipsum dolor sit amet',
              subject: '22222222111111Instrukcja dodawania kolor\u00f3w dla modu\u0142\u00f3w'
            }
          ]
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
