<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <hook-wrapper>
    <q-btn color="secondary" icon="mdi-content-save" @click="save">{{ $t('Save') }}</q-btn>
    <q-separator />
    <q-tree :nodes="cNodes" node-key="name">
      <template v-slot:default-header="prop">
        <div class="row items-center" v-touch-pan.prevent.mouse.mousePrevent="handlePan">
          <q-icon :name="prop.node.icon || 'mdi-cube'" class="q-mr-sm" />
          <div class="label">
            {{ prop.node.label }}
          </div>
        </div>
      </template>
      <template v-slot:default-body="prop">
        <div class="actions">
          <q-btn-group outline>
            <q-btn round outline icon="mdi-arrow-down" size="sm" @click.prevent="onDown(prop.node)" />
            <q-btn round outline icon="mdi-arrow-up" size="sm" @click.prevent="onUp(prop.node)" />
            <q-btn round outline icon="mdi-arrow-left" size="sm" @click.prevent="onLeft(prop.node)" />
            <q-btn round outline icon="mdi-arrow-bottom-right" size="sm" @click.prevent="onBottomRight(prop.node)" />
            <q-btn round outline icon="mdi-arrow-top-right" size="sm" @click.prevent="onTopRight(prop.node)" />
          </q-btn-group>
          <q-btn-group outline>
            <q-btn round outline icon="mdi-plus" size="sm" @click.prevent="onPlus(prop.node)" />
            <q-btn round outline icon="mdi-minus" size="sm" @click.prevent="onMinus(prop.node)" />
            <q-btn round outline icon="mdi-square-edit-outline" size="sm" @click.prevent="onEdit(prop.node)" />
          </q-btn-group>
        </div>
      </template>
    </q-tree>
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
      return this.resolveIndexes(this.nodes)
    }
  },
  methods: {
    addParents(nodes, parent = null) {
      return nodes.map((node, index) => {
        const current = Objects.mergeDeep({}, node)
        if (typeof current.parent === 'undefined') {
          this.$set(current, 'parent', parent)
        }
        if (typeof current.children !== 'undefined' && Array.isArray(current.children) && current.children.length) {
          current.children = this.addParents(current.children, current)
        }
        return current
      })
    },
    resolveIndexes(nodes) {
      return nodes.map((node, index) => {
        if (typeof node.index === 'undefined') {
          node.index = index
        }
        if (typeof node.children !== 'undefined' && Array.isArray(node.children) && node.children.length) {
          node.children = this.resolveIndexes(node.children)
        }
        return node
      })
    },
    handlePan(event) {
      console.log(event)
    },
    onDown(node) {
      const children = node.parent.children.map(child => {
        return Objects.mergeDeepReactive(child)
      })
      for (let [index, child] of children.entries()) {
        if (child.name === node.name) {
          const removed = children[index + 1]
          children.splice(index, 2, removed, child)
          break
        }
      }
      node.parent.children = children
    },
    onUp(node) {
      console.log(node.index)
      const children = node.parent.children.map(child => {
        return Objects.mergeDeepReactive(child)
      })
      for (let [index, child] of children.entries()) {
        if (child.name === node.name) {
          const removed = children[index - 1]
          children.splice(index - 1, 2, child, removed)
          break
        }
      }
      node.parent.children = children
    },
    onLeft(node) {
      console.log(node)
    },
    onBottomRight(node) {
      console.log(node)
    },
    onTopRight(node) {
      console.log(node)
    },
    onPlus(node) {
      console.log(node)
    },
    onMinus(node) {
      console.log(node)
    },
    onEdit(node) {
      console.log(node)
    },
    save() {
      this.$store.commit(mutations.Core.Menu.updateItems, this.cNodes)
    }
  },
  created() {
    const children = this.$store.getters[getters.Core.Menu.items].map(item => Objects.mergeDeep({}, item))
    this.nodes = this.addParents(
      this.resolveIndexes([
        {
          label: 'Base',
          icon: 'mdi-cube',
          name: 'BaseMenu',
          position: 0,
          parent: null,
          children
        }
      ])
    )
  }
}
</script>

<style></style>
