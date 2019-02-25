window.modules = {
  Home: {
    path: 'src/modules/Home',
    entry: 'src/modules/Home/Home.vue',
    directories: ['pages', 'router'],
    routes: [
      {
        parent: 'Layout',
        path: '/home',
        componentPath: 'pages/Home.vue'
      }
    ]
  },
  ModuleExample: {
    path: 'src/modules/ModuleExample',
    entry: 'src/modules/ModuleExample/ModuleExample.vue',
    directories: ['pages', 'router'],
    routes: [
      {
        parent: 'Layout',
        path: '/module-example',
        componentPath: 'pages/ModuleExample.vue'
      }
    ]
  }
}
