/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

<template>
  <div>
    <q-tree :nodes="simple" node-key="label"></q-tree>
  </div>
</template>
<script>
export default {
  name: 'TreeView',
  components: {
    'q-tree': window.Quasar.components.QTree
  },
  data() {
    return {
      simple: [
        {
          label: 'Satisfied customers (with avatar)',
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
        }
      ]
    };
  }
};
</script>
