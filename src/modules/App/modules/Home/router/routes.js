export default [
  {
    name: 'App.HomeIndex',
    parent: 'App',
    path: 'home',
    componentPath: '/pages/Index.vue',
    children: [
      {
        name: 'App.HomeIndex.Home',
        path: '/',
        componentPath: 'pages/Home.vue'
      }
    ]
  }
]
