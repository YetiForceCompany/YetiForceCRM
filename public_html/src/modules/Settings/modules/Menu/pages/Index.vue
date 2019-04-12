<!--
/**
 * Index page for Settings.Menu module
 *
 * @description Index page
 * @license YetiForce Public License 3.0
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */
-->
<template>
  <hook-wrapper>
    <div class="q-pa-md row q-gutter-md">
      <div class="col">
        <q-btn color="secondary" icon="mdi-content-save" @click="save" :label="$t('Save')" />
      </div>
    </div>
    <div class="q-pa-md row q-gutter-md">
      <div class="col">
        <tree-editor :nodes.sync="menu.base" :options.sync="treeOptions.base" @action:edit="edit">
          <template v-slot:addButton>
            <popup-proxy-layout
              :items="menuOptions"
              itemKey="value"
              :title="$t('LBL_SELECT_TYPE_OF_MENU')"
              icon="mdi-plus-circle-outline"
              @select="add"
            />
          </template>
        </tree-editor>
      </div>
      <div class="col">
        <tree-editor
          :nodes.sync="menu.settings"
          :options.sync="treeOptions.settings"
          @action:add="add"
          @action:edit="edit"
        />
      </div>
    </div>
    <q-dialog v-model="editor.visible">
      <q-layout style="min-height:10px" class="bg-white">
        <q-header bordered class="bg-white">
          <q-toolbar class="bg-white text-grey-10">
            <q-avatar v-if="editor.icon">
              <q-icon :name="editor.icon" />
            </q-avatar>
            <q-toolbar-title>{{ editor.title }}</q-toolbar-title>
            <q-btn icon="mdi-close" flat round dense v-close-popup />
          </q-toolbar>
        </q-header>
        <q-page-container>
          <q-page style="min-height:10px" class="q-pa-md">
            <component :is="editor.component" />
          </q-page>
        </q-page-container>
        <q-footer bordered class="bg-white q-pa-sm" align="right">
          <q-btn unelevated color="positive" :label="$t('LBL_ADD_MENU')" icon="mdi-plus" />
          <q-btn flat color="negative" :label="$t('LBL_CANCEL')" icon="mdi-close" v-close-popup />
        </q-footer>
      </q-layout>
    </q-dialog>
  </hook-wrapper>
</template>

<script>
import getters from '/store/getters.js'
import mutations from '/store/mutations.js'
import Objects from '/utilities/Objects.js'
import TreeEditor from '/Core/components/TreeEditor.vue.js'
import PopupProxyLayout from '/Core/components/PopupProxyLayout.vue.js'

import ModuleEditor from '../components/Module.vue.js'
import ShortcutEditor from '../components/Shortcut.vue.js'

const moduleName = 'Settings.Menu.Pages.Index'

export default {
  name: moduleName,
  inject: ['App'],
  components: {
    TreeEditor,
    PopupProxyLayout
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
      },
      editor: {
        visible: false,
        component: null,
        title: '',
        caption: '',
        icon: ''
      }
    }
  },
  computed: {
    menuOptions() {
      return this.$store.getters[getters.Core.Menu.types]
    }
  },
  methods: {
    /**
     * Save menu
     */
    save() {
      const base = Objects.stripPrivate(this.menu.base)
      const settings = Objects.stripPrivate(this.menu.settings)
      this.$store.commit(mutations.Core.Menu.updateItems, base)
    },

    /**
     * Add node button clicked
     *
     * @param {object} node
     */
    add(option) {
      this.editor.caption = this.$t('LBL_ADD_MENU')
      switch (option.value) {
        case 'module':
          this.editor.component = ModuleEditor
          this.editor.title = this.$t('LBL_MODULE')
          this.editor.icon = 'mdi-cube'
          break
        case 'shortcut':
          this.editor.component = ShortcutEditor
          this.editor.title = this.$t('LBL_SHORTCUT')
          this.editor.icon = 'mdi-bullseye-arrow'
          break
      }
      this.editor.visible = true
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
