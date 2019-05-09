const moduleName = 'Accounts'
export default [
  {
    name: 'Base.' + moduleName,
    parent: 'Base',
    path: 'accounts',
    componentPath: '../Basic/layouts/Basic',
    redirect: 'accounts/list',
    children: [
      {
        name: 'Base.Basic.List',
        path: 'list',
        componentPath: '../Basic/views/List/List',
        meta: { moduleName: moduleName }
      },
      { name: 'Base.Basic.Detail', path: 'detail', componentPath: '../Basic/views/Detail/Detail' }
    ]
  }
]
