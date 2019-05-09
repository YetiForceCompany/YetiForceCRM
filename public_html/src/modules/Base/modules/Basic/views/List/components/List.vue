<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div>
    <q-table
      :title="moduleName"
      :data="rows"
      :columns="columns"
      row-key="name"
      :selected-rows-label="getSelectedString"
      selection="multiple"
      :selected.sync="selected"
    ></q-table>
  </div>
</template>

<script>
import getters from '/src/store/getters.js'
const moduleName = 'Base.Basic.List'
export default {
  name: moduleName,
  props: {
    moduleName: {
      type: String,
      default: 'Basic'
    }
  },
  data() {
    return {
      selected: []
    }
  },
  methods: {
    getSelectedString() {
      return this.selected.length === 0
        ? ''
        : `${this.selected.length} record${this.selected.length > 1 ? 's' : ''} selected of ${this.data.length}`
    }
  },
  computed: {
    columns() {
      return this.$store.getters[getters.Base[this.moduleName].getHeaders]
    },
    rows() {
      return this.$store.getters[getters.Base[this.moduleName].getEntries]
    }
  }
}
</script>
