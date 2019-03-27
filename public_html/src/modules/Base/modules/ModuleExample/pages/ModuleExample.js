//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

import getters from '/src/store/getters.js';
import mutations from '/src/store/mutations.js';

var moduleName = 'Base.ModuleExample.Pages.ModuleExample';
var __script__ = {
  name: moduleName,
  data: function data() {
    return {
      selected: [],
      columns: [{
        name: 'desc',
        required: true,
        label: 'Dessert (100g serving)',
        align: 'left',
        field: function field(row) {
          return row.name;
        },
        format: function format(val) {
          return '' + val;
        },
        sortable: true
      }, { name: 'calories', align: 'center', label: 'Calories', field: 'calories', sortable: true }, { name: 'fat', label: 'Fat (g)', field: 'fat', sortable: true }, { name: 'carbs', label: 'Carbs (g)', field: 'carbs' }, { name: 'protein', label: 'Protein (g)', field: 'protein' }, { name: 'sodium', label: 'Sodium (mg)', field: 'sodium' }, {
        name: 'calcium',
        label: 'Calcium (%)',
        field: 'calcium',
        sortable: true,
        sort: function sort(a, b) {
          return parseInt(a, 10) - parseInt(b, 10);
        }
      }, {
        name: 'iron',
        label: 'Iron (%)',
        field: 'iron',
        sortable: true,
        sort: function sort(a, b) {
          return parseInt(a, 10) - parseInt(b, 10);
        }
      }],
      data: [{
        name: 'Frozen Yogurt',
        calories: 159,
        fat: 6.0,
        carbs: 24,
        protein: 4.0,
        sodium: 87,
        calcium: '14%',
        iron: '1%'
      }, {
        name: 'Ice cream sandwich',
        calories: 237,
        fat: 9.0,
        carbs: 37,
        protein: 4.3,
        sodium: 129,
        calcium: '8%',
        iron: '1%'
      }, {
        name: 'Eclair',
        calories: 262,
        fat: 16.0,
        carbs: 23,
        protein: 6.0,
        sodium: 337,
        calcium: '6%',
        iron: '7%'
      }, {
        name: 'Cupcake',
        calories: 305,
        fat: 3.7,
        carbs: 67,
        protein: 4.3,
        sodium: 413,
        calcium: '3%',
        iron: '8%'
      }, {
        name: 'Gingerbread',
        calories: 356,
        fat: 16.0,
        carbs: 49,
        protein: 3.9,
        sodium: 327,
        calcium: '7%',
        iron: '16%'
      }, {
        name: 'Jelly bean',
        calories: 375,
        fat: 0.0,
        carbs: 94,
        protein: 0.0,
        sodium: 50,
        calcium: '0%',
        iron: '0%'
      }, {
        name: 'Lollipop',
        calories: 392,
        fat: 0.2,
        carbs: 98,
        protein: 0,
        sodium: 38,
        calcium: '0%',
        iron: '2%'
      }, {
        name: 'Honeycomb',
        calories: 408,
        fat: 3.2,
        carbs: 87,
        protein: 6.5,
        sodium: 562,
        calcium: '0%',
        iron: '45%'
      }, {
        name: 'Donut',
        calories: 452,
        fat: 25.0,
        carbs: 51,
        protein: 4.9,
        sodium: 326,
        calcium: '2%',
        iron: '22%'
      }, {
        name: 'KitKat',
        calories: 518,
        fat: 26.0,
        carbs: 65,
        protein: 7,
        sodium: 54,
        calcium: '12%',
        iron: '6%'
      }]
    };
  },

  methods: {
    updateVariable: function updateVariable() {
      this.$store.commit(mutations.Base.ModuleExample.updateTestVariable, 'changed!');
    },
    getSelectedString: function getSelectedString() {
      return this.selected.length === 0 ? '' : this.selected.length + ' record' + (this.selected.length > 1 ? 's' : '') + ' selected of ' + this.data.length;
    }
  },
  computed: Object.assign({}, Vuex.mapGetters({
    testVariable: getters.Base.ModuleExample.testVariable
  }))
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('div', [_c('q-table', {
    attrs: {
      "title": "Treats",
      "data": _vm.data,
      "columns": _vm.columns,
      "row-key": "name",
      "selected-rows-label": _vm.getSelectedString,
      "selection": "multiple",
      "selected": _vm.selected
    },
    on: {
      "update:selected": function updateSelected($event) {
        _vm.selected = $event;
      }
    }
  })], 1);
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);