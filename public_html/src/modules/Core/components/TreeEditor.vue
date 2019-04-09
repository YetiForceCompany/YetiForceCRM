<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-tree :nodes="cNodes" :node-key="internalOptions.nodeKey">
    <template v-slot:default-header="prop">
      <div class="row items-center" v-touch-pan.prevent.mouse.mousePrevent="handlePan">
        <q-icon :name="prop.node.icon || 'mdi-cube'" class="q-mr-sm" />
        <hook-wrapper name="label" class="label">
          <slot name="label">{{ prop.node.label }}</slot>
        </hook-wrapper>
        <hook-wrapper name="actions" class="q-ml-md text-grey actions" v-if="prop.node.$_options.buttons.display">
          <slot name="move-actions">
            <q-btn-group outline>
              <q-btn
                :ripple="false"
                round
                outline
                icon="mdi-arrow-down"
                size="sm"
                @click.stop.prevent="onDown(prop.node)"
                v-if="prop.node.$_next"
              />
              <q-btn
                :ripple="false"
                round
                outline
                icon="mdi-arrow-up"
                size="sm"
                @click.stop.prevent="onUp(prop.node)"
                v-if="prop.node.$_previous"
              />
              <q-btn
                :ripple="false"
                round
                outline
                icon="mdi-arrow-left"
                size="sm"
                @click.stop.prevent="onLeft(prop.node)"
                v-if="prop.node.$_parent"
              />
              <q-btn
                :ripple="false"
                round
                outline
                icon="mdi-arrow-bottom-right"
                size="sm"
                @click.stop.prevent="onBottomRight(prop.node)"
                v-if="prop.node.$_next"
              />
              <q-btn
                :ripple="false"
                round
                outline
                icon="mdi-arrow-top-right"
                size="sm"
                @click.stop.prevent="onTopRight(prop.node)"
                v-if="prop.node.$_previous"
              />
            </q-btn-group>
          </slot>
          <slot name="edit">
            <q-btn-group
              outline
              v-if="
                prop.node.$_options.buttons.add ||
                  prop.node.$_options.buttons.remove ||
                  prop.node.$_options.buttons.edit
              "
            >
              <q-btn
                :ripple="false"
                round
                outline
                icon="mdi-plus"
                size="sm"
                @click.stop.prevent="onPlus(prop.node)"
                v-if="prop.node.$_options.buttons.add"
              />
              <q-btn
                :ripple="false"
                round
                outline
                icon="mdi-minus"
                size="sm"
                @click.stop.prevent="onMinus(prop.node)"
                v-if="prop.node.$_options.buttons.remove"
              />
              <q-btn
                :ripple="false"
                round
                outline
                icon="mdi-square-edit-outline"
                size="sm"
                @click.stop.prevent="onEdit(prop.node)"
                v-if="prop.node.$_options.buttons.edit"
              />
            </q-btn-group>
          </slot>
        </hook-wrapper>
      </div>
    </template>
    <template v-slot:default-body="prop"></template>
  </q-tree>
</template>

<script>
import Objects from '/utilities/Objects.js'

const moduleName = 'Core.Components.TreeEditor'
const privatePrefix = moduleName.split('.').join('')

export default {
  name: moduleName,

  props: {
    nodes: Array,
    options: Object
  },

  data() {
    return {
      internalNodes: [],
      internalOptions: {
        nodeKey: 'name',
        node: {
          display: true,
          buttons: {
            display: true,
            add: true,
            remove: true,
            edit: true
          }
        }
      },
      watchers: []
    }
  },

  computed: {
    cNodes() {
      return this.buildTree(this.internalNodes)
    }
  },

  methods: {
    /**
     * Add node options
     */
    addNodeOptions(nodes) {
      const clonedOptions = Objects.mergeDeep({}, this.internalOptions.node)
      return nodes.map(node => {
        this.$set(
          node,
          '$_options',
          Objects.mergeDeep({}, clonedOptions, node.hasOwnProperty('options') ? node.options : {})
        )
        if (typeof node.children !== 'undefined' && Array.isArray(node.children) && node.children.length) {
          node.children = this.addNodeOptions(node.children)
        }
        return node
      })
    },

    /**
     * Add parent nodes to each node
     */
    addParents(nodes, parent = null) {
      return nodes.map(node => {
        this.$set(node, '$_parent', parent)
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
        this.$set(node, '$_index', index)
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
          this.$set(node, '$_next', nodes[index + 1])
        } else {
          this.$set(node, '$_next', null)
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
          this.$set(node, '$_previous', nodes[index - 1])
        } else {
          this.$set(node, '$_previous', null)
        }
        if (typeof node.children !== 'undefined' && Array.isArray(node.children) && node.children.length) {
          this.$set(node, 'children', this.addPrevious(node.children))
        }
        return node
      })
    },

    /**
     * Build tree with additional priv properties
     */
    buildTree(nodes) {
      nodes = this.addNodeOptions(nodes)
      nodes = this.addParents(nodes)
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
      const children = node.$_parent.children.map(child => {
        return Objects.mergeDeepReactive(child)
      })
      for (let [index, child] of children.entries()) {
        if (child.name === node.name) {
          const removed = children[index + 1]
          children.splice(index, 2, removed, child)
          break
        }
      }
      node.$_parent.children = children
    },

    /**
     * Move node up
     *
     * @param {object} node
     */
    onUp(node) {
      const children = node.$_parent.children.map(child => {
        return Objects.mergeDeepReactive(child)
      })
      for (let [index, child] of children.entries()) {
        if (child.name === node.name) {
          const removed = children[index - 1]
          children.splice(index - 1, 2, child, removed)
          break
        }
      }
      node.$_parent.children = children
    },

    /**
     * Move node outside current parent
     *
     * @param {object} node
     */
    onLeft(node) {
      const parent = node.$_parent
      let parentOfParentChildren = this.cNodes.map(item => item)
      if (parent.$_parent !== null) {
        parentOfParentChildren = parent.$_parent.children.map(item => item)
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
      if (parent.$_parent !== null) {
        parent.$_parent.children = result
      } else {
        this.internalNodes = result
      }
    },

    /**
     * Move node to next node children
     */
    onBottomRight(node) {
      node.$_parent.children = node.$_parent.children.filter(current => current !== node)
      node.$_next.children.push(node)
    },

    /**
     * Move node to previous node children
     */
    onTopRight(node) {
      node.$_parent.children = node.$_parent.children.filter(current => current !== node)
      node.$_previous.children.push(node)
    },

    /**
     * Add node
     */
    onPlus(node) {
      this.$emit('action:add', node)
    },

    /**
     * Edit node
     */
    onEdit(node) {
      this.$emit('action:edit', node)
    }
  },

  created() {
    this.watchers.push(
      this.$watch(
        'nodes',
        nodes => {
          const striped = Objects.stripPrivate(this.internalNodes)
          if (!Objects.equalDeep(nodes, striped)) {
            this.internalNodes = nodes.map(node => Objects.mergeDeep({}, node))
          }
        },
        { immediate: true, deep: true }
      )
    )
    this.watchers.push(
      this.$watch(
        'internalNodes',
        internalNodes => {
          const striped = Objects.stripPrivate(this.internalNodes)
          if (!Objects.equalDeep(this.nodes, striped)) {
            this.$emit('update:nodes', striped.map(node => Objects.mergeDeep({}, node)))
          }
        },
        { deep: true }
      )
    )
    this.watchers.push(
      this.$watch(
        'options',
        options => {
          const striped = Objects.stripPrivate(this.internalOptions)
          if (!Objects.equalDeep(options, striped)) {
            this.internalOptions = Objects.mergeDeep({}, this.internalOptions, options)
          }
        },
        { immediate: true, deep: true }
      )
    )
    this.watchers.push(
      this.$watch(
        'internalOptions',
        internalOptions => {
          const striped = Objects.stripPrivate(this.internalOptions)
          if (!Objects.equalDeep(this.options, striped)) {
            this.$emit('update:options', Objects.mergeDeep({}, striped))
          }
        },
        { deep: true }
      )
    )
  },

  beforeDestroy() {
    this.watchers.forEach(unwatchFn => unwatchFn())
  }
}
</script>

<style>

</style>
