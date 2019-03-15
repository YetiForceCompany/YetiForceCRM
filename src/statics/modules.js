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
    childrenPriority: 98,
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
        fullName: "App.Debug",
        name: "Debug",
        path: "src\\modules\\App\\modules\\Debug",
        level: 1,
        priority: 98,
        autoLoad: false,
        entry: "src\\modules\\App\\modules\\Debug\\Debug.vue",
        directories: ["store"],
        store: {
          getters: {
            get: "App/Debug/get"
          },
          mutations: {
            push: "App/Debug/push"
          }
        },
        storeFiles: {
          getters: "src\\modules\\App\\modules\\Debug\\store\\getters.js",
          mutations: "src\\modules\\App\\modules\\Debug\\store\\mutations.js",
          state: "src\\modules\\App\\modules\\Debug\\store\\state.js"
        }
      },
      {
        parentHierarchy: "App",
        fullName: "App.Env",
        name: "Env",
        path: "src\\modules\\App\\modules\\Env",
        level: 1,
        priority: 98,
        autoLoad: true,
        entry: "src\\modules\\App\\modules\\Env\\Env.vue",
        directories: ["store"],
        store: {
          getters: {
            all: "App/Env/all"
          },
          mutations: {
            update: "App/Env/update"
          }
        },
        storeFiles: {
          getters: "src\\modules\\App\\modules\\Env\\store\\getters.js",
          mutations: "src\\modules\\App\\modules\\Env\\store\\mutations.js",
          state: "src\\modules\\App\\modules\\Env\\store\\state.js"
        }
      },
      {
        parentHierarchy: "App",
        fullName: "App.Hooks",
        name: "Hooks",
        path: "src\\modules\\App\\modules\\Hooks",
        level: 1,
        priority: 98,
        autoLoad: true,
        entry: "src\\modules\\App\\modules\\Hooks\\Hooks.vue",
        directories: ["components", "store"],
        store: {
          getters: {
            get: "App/Hooks/get"
          },
          mutations: {
            add: "App/Hooks/add",
            remove: "App/Hooks/remove"
          }
        },
        storeFiles: {
          getters: "src\\modules\\App\\modules\\Hooks\\store\\getters.js",
          mutations: "src\\modules\\App\\modules\\Hooks\\store\\mutations.js",
          state: "src\\modules\\App\\modules\\Hooks\\store\\state.js"
        }
      },
      {
        parentHierarchy: "App",
        fullName: "App.Language",
        name: "Language",
        path: "src\\modules\\App\\modules\\Language",
        level: 1,
        priority: 98,
        autoLoad: true,
        entry: "src\\modules\\App\\modules\\Language\\Language.vue",
        directories: ["store"],
        store: {
          mutations: {
            update: "App/Language/update"
          }
        },
        storeFiles: {
          mutations:
            "src\\modules\\App\\modules\\Language\\store\\mutations.js",
          state: "src\\modules\\App\\modules\\Language\\store\\state.js"
        }
      },
      {
        parentHierarchy: "App",
        fullName: "App.Menu",
        name: "Menu",
        path: "src\\modules\\App\\modules\\Menu",
        level: 1,
        priority: 99,
        autoLoad: true,
        entry: "src\\modules\\App\\modules\\Menu\\Menu.vue",
        directories: ["components", "store"],
        store: {
          getters: {
            items: "App/Menu/items"
          },
          mutations: {
            updateItems: "App/Menu/updateItems",
            addItem: "App/Menu/addItem"
          }
        },
        storeFiles: {
          getters: "src\\modules\\App\\modules\\Menu\\store\\getters.js",
          mutations: "src\\modules\\App\\modules\\Menu\\store\\mutations.js",
          state: "src\\modules\\App\\modules\\Menu\\store\\state.js"
        }
      },
      {
        parentHierarchy: "App",
        fullName: "App.Url",
        name: "Url",
        path: "src\\modules\\App\\modules\\Url",
        level: 1,
        priority: 98,
        autoLoad: true,
        entry: "src\\modules\\App\\modules\\Url\\Url.vue",
        directories: ["store"],
        store: {
          getters: {
            get: "App/Url/get"
          },
          mutations: {
            addUrl: "App/Url/addUrl"
          }
        },
        storeFiles: {
          getters: "src\\modules\\App\\modules\\Url\\store\\getters.js",
          mutations: "src\\modules\\App\\modules\\Url\\store\\mutations.js",
          state: "src\\modules\\App\\modules\\Url\\store\\state.js"
        }
      },
      {
        parentHierarchy: "App",
        fullName: "App.Users",
        name: "Users",
        path: "src\\modules\\App\\modules\\Users",
        level: 1,
        priority: 98,
        autoLoad: true,
        entry: "src\\modules\\App\\modules\\Users\\Users.vue",
        directories: ["layouts", "pages", "router", "store"],
        routes: [
          {
            parent: "App",
            name: "App.Users.Login",
            path: "users/login",
            redirect: "users/login/form",
            componentPath: "layouts/Login.vue",
            children: [
              {
                name: "App.Users.Login.LoginForm",
                path: "form",
                meta: {
                  module: "App.Users",
                  view: "Login"
                },
                componentPath: "pages/Login/Form.vue"
              },
              {
                name: "App.Users.Login.2FA",
                path: "2fa",
                meta: {
                  module: "App.Users",
                  view: "Login"
                },
                componentPath: "pages/Login/2FA.vue"
              },
              {
                name: "App.Users.Login.Reminder",
                path: "reminder",
                meta: {
                  module: "App.Users",
                  view: "Login"
                },
                componentPath: "pages/Login/Reminder.vue"
              }
            ]
          }
        ],
        store: {
          actions: {
            fetchData: "App/Users/fetchData",
            login: "App/Users/login",
            remind: "App/Users/remind"
          },
          getters: {
            isLoggedIn: "App/Users/isLoggedIn"
          },
          mutations: {
            isLoggedIn: "App/Users/isLoggedIn"
          }
        },
        storeFiles: {
          actions: "src\\modules\\App\\modules\\Users\\store\\actions.js",
          getters: "src\\modules\\App\\modules\\Users\\store\\getters.js",
          mutations: "src\\modules\\App\\modules\\Users\\store\\mutations.js",
          state: "src\\modules\\App\\modules\\Users\\store\\state.js"
        }
      }
    ]
  },
  {
    parentHierarchy: "",
    fullName: "Base",
    name: "Base",
    path: "src\\modules\\Base",
    level: 0,
    parent: "",
    priority: 0,
    autoLoad: true,
    entry: "src\\modules\\Base\\Base.vue",
    directories: ["layouts", "modules", "router", "store"],
    routes: [
      {
        name: "Base",
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
      actions: "src\\modules\\Base\\store\\actions.js",
      getters: "src\\modules\\Base\\store\\getters.js",
      mutations: "src\\modules\\Base\\store\\mutations.js",
      state: "src\\modules\\Base\\store\\state.js"
    },
    modules: [
      {
        parentHierarchy: "Base",
        fullName: "Base.Home",
        name: "Home",
        path: "src\\modules\\Base\\modules\\Home",
        level: 1,
        priority: 0,
        autoLoad: true,
        entry: "src\\modules\\Base\\modules\\Home\\Home.vue",
        directories: ["pages", "router"],
        routes: [
          {
            name: "Base.HomeIndex",
            parent: "Base",
            path: "home",
            componentPath: "/pages/Index.vue",
            children: [
              {
                name: "Base.HomeIndex.Home",
                path: "",
                componentPath: "pages/Home.vue"
              }
            ]
          }
        ]
      },
      {
        parentHierarchy: "Base",
        fullName: "Base.ModuleExample",
        name: "ModuleExample",
        path: "src\\modules\\Base\\modules\\ModuleExample",
        level: 1,
        priority: 0,
        autoLoad: true,
        entry: "src\\modules\\Base\\modules\\ModuleExample\\ModuleExample.vue",
        directories: ["pages", "router", "store"],
        routes: [
          {
            name: "Base.ModuleExample",
            parent: "Base",
            path: "module-example",
            componentPath: "pages/ModuleExample.vue"
          }
        ],
        store: {
          actions: {
            getData: "Base/ModuleExample/getData"
          },
          getters: {
            testVariable: "Base/ModuleExample/testVariable"
          },
          mutations: {
            updateTestVariable: "Base/ModuleExample/updateTestVariable"
          }
        },
        storeFiles: {
          actions:
            "src\\modules\\Base\\modules\\ModuleExample\\store\\actions.js",
          getters:
            "src\\modules\\Base\\modules\\ModuleExample\\store\\getters.js",
          mutations:
            "src\\modules\\Base\\modules\\ModuleExample\\store\\mutations.js",
          state: "src\\modules\\Base\\modules\\ModuleExample\\store\\state.js"
        }
      }
    ]
  },
  {
    parentHierarchy: "",
    fullName: "Settings",
    name: "Settings",
    path: "src\\modules\\Settings",
    level: 0,
    parent: "",
    priority: 0,
    autoLoad: true,
    entry: "src\\modules\\Settings\\Settings.vue",
    directories: ["layouts", "modules", "router", "store"],
    routes: [
      {
        name: "Settings",
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
      actions: "src\\modules\\Settings\\store\\actions.js",
      getters: "src\\modules\\Settings\\store\\getters.js",
      mutations: "src\\modules\\Settings\\store\\mutations.js",
      state: "src\\modules\\Settings\\store\\state.js"
    },
    modules: [
      {
        parentHierarchy: "Settings",
        fullName: "Settings.ModuleExample",
        name: "ModuleExample",
        path: "src\\modules\\Settings\\modules\\ModuleExample",
        level: 1,
        priority: 0,
        autoLoad: true,
        entry:
          "src\\modules\\Settings\\modules\\ModuleExample\\ModuleExample.vue",
        directories: ["pages", "router"],
        routes: [
          {
            name: "Settings.ModuleExample",
            parent: "Settings",
            path: "module-example",
            componentPath: "pages/ModuleExample"
          }
        ]
      }
    ]
  }
];
