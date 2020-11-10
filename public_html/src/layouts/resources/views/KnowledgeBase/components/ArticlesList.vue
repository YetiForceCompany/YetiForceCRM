
<template>
  <div class="KnowledgeBase__ArticlesList">
    <q-table
      :data="data"
      :columns="columns"
      :pagination.sync="pagination"
      :title="title"
      row-key="subject"
      grid
      hide-header
    >
      <template #item="props">
        <q-list
          class="full-width"
          padding
          @click.prevent="onClickRecord(props.row.id)"
        >
          <q-item clickable>
            <q-item-section avatar>
              <q-icon name="mdi-text" />
            </q-item-section>
            <q-item-section>
              <q-item-label class="text-primary">
                <a
                  class="js-popover-tooltip--record"
                  :href="`index.php?module=${moduleName}&view=Detail&record=${props.row.id}`"
                >
                  {{ props.row.subject }}
                </a>
              </q-item-label>
              <q-item-label
                class="flex items-center"
                overline
              >
                <q-breadcrumbs
                  class="mr-2 text-grey-8"
                  active-color="grey-8"
                >
                  <q-breadcrumbs-el
                    v-for="category in tree.categories[props.row.category].parentTree"
                    :key="tree.categories[category].label"
                  >
                    <YfIcon
                      v-if="tree.categories[category].icon"
                      class="q-mr-sm"
                      :size="iconSize"
                      :icon="tree.categories[category].icon"
                    />
                    {{ tree.categories[category].label }}
                  </q-breadcrumbs-el>
                  <q-tooltip>
                    {{ translate('JS_KB_CATEGORY') }}
                  </q-tooltip>
                </q-breadcrumbs>
                | {{ translate('JS_KB_AUTHORED_BY') }}:
                <span
                  class="q-ml-sm"
                  v-html="props.row.assigned_user_id"
                ></span>
              </q-item-label>
              <q-item-label caption v-html="props.row.introduction"></q-item-label>
            </q-item-section>
            <q-item-section
              side
              top
            >
              <q-item-label caption>{{ props.row.short_time }}</q-item-label>
              <q-tooltip>
                {{ props.row.full_time }}
              </q-tooltip>
            </q-item-section>
          </q-item>
        </q-list>
      </template>
      <template #bottom="props"> </template>
    </q-table>
    <div :class="['flex items-center q-px-lg q-py-sm', hasData ? 'hidden' : '']">
      <q-icon
        class="q-mr-sm"
        name="mdi-alert-outline"
      ></q-icon>
      {{ translate('JS_NO_RESULTS_FOUND') }}
    </div>
  </div>
</template>
<script>
import YfIcon from '~/components/YfIcon.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('KnowledgeBase')
export default {
  name: 'ArticlesList',
  components: { YfIcon },
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
    ...mapGetters(['tree', 'iconSize', 'moduleName']),
    hasData() {
      return this.data.length
    }
  },
  methods: {
    ...mapActions(['fetchRecord']),
    onClickRecord(id) {
      this.fetchRecord(id).then(() => {
        this.$emit('onClickRecord', id)
      })
    }
  }
}
</script>
<style>
.KnowledgeBase__ArticlesList .q-table__bottom {
  display: none !important;
}
</style>
