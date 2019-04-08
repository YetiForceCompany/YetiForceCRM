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
        <q-tree :nodes="cNodes" node-key="name">
          <template v-slot:default-header="prop">
            <div class="row items-center" v-touch-pan.prevent.mouse.mousePrevent="handlePan">
              <q-icon :name="prop.node.icon || 'mdi-cube'" class="q-mr-sm" />
              <hook-wrapper name="label" class="label">
                {{ prop.node.label }}
              </hook-wrapper>
              <hook-wrapper name="actions" class="q-ml-md text-grey actions">
                <q-btn-group outline>
                  <q-btn
                    :ripple="false"
                    round
                    outline
                    icon="mdi-arrow-down"
                    size="sm"
                    @click.stop.prevent="onDown(prop.node)"
                    v-if="prop.node.$_SettingsMenu_next"
                  />
                  <q-btn
                    :ripple="false"
                    round
                    outline
                    icon="mdi-arrow-up"
                    size="sm"
                    @click.stop.prevent="onUp(prop.node)"
                    v-if="prop.node.$_SettingsMenu_previous"
                  />
                  <q-btn
                    :ripple="false"
                    round
                    outline
                    icon="mdi-arrow-left"
                    size="sm"
                    @click.stop.prevent="onLeft(prop.node)"
                    v-if="prop.node.$_SettingsMenu_parent"
                  />
                  <q-btn
                    :ripple="false"
                    round
                    outline
                    icon="mdi-arrow-bottom-right"
                    size="sm"
                    @click.stop.prevent="onBottomRight(prop.node)"
                    v-if="prop.node.$_SettingsMenu_next"
                  />
                  <q-btn
                    :ripple="false"
                    round
                    outline
                    icon="mdi-arrow-top-right"
                    size="sm"
                    @click.stop.prevent="onTopRight(prop.node)"
                    v-if="prop.node.$_SettingsMenu_previous"
                  />
                </q-btn-group>
                <q-btn-group outline>
                  <q-btn
                    :ripple="false"
                    round
                    outline
                    icon="mdi-plus"
                    size="sm"
                    @click.stop.prevent="onPlus(prop.node)"
                  />
                  <q-btn
                    :ripple="false"
                    round
                    outline
                    icon="mdi-minus"
                    size="sm"
                    @click.stop.prevent="onMinus(prop.node)"
                  />
                  <q-btn
                    :ripple="false"
                    round
                    outline
                    icon="mdi-square-edit-outline"
                    size="sm"
                    @click.stop.prevent="onEdit(prop.node)"
                  />
                </q-btn-group>
              </hook-wrapper>
            </div>
          </template>
          <template v-slot:default-body="prop"> </template>
        </q-tree>
      </div>
    </div>
  </hook-wrapper>
</template>

<script>
import getters from '/store/getters.js'
import mutations from '/store/mutations.js'
import Objects from '/utilities/Objects.js'

const moduleName = 'Settings.Menu.Pages.Index'

export default {
  name: moduleName,
  inject: ['App'],
  data() {
    return {
      nodes: []
    }
  },
  computed: {
    cNodes() {
      return this.buildTree(this.nodes)
    }
  },
  methods: {
    /**
     * Add parent nodes to each node
     */
    addParents(nodes, parent = null) {
      return nodes.map((node, index) => {
        this.$set(node, '$_SettingsMenu_parent', parent)
        if (typeof node.children !== 'undefined' && Array.isArray(node.children) && node.children.length) {
          node.children = this.addParents(node.children, node)
        }
        return node
      })
    },
    /**
     * Add actual index to each node
     */
    resolveIndexes(nodes) {
      return nodes.map((node, index) => {
        this.$set(node, '$_SettingsMenu_index', index)
        if (typeof node.children !== 'undefined' && Array.isArray(node.children) && node.children.length) {
          this.$set(node, 'children', this.resolveIndexes(node.children))
        }
        return node
      })
    },
    /**
     * Add next item if exists to each node
     */
    addNext(nodes) {
      return nodes.map((node, index) => {
        if (typeof nodes[index + 1] !== 'undefined') {
          this.$set(node, '$_SettingsMenu_next', nodes[index + 1])
        } else {
          this.$set(node, '$_SettingsMenu_next', null)
        }
        if (typeof node.children !== 'undefined' && Array.isArray(node.children) && node.children.length) {
          this.$set(node, 'children', this.addNext(node.children))
        }
        return node
      })
    },
    /**
     * Add previous item if exists to each node
     */
    addPrevious(nodes) {
      return nodes.map((node, index) => {
        if (typeof nodes[index - 1] !== 'undefined') {
          this.$set(node, '$_SettingsMenu_previous', nodes[index - 1])
        } else {
          this.$set(node, '$_SettingsMenu_previous', null)
        }
        if (typeof node.children !== 'undefined' && Array.isArray(node.children) && node.children.length) {
          this.$set(node, 'children', this.addPrevious(node.children))
        }
        return node
      })
    },
    /**
     * Build tree with additional private properties
     */
    buildTree(nodes) {
      this.addParents(nodes)
      nodes = this.resolveIndexes(nodes)
      nodes = this.addNext(nodes)
      nodes = this.addPrevious(nodes)
      return nodes
    },
    /**
     * Handle drag and drop
     */
    handlePan(event) {},
    /**
     * Move node down
     *
     * @param {object} node
     */
    onDown(node) {
      const children = node.$_SettingsMenu_parent.children.map(child => {
        return Objects.mergeDeepReactive(child)
      })
      for (let [index, child] of children.entries()) {
        if (child.name === node.name) {
          const removed = children[index + 1]
          children.splice(index, 2, removed, child)
          break
        }
      }
      node.$_SettingsMenu_parent.children = children
    },
    /**
     * Move node up
     *
     * @param {object} node
     */
    onUp(node) {
      const children = node.$_SettingsMenu_parent.children.map(child => {
        return Objects.mergeDeepReactive(child)
      })
      for (let [index, child] of children.entries()) {
        if (child.name === node.name) {
          const removed = children[index - 1]
          children.splice(index - 1, 2, child, removed)
          break
        }
      }
      node.$_SettingsMenu_parent.children = children
    },
    /**
     * Move node outside current parent
     *
     * @param {object} node
     */
    onLeft(node) {
      const parent = node.$_SettingsMenu_parent
      let parentOfParentChildren = this.cNodes.map(item => item)
      if (parent.$_SettingsMenu_parent !== null) {
        parentOfParentChildren = parent.$_SettingsMenu_parent.children.map(item => item)
      }
      parent.children = parent.children.filter(current => {
        return current !== node
      })
      let result = []
      for (let child of parentOfParentChildren) {
        if (child === parent) {
          result.push(child)
          result.push(node)
        } else {
          result.push(child)
        }
      }
      if (parent.$_SettingsMenu_parent !== null) {
        parent.$_SettingsMenu_parent.children = result
      } else {
        this.nodes = result
      }
    },
    /**
     * Move node to next node children
     */
    onBottomRight(node) {
      node.$_SettingsMenu_parent.children = node.$_SettingsMenu_parent.children.filter(current => current !== node)
      node.$_SettingsMenu_next.children.push(node)
    },
    /**
     * Move node to previous node children
     */
    onTopRight(node) {
      node.$_SettingsMenu_parent.children = node.$_SettingsMenu_parent.children.filter(current => current !== node)
      node.$_SettingsMenu_previous.children.push(node)
    },
    onPlus(node) {},
    onMinus(node) {},
    onEdit(node) {},
    /**
     * Save menu
     */
    save() {
      const data = Objects.stripPrivate(this.cNodes)
      this.$store.commit(mutations.Core.Menu.updateItems, data)
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
