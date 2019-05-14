/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

<template>
  <div class="q-pa-md">
    <q-layout view="hHh lpr fFf" container style="height: 500px" class="shadow-2 rounded-borders">
      <q-header elevated class="bg-primary text-white">
        <q-toolbar>
          <q-btn dense flat round icon="mdi-menu" @click="left = !left"></q-btn>
          <q-toolbar-title>
            Title
          </q-toolbar-title>
        </q-toolbar>
      </q-header>
      <q-drawer v-model="left" side="left" bordered :width="200" :breakpoint="700">
        <q-scroll-area class="fit">
          <q-tree :nodes="testData" node-key="label"></q-tree>
        </q-scroll-area>
      </q-drawer>
      <q-page-container>
        <q-page style="padding-top: 60px" class="q-pa-md"> </q-page>
      </q-page-container>
    </q-layout>
  </div>
</template>
<script>
export default {
  name: 'TreeView',
  data() {
    return {
      left: true,
      testData: [
        {
          label: 'Satisfied customers (with avatar)',
          children: [
            {
              label: 'Good food (with icon)',
              children: [
                {
                  label: 'Quality ingredients',
                  children: [
                    {
                      label: 'Good food (with icon)',
                      children: [{ label: 'Quality ingredients' }, { label: 'Good recipe' }]
                    },
                    {
                      label: 'Good service (disabled node with icon)',
                      disabled: true,
                      children: [{ label: 'Prompt attention' }, { label: 'Professional waiter' }]
                    },
                    {
                      label: 'Pleasant surroundings (with icon)',
                      children: [
                        {
                          label: 'Happy atmosphere (with image)'
                        },
                        { label: 'Good table presentation' },
                        { label: 'Pleasing decor' }
                      ]
                    }
                  ]
                },
                { label: 'Good recipe' }
              ]
            },
            {
              label: 'Good service (disabled node with icon)',
              disabled: true,
              children: [{ label: 'Prompt attention' }, { label: 'Professional waiter' }]
            },
            {
              label: 'Pleasant surroundings (with icon)',
              children: [
                {
                  label: 'Happy atmosphere (with image)'
                },
                { label: 'Good table presentation' },
                { label: 'Pleasing decor' }
              ]
            }
          ]
        }
      ]
    }
  }
}
</script>
<style scoped>
</style>
