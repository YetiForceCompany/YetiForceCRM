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
          :nodes="menu.base"
          :options="treeOptions.base"
          @nodes-changed="updateBaseNodes"
          @options-changed="updateBaseOptions"
          @action-add="add"
          @action-edit="edit"
        />
      </div>
      <div class="col">
        <tree-editor
          :nodes="menu.settings"
          :options="treeOptions.settings"
          @nodes-changed="updateSettingsNodes"
          @options-changed="updateSettingsOptions"
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
      menu: {
        base: [],
        settings: []
      },
      treeOptions: {
        base: {},
        settings: {}
      }
    }
  },
  methods: {
    /**
     * Update current instance nodes data when they was changed in child component
     */
    updateBaseNodes(nodes) {
      this.menu.base = nodes.map(node => Objects.mergeDeep({}, node))
    },

    /**
     * Update options when they was changed in child component
     */
    updateBaseOptions(options) {
      this.treeOptions.base = Objects.mergeDeep({}, options)
    },

    /**
     * Update current instance nodes data when they was changed in child component
     */
    updateSettingsNodes(nodes) {
      this.menu.settings = nodes.map(node => Objects.mergeDeep({}, node))
    },

    /**
     * Update options when they was changed in child component
     */
    updateSettingsOptions(options) {
      this.treeOptions.settings = Objects.mergeDeep({}, options)
    },

    /**
     * Save menu
     */
    save() {
      const base = Objects.stripPrivate(this.menu.base)
      const settings = Objects.stripPrivate(this.menu.settings)
      this.$store.commit(mutations.Core.Menu.updateItems, base)
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
    this.menu.base = [
      {
        label: 'Base',
        icon: 'mdi-cube',
        name: 'Base',
        position: 0,
        parent: null,
        options: {
          buttons: {
            display: false
          }
        },
        children: children.map(item => Objects.mergeDeep({}, item))
      }
    ]
    this.menu.settings = [
      {
        label: 'Settings',
        icon: 'mdi-settings',
        name: 'Settings',
        position: 0,
        parent: null,
        options: {
          buttons: {
            display: false
          }
        },
        children: children.map(item => Objects.mergeDeep({}, item))
      }
    ]
  }
}
</script>

<style></style>
