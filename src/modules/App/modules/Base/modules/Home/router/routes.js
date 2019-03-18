export default [
  {
    name: 'App.Base.HomeIndex',
    parent: 'App.Base',
    path: 'home',
    componentPath: '/pages/Index.vue',
    children: [
      {
        name: 'App.Base.HomeIndex.Home',
        path: '',
        componentPath: 'pages/Home.vue'
      }
    ]
  }
]
