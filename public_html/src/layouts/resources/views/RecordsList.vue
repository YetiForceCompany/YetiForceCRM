/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

<template>
  <q-table
    :data="data"
    :columns="columns"
    row-key="subject"
    grid
    hide-header
    :pagination.sync="pagination"
    :title="title"
  >
    <template v-slot:item="props">
      <q-list class="list-item" padding @click="fetchRecord(props.row.id)">
        <q-item clickable>
          <q-item-section avatar>
            <q-icon name="mdi-text" />
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-primary"> {{ props.row.subject }}</q-item-label>
            <q-item-label class="flex items-center" overline>
              <q-breadcrumbs class="mr-2 text-grey-8" active-color="grey-8">
                <q-breadcrumbs-el
                  v-for="category in tree.categories[props.row.category].parentTree"
                  :key="tree.categories[category].label"
                >
                  <icon
                    v-if="tree.categories[category].icon"
                    :size="iconSize"
                    :icon="tree.categories[category].icon"
                    class="q-mr-sm"
                  />
                  {{ tree.categories[category].label }}
                </q-breadcrumbs-el>
                <q-tooltip>
                  {{ translate('JS_CATEGORY') }}
                </q-tooltip>
              </q-breadcrumbs>
              | {{ translate('JS_AUTHORED_BY') }}:
              <span v-html="props.row.assigned_user_id" class="q-ml-sm"></span>
            </q-item-label>
            <q-item-label caption>{{ props.row.introduction }}</q-item-label>
          </q-item-section>
          <q-item-section side top>
            <q-item-label caption>{{ props.row.short_time }}</q-item-label>
            <q-tooltip>
              {{ props.row.full_time }}
            </q-tooltip>
          </q-item-section>
        </q-item>
      </q-list>
    </template>
    <template v-slot:bottom="props"> </template>
  </q-table>
</template>
<script>
import Icon from '../../../components/Icon.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('KnowledgeBase')
export default {
  name: 'RecordsList',
  components: { Icon },
  props: {
    data: {
      type: Array,
      default: []
    },
    title: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      moduleName: '',
      pagination: {
        rowsPerPage: 0
      },
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
        { name: 'short_time', align: 'center', label: 'Short time', field: 'short_time', sortable: true },
        { name: 'introduction', align: 'center', label: 'Introduction', field: 'introduction', sortable: true }
      ]
    }
  },
  computed: {
    ...mapGetters(['tree', 'iconSize'])
  },
  methods: {
    ...mapActions(['fetchRecord'])
  },
  mounted() {
    console.log(111111111111111, this.$root)
    console.log(111111111111111, this)
  }
}
</script>
<style>
.Knowledge-Base .q-table__bottom,
.Knowledge-Base__dialog .q-table__bottom {
  display: none;
}
.list-item {
  width: 100%;
}
</style>
