window.modules = [
  {
    parentHierarchy: "",
    fullName: "App",
    name: "App",
    path: "src\\modules\\App",
    level: 0,
    parent: "",
    priority: 100,
    autoLoad: false,
    entry: "src\\modules\\App\\App.vue",
    directories: ["layouts", "modules", "router", "store"],
    routes: [
      {
        name: "App",
        parent: "Layout",
        path: "app",
        componentPath: "layouts/App.vue"
      }
    ],
    store: {
      actions: {},
      getters: {
        get: "App/get",
        all: "App/all"
      },
      mutations: {
        setModules: "App/setModules"
      }
    },
    storeFiles: {
      actions: "src\\modules\\App\\store\\actions.js",
      getters: "src\\modules\\App\\store\\getters.js",
      mutations: "src\\modules\\App\\store\\mutations.js",
      state: "src\\modules\\App\\store\\state.js"
    },
    modules: [
      {
        parentHierarchy: "App",
        fullName: "App.Base",
        name: "Base",
        path: "src\\modules\\App\\modules\\Base",
        level: 1,
        priority: 0,
        autoLoad: true,
        entry: "src\\modules\\App\\modules\\Base\\Base.vue",
        directories: ["layouts", "modules", "router", "store"],
        routes: [
          {
            name: "App.Base",
            parent: "App",
            path: "base",
            componentPath: "layouts/Base.vue"
          }
        ],
        store: {
          actions: {},
          getters: {},
          mutations: {}
        },
        storeFiles: {
          actions: "src\\modules\\App\\modules\\Base\\store\\actions.js",
          getters: "src\\modules\\App\\modules\\Base\\store\\getters.js",
          mutations: "src\\modules\\App\\modules\\Base\\store\\mutations.js",
          state: "src\\modules\\App\\modules\\Base\\store\\state.js"
        },
        modules: [
          {
            parentHierarchy: "App.Base",
            fullName: "App.Base.Home",
            name: "Home",
            path: "src\\modules\\App\\modules\\Base\\modules\\Home",
            level: 2,
            priority: 0,
            autoLoad: true,
            entry: "src\\modules\\App\\modules\\Base\\modules\\Home\\Home.vue",
            directories: ["pages", "router"],
            routes: [
              {
                name: "App.Base.HomeIndex",
                parent: "App.Base",
                path: "home",
                componentPath: "/pages/Index.vue",
                children: [
                  {
                    name: "App.Base.HomeIndex.Home",
                    path: "",
                    componentPath: "pages/Home.vue"
                  }
                ]
              }
            ]
          },
          {
            parentHierarchy: "App.Base",
            fullName: "App.Base.ModuleExample",
            name: "ModuleExample",
            path: "src\\modules\\App\\modules\\Base\\modules\\ModuleExample",
            level: 2,
            priority: 0,
            autoLoad: true,
            entry:
              "src\\modules\\App\\modules\\Base\\modules\\ModuleExample\\ModuleExample.vue",
            directories: ["pages", "router", "store"],
            routes: [
              {
                name: "App.Base.ModuleExample",
                parent: "App.Base",
                path: "module-example",
                componentPath: "pages/ModuleExample.vue"
              }
            ],
            store: {
              actions: {
                getData: "App/Base/ModuleExample/getData"
              },
              getters: {
                testVariable: "App/Base/ModuleExample/testVariable"
              },
              mutations: {
                updateTestVariable: "App/Base/ModuleExample/updateTestVariable"
              }
            },
            storeFiles: {
              actions:
                "src\\modules\\App\\modules\\Base\\modules\\ModuleExample\\store\\actions.js",
              getters:
                "src\\modules\\App\\modules\\Base\\modules\\ModuleExample\\store\\getters.js",
              mutations:
                "src\\modules\\App\\modules\\Base\\modules\\ModuleExample\\store\\mutations.js",
              state:
                "src\\modules\\App\\modules\\Base\\modules\\ModuleExample\\store\\state.js"
            }
          }
        ]
      },
      {
        parentHierarchy: "App",
        fullName: "App.Core",
        name: "Core",
        path: "src\\modules\\App\\modules\\Core",
        level: 1,
        priority: 95,
        autoLoad: true,
        childrenPriority: 90,
        entry: "src\\modules\\App\\modules\\Core\\Core.vue",
        directories: ["layouts", "modules", "router", "store"],
        routes: [
          {
            name: "App.Core",
            parent: "App",
            path: "core",
            componentPath: "layouts/Core.vue"
          }
        ],
        store: {
          actions: {},
          getters: {},
          mutations: {}
        },
        storeFiles: {
          actions: "src\\modules\\App\\modules\\Core\\store\\actions.js",
          getters: "src\\modules\\App\\modules\\Core\\store\\getters.js",
          mutations: "src\\modules\\App\\modules\\Core\\store\\mutations.js",
          state: "src\\modules\\App\\modules\\Core\\store\\state.js"
        },
        modules: [
          {
            parentHierarchy: "App.Core",
            fullName: "App.Core.Debug",
            name: "Debug",
            path: "src\\modules\\App\\modules\\Core\\modules\\Debug",
            level: 2,
            priority: 90,
            autoLoad: false,
            entry:
              "src\\modules\\App\\modules\\Core\\modules\\Debug\\Debug.vue",
            directories: ["store"],
            store: {
              getters: {
                get: "App/Core/Debug/get"
              },
              mutations: {
                push: "App/Core/Debug/push"
              }
            },
            storeFiles: {
              getters:
                "src\\modules\\App\\modules\\Core\\modules\\Debug\\store\\getters.js",
              mutations:
                "src\\modules\\App\\modules\\Core\\modules\\Debug\\store\\mutations.js",
              state:
                "src\\modules\\App\\modules\\Core\\modules\\Debug\\store\\state.js"
            }
          },
          {
            parentHierarchy: "App.Core",
            fullName: "App.Core.Env",
            name: "Env",
            path: "src\\modules\\App\\modules\\Core\\modules\\Env",
            level: 2,
            priority: 90,
            autoLoad: true,
            entry: "src\\modules\\App\\modules\\Core\\modules\\Env\\Env.vue",
            directories: ["store"],
            store: {
              getters: {
                all: "App/Core/Env/all"
              },
              mutations: {
                update: "App/Core/Env/update"
              }
            },
            storeFiles: {
              getters:
                "src\\modules\\App\\modules\\Core\\modules\\Env\\store\\getters.js",
              mutations:
                "src\\modules\\App\\modules\\Core\\modules\\Env\\store\\mutations.js",
              state:
                "src\\modules\\App\\modules\\Core\\modules\\Env\\store\\state.js"
            }
          },
          {
            parentHierarchy: "App.Core",
            fullName: "App.Core.Hooks",
            name: "Hooks",
            path: "src\\modules\\App\\modules\\Core\\modules\\Hooks",
            level: 2,
            priority: 90,
            autoLoad: true,
            entry:
              "src\\modules\\App\\modules\\Core\\modules\\Hooks\\Hooks.vue",
            directories: ["components", "store"],
            store: {
              getters: {
                get: "App/Core/Hooks/get"
              },
              mutations: {
                add: "App/Core/Hooks/add",
                remove: "App/Core/Hooks/remove"
              }
            },
            storeFiles: {
              getters:
                "src\\modules\\App\\modules\\Core\\modules\\Hooks\\store\\getters.js",
              mutations:
                "src\\modules\\App\\modules\\Core\\modules\\Hooks\\store\\mutations.js",
              state:
                "src\\modules\\App\\modules\\Core\\modules\\Hooks\\store\\state.js"
            }
          },
          {
            parentHierarchy: "App.Core",
            fullName: "App.Core.Language",
            name: "Language",
            path: "src\\modules\\App\\modules\\Core\\modules\\Language",
            level: 2,
            priority: 90,
            autoLoad: true,
            entry:
              "src\\modules\\App\\modules\\Core\\modules\\Language\\Language.vue",
            directories: ["store"],
            store: {
              mutations: {
                update: "App/Core/Language/update"
              }
            },
            storeFiles: {
              mutations:
                "src\\modules\\App\\modules\\Core\\modules\\Language\\store\\mutations.js",
              state:
                "src\\modules\\App\\modules\\Core\\modules\\Language\\store\\state.js"
            }
          },
          {
            parentHierarchy: "App.Core",
            fullName: "App.Core.Menu",
            name: "Menu",
            path: "src\\modules\\App\\modules\\Core\\modules\\Menu",
            level: 2,
            priority: 99,
            autoLoad: true,
            entry: "src\\modules\\App\\modules\\Core\\modules\\Menu\\Menu.vue",
            directories: ["components", "store"],
            store: {
              getters: {
                items: "App/Core/Menu/items"
              },
              mutations: {
                updateItems: "App/Core/Menu/updateItems",
                addItem: "App/Core/Menu/addItem"
              }
            },
            storeFiles: {
              getters:
                "src\\modules\\App\\modules\\Core\\modules\\Menu\\store\\getters.js",
              mutations:
                "src\\modules\\App\\modules\\Core\\modules\\Menu\\store\\mutations.js",
              state:
                "src\\modules\\App\\modules\\Core\\modules\\Menu\\store\\state.js"
            }
          },
          {
            parentHierarchy: "App.Core",
            fullName: "App.Core.Url",
            name: "Url",
            path: "src\\modules\\App\\modules\\Core\\modules\\Url",
            level: 2,
            priority: 90,
            autoLoad: true,
            entry: "src\\modules\\App\\modules\\Core\\modules\\Url\\Url.vue",
            directories: ["store"],
            store: {
              getters: {
                get: "App/Core/Url/get"
              },
              mutations: {
                addUrl: "App/Core/Url/addUrl"
              }
            },
            storeFiles: {
              getters:
                "src\\modules\\App\\modules\\Core\\modules\\Url\\store\\getters.js",
              mutations:
                "src\\modules\\App\\modules\\Core\\modules\\Url\\store\\mutations.js",
              state:
                "src\\modules\\App\\modules\\Core\\modules\\Url\\store\\state.js"
            }
          },
          {
            parentHierarchy: "App.Core",
            fullName: "App.Core.Users",
            name: "Users",
            path: "src\\modules\\App\\modules\\Core\\modules\\Users",
            level: 2,
            priority: 90,
            autoLoad: true,
            childrenPriority: 85,
            entry:
              "src\\modules\\App\\modules\\Core\\modules\\Users\\Users.vue",
            directories: ["layouts", "pages", "router", "store"],
            routes: [
              {
                parent: "App.Core",
                name: "App.Core.Users.Login",
                path: "users/login",
                redirect: "users/login/form",
                componentPath: "layouts/Login.vue",
                children: [
                  {
                    name: "App.Core.Users.Login.LoginForm",
                    path: "form",
                    meta: {
                      module: "App.Core.Users",
                      view: "Login"
                    },
                    componentPath: "pages/Login/Form.vue"
                  },
                  {
                    name: "App.Core.Users.Login.2FA",
                    path: "2fa",
                    meta: {
                      module: "App.Core.Users",
                      view: "Login"
                    },
                    componentPath: "pages/Login/2FA.vue"
                  },
                  {
                    name: "App.Core.Users.Login.Reminder",
                    path: "reminder",
                    meta: {
                      module: "App.Core.Users",
                      view: "Login"
                    },
                    componentPath: "pages/Login/Reminder.vue"
                  }
                ]
              }
            ],
            store: {
              actions: {
                fetchData: "App/Core/Users/fetchData",
                login: "App/Core/Users/login",
                remind: "App/Core/Users/remind"
              },
              getters: {
                isLoggedIn: "App/Core/Users/isLoggedIn"
              },
              mutations: {
                isLoggedIn: "App/Core/Users/isLoggedIn"
              }
            },
            storeFiles: {
              actions:
                "src\\modules\\App\\modules\\Core\\modules\\Users\\store\\actions.js",
              getters:
                "src\\modules\\App\\modules\\Core\\modules\\Users\\store\\getters.js",
              mutations:
                "src\\modules\\App\\modules\\Core\\modules\\Users\\store\\mutations.js",
              state:
                "src\\modules\\App\\modules\\Core\\modules\\Users\\store\\state.js"
            }
          }
        ]
      },
      {
        parentHierarchy: "App",
        fullName: "App.Settings",
        name: "Settings",
        path: "src\\modules\\App\\modules\\Settings",
        level: 1,
        priority: 0,
        autoLoad: true,
        entry: "src\\modules\\App\\modules\\Settings\\Settings.vue",
        directories: ["layouts", "modules", "router", "store"],
        routes: [
          {
            name: "App.Settings",
            parent: "App",
            path: "settings",
            componentPath: "layouts/Settings.vue"
          }
        ],
        store: {
          actions: {},
          getters: {},
          mutations: {}
        },
        storeFiles: {
          actions: "src\\modules\\App\\modules\\Settings\\store\\actions.js",
          getters: "src\\modules\\App\\modules\\Settings\\store\\getters.js",
          mutations:
            "src\\modules\\App\\modules\\Settings\\store\\mutations.js",
          state: "src\\modules\\App\\modules\\Settings\\store\\state.js"
        },
        modules: [
          {
            parentHierarchy: "App.Settings",
            fullName: "App.Settings.ModuleExample",
            name: "ModuleExample",
            path:
              "src\\modules\\App\\modules\\Settings\\modules\\ModuleExample",
            level: 2,
            priority: 0,
            autoLoad: true,
            entry:
              "src\\modules\\App\\modules\\Settings\\modules\\ModuleExample\\ModuleExample.vue",
            directories: ["pages", "router"],
            routes: [
              {
                name: "App.Settings.ModuleExample",
                parent: "App.Settings",
                path: "module-example",
                componentPath: "pages/ModuleExample"
              }
            ]
          }
        ]
      }
    ]
  }
];
