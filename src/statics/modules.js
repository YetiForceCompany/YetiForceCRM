window.modules = [
  {
    parentHierarchy: "",
    fullName: "App",
    name: "App",
    path: "src\\modules\\App",
    level: 0,
    parent: "",
    priority: 0,
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
      getters: {},
      mutations: {}
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
        fullName: "App.Home",
        name: "Home",
        path: "src\\modules\\App\\modules\\Home",
        level: 1,
        priority: 0,
        entry: "src\\modules\\App\\modules\\Home\\Home.vue",
        directories: ["pages", "router"],
        routes: [
          {
            name: "App.HomeIndex",
            parent: "App",
            path: "home",
            componentPath: "/pages/Index.vue",
            children: [
              {
                name: "App.HomeIndex.Home",
                path: "/",
                componentPath: "pages/Home.vue"
              }
            ]
          }
        ]
      },
      {
        parentHierarchy: "App",
        fullName: "App.ModuleExample",
        name: "ModuleExample",
        path: "src\\modules\\App\\modules\\ModuleExample",
        level: 1,
        priority: 0,
        entry: "src\\modules\\App\\modules\\ModuleExample\\ModuleExample.vue",
        directories: ["pages", "router", "store"],
        routes: [
          {
            name: "App.ModuleExample",
            parent: "App",
            path: "module-example",
            componentPath: "pages/ModuleExample.vue"
          }
        ],
        store: {
          actions: {
            getData: "App/ModuleExample/getData"
          },
          getters: {
            testVariable: "App/ModuleExample/testVariable"
          },
          mutations: {
            updateTestVariable: "App/ModuleExample/updateTestVariable"
          }
        },
        storeFiles: {
          actions:
            "src\\modules\\App\\modules\\ModuleExample\\store\\actions.js",
          getters:
            "src\\modules\\App\\modules\\ModuleExample\\store\\getters.js",
          mutations:
            "src\\modules\\App\\modules\\ModuleExample\\store\\mutations.js",
          state: "src\\modules\\App\\modules\\ModuleExample\\store\\state.js"
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
    priority: 100,
    entry: "src\\modules\\Base\\Base.vue",
    directories: ["layouts", "modules", "router", "store"],
    routes: [
      {
        name: "Base",
        parent: "Layout",
        path: "/base",
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
        fullName: "Base.Debug",
        name: "Debug",
        path: "src\\modules\\Base\\modules\\Debug",
        level: 1,
        priority: 100,
        entry: "src\\modules\\Base\\modules\\Debug\\Debug.vue",
        directories: ["store"],
        store: {
          mutations: {
            pushError: "Base/Debug/pushError"
          }
        },
        storeFiles: {
          mutations: "src\\modules\\Base\\modules\\Debug\\store\\mutations.js",
          state: "src\\modules\\Base\\modules\\Debug\\store\\state.js"
        }
      },
      {
        parentHierarchy: "Base",
        fullName: "Base.Env",
        name: "Env",
        path: "src\\modules\\Base\\modules\\Env",
        level: 1,
        priority: 100,
        entry: "src\\modules\\Base\\modules\\Env\\Env.vue",
        directories: ["store"],
        store: {
          getters: {
            all: "Base/Env/all"
          },
          mutations: {
            update: "Base/Env/update"
          }
        },
        storeFiles: {
          getters: "src\\modules\\Base\\modules\\Env\\store\\getters.js",
          mutations: "src\\modules\\Base\\modules\\Env\\store\\mutations.js",
          state: "src\\modules\\Base\\modules\\Env\\store\\state.js"
        }
      },
      {
        parentHierarchy: "Base",
        fullName: "Base.Hooks",
        name: "Hooks",
        path: "src\\modules\\Base\\modules\\Hooks",
        level: 1,
        priority: 100,
        entry: "src\\modules\\Base\\modules\\Hooks\\Hooks.vue",
        directories: ["store"],
        store: {
          getters: {
            get: "Base/Hooks/get"
          },
          mutations: {
            add: "Base/Hooks/add",
            remove: "Base/Hooks/remove"
          }
        },
        storeFiles: {
          getters: "src\\modules\\Base\\modules\\Hooks\\store\\getters.js",
          mutations: "src\\modules\\Base\\modules\\Hooks\\store\\mutations.js",
          state: "src\\modules\\Base\\modules\\Hooks\\store\\state.js"
        }
      },
      {
        parentHierarchy: "Base",
        fullName: "Base.Language",
        name: "Language",
        path: "src\\modules\\Base\\modules\\Language",
        level: 1,
        priority: 100,
        entry: "src\\modules\\Base\\modules\\Language\\Language.vue",
        directories: ["store"],
        store: {
          mutations: {
            update: "Base/Language/update"
          }
        },
        storeFiles: {
          mutations:
            "src\\modules\\Base\\modules\\Language\\store\\mutations.js",
          state: "src\\modules\\Base\\modules\\Language\\store\\state.js"
        }
      },
      {
        parentHierarchy: "Base",
        fullName: "Base.Menu",
        name: "Menu",
        path: "src\\modules\\Base\\modules\\Menu",
        level: 1,
        priority: 100,
        entry: "src\\modules\\Base\\modules\\Menu\\Menu.vue",
        directories: ["store"],
        store: {
          getters: {
            items: "Base/Menu/items"
          },
          mutations: {
            updateItems: "Base/Menu/updateItems",
            addItem: "Base/Menu/addItem"
          }
        },
        storeFiles: {
          getters: "src\\modules\\Base\\modules\\Menu\\store\\getters.js",
          mutations: "src\\modules\\Base\\modules\\Menu\\store\\mutations.js",
          state: "src\\modules\\Base\\modules\\Menu\\store\\state.js"
        }
      },
      {
        parentHierarchy: "Base",
        fullName: "Base.Url",
        name: "Url",
        path: "src\\modules\\Base\\modules\\Url",
        level: 1,
        priority: 100,
        entry: "src\\modules\\Base\\modules\\Url\\Url.vue",
        directories: ["store"],
        store: {
          getters: {
            get: "Base/Url/get"
          },
          mutations: {
            addUrl: "Base/Url/addUrl"
          }
        },
        storeFiles: {
          getters: "src\\modules\\Base\\modules\\Url\\store\\getters.js",
          mutations: "src\\modules\\Base\\modules\\Url\\store\\mutations.js",
          state: "src\\modules\\Base\\modules\\Url\\store\\state.js"
        }
      },
      {
        parentHierarchy: "Base",
        fullName: "Base.Users",
        name: "Users",
        path: "src\\modules\\Base\\modules\\Users",
        level: 1,
        priority: 100,
        entry: "src\\modules\\Base\\modules\\Users\\Users.vue",
        directories: ["layouts", "pages", "router", "store"],
        routes: [
          {
            parent: "Base",
            name: "Base.Users.Login",
            path: "users/login",
            redirect: "users/login/form",
            componentPath: "layouts/Login.vue",
            children: [
              {
                name: "Base.Users.Login.LoginForm",
                path: "form",
                meta: {
                  module: "Base.Users",
                  view: "Login"
                },
                componentPath: "pages/Login/Form.vue"
              },
              {
                name: "Base.Users.Login.2FA",
                path: "2fa",
                meta: {
                  module: "Base.Users",
                  view: "Login"
                },
                componentPath: "pages/Login/2FA.vue"
              },
              {
                name: "Base.Users.Login.Reminder",
                path: "reminder",
                meta: {
                  module: "Base.Users",
                  view: "Login"
                },
                componentPath: "pages/Login/Reminder.vue"
              }
            ]
          }
        ],
        store: {
          actions: {
            fetchData: "Base/Users/fetchData",
            login: "Base/Users/login",
            remind: "Base/Users/remind"
          },
          getters: {
            isLoggedIn: "Base/Users/isLoggedIn"
          },
          mutations: {
            isLoggedIn: "Base/Users/isLoggedIn"
          }
        },
        storeFiles: {
          actions: "src\\modules\\Base\\modules\\Users\\store\\actions.js",
          getters: "src\\modules\\Base\\modules\\Users\\store\\getters.js",
          mutations: "src\\modules\\Base\\modules\\Users\\store\\mutations.js",
          state: "src\\modules\\Base\\modules\\Users\\store\\state.js"
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
    entry: "src\\modules\\Settings\\Settings.vue",
    directories: ["layouts", "modules", "router", "store"],
    routes: [
      {
        name: "Settings",
        parent: "Layout",
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
