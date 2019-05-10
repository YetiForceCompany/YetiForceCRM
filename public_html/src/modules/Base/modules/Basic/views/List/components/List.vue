<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div>
    <q-table
      :title="moduleName"
      :data="data"
      :columns="columns"
      row-key="name"
      :selected-rows-label="getSelectedString"
      selection="multiple"
      :selected.sync="selected"
      :pagination.sync="pagination"
      :loading="loading"
      :filter="filter"
      @request="onRequest"
      :visible-columns="visibleColumns"
      binary-state-sort
    >
      <template v-slot:top-right>
        <div v-if="$q.screen.gt.xs" class="col">
          <q-toggle v-model="visibleColumns" val="website" label="Website" />
          <q-toggle v-model="visibleColumns" val="phone" label="Phone" />
          <q-toggle v-model="visibleColumns" val="assigned_user_id" label="Assigned To" />
        </div>
        <q-select
          v-else
          v-model="visibleColumns"
          multiple
          borderless
          dense
          options-dense
          :display-value="$q.lang.table.columns"
          emit-value
          map-options
          :options="columns"
          option-value="name"
          style="min-width: 150px"
        />
        <q-input class="q-ml-lg" borderless dense debounce="300" v-model="filter" placeholder="Search">
          <template v-slot:append>
            <q-icon name="mdi-magnify" />
          </template>
        </q-input>
      </template>
    </q-table>
  </div>
</template>

<script>
import getters from '/src/store/getters.js'
import actions from '/src/store/actions.js'

const moduleName = 'Base.Basic.List'
export default {
  name: moduleName,
  props: {
    moduleName: {
      type: String,
      default: 'Basic'
    },
    columns: {
      type: Array
    },
    data: {
      type: Array
    }
  },
  data() {
    return {
      visibleColumns: ['website', 'phone', 'assigned_user_id'],
      selected: [],
      filter: '',
      loading: false,
      pagination: {
        sortBy: 'name',
        descending: false,
        page: 1,
        rowsPerPage: 3,
        rowsNumber: 10
      }
    }
  },
  methods: {
    getSelectedString() {
      return this.selected.length === 0
        ? ''
        : `${this.selected.length} record${this.selected.length > 1 ? 's' : ''} selected of ${this.data.length}`
    },
    onRequest(props) {
      let { page, rowsPerPage, rowsNumber, sortBy, descending } = props.pagination
      let filter = props.filter
      this.loading = true
      setTimeout(() => {
        this.pagination.rowsNumber = this.getRowsNumberCount(filter)
        let fetchCount = rowsPerPage === 0 ? rowsNumber : rowsPerPage
        let startRow = (page - 1) * rowsPerPage
        let returnedData = this.fetchFromServer(startRow, fetchCount, filter, sortBy, descending)
        this.data.splice(0, this.data.length, ...returnedData)
        this.pagination.page = page
        this.pagination.rowsPerPage = rowsPerPage
        this.pagination.sortBy = sortBy
        this.pagination.descending = descending
        this.loading = false
      }, 1500)
    },
    fetchFromServer(startRow, count, filter, sortBy, descending) {
      let data = []
      if (!filter) {
        data = this.rows.slice(startRow, startRow + count)
      } else {
        let found = 0
        for (let index = startRow, items = 0; index < this.rows.length && items < count; ++index) {
          let row = this.rows[index]
          if (!row['name'].includes(filter)) {
            continue
          }
          ++found
          if (found >= startRow) {
            data.push(row)
            ++items
          }
        }
      }
      if (sortBy) {
        data.sort((a, b) => {
          let x = descending ? b : a
          let y = descending ? a : b
          if (sortBy === 'desc') {
            return x[sortBy] > y[sortBy] ? 1 : x[sortBy] < y[sortBy] ? -1 : 0
          } else {
            return parseFloat(x[sortBy]) - parseFloat(y[sortBy])
          }
        })
      }

      return data
    },
    getRowsNumberCount(filter) {
      if (!filter) {
        return this.rows.length
      }
      let count = 0
      this.rows.forEach(treat => {
        if (treat['name'].includes(filter)) {
          ++count
        }
      })
      return count
    }
  },
  computed: {
    rows() {
      return this.$store.getters[getters.Base[this.moduleName].getEntries]
    }
  },
  mounted() {
    this.onRequest({
      pagination: this.pagination,
      filter: undefined
    })
  }
}
</script>
