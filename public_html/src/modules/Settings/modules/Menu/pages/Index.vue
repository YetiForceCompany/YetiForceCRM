<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <hook-wrapper>
    <div class="q-pa-md row q-gutter-md">
      <div class="col">
        <q-btn color="secondary" icon="mdi-content-save" @click="save" :label="$t('Save')" />
      </div>
    </div>
    <div class="q-pa-md row q-gutter-md">
      <div class="col">
        <tree-editor
          :nodes="nodes"
          :options="treeEditorOptions"
          @nodes-changed="updateNodes"
          @options-changed="updateOptions"
          @action-add="add"
          @action-edit="edit"
        />
      </div>
    </div>
  </hook-wrapper>
</template>

<script>
import getters from '/store/getters.js'
import mutations from '/store/mutations.js'
import Objects from '/utilities/Objects.js'
import TreeEditor from '/Core/components/TreeEditor.vue.js'

const moduleName = 'Settings.Menu.Pages.Index'

export default {
  name: moduleName,
  inject: ['App'],
  components: {
    TreeEditor
  },
  data() {
    return {
      nodes: [],
      treeEditorOptions: {}
    }
  },
  methods: {
    /**
     * Update current instance nodes data when they was changed in child component
     */
    updateNodes(nodes) {
      this.nodes = nodes.map(node => Objects.mergeDeep({}, node))
    },

    /**
     * Update options when they was changed in child component
     */
    updateOptions(options) {
      this.options = Objects.mergeDeep({}, options)
    },

    /**
     * Save menu
     */
    save() {
      const data = Objects.stripPrivate(this.nodes)
      this.$store.commit(mutations.Core.Menu.updateItems, data)
    },

    /**
     * Add node
     *
     * @param {object} node
     */
    add(node) {
      console.log('add', node)
    },

    /**
     * Edit node
     *
     * @param {object} node
     */
    edit(node) {
      console.log('edit', node)
    }
  },
  created() {
    const children = this.$store.getters[getters.Core.Menu.items].map(item => Objects.mergeDeep({}, item))
    this.nodes = [
      {
        label: 'Base',
        icon: 'mdi-cube',
        name: 'BaseMenu',
        position: 0,
        parent: null,
        children
      }
    ]
  }
}
</script>

<style></style>
