export default [
  {
    name: 'Base.Basic',
    parent: 'Base',
    path: 'basic',
    componentPath: 'layouts/Basic',
    redirect: 'basic/list',
    children: [
      {
        name: 'Base.Basic.List',
        path: 'list',
        componentPath: 'views/List/List'
      },
      {
        name: 'Base.Basic.Detail',
        path: 'detail',
        componentPath: 'views/Detail/Detail'
      }
    ]
  }
]
