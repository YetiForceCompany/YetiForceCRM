export default [
  {
    name: 'Base.HomeIndex',
    parent: 'Base',
    path: 'home',
    componentPath: '/pages/Index.vue',
    children: [
      {
        name: 'Base.HomeIndex.Home',
        path: '',
        componentPath: 'pages/Home.vue'
      }
    ]
  }
]
