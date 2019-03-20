window.modules = [
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
        path: "/",
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
    fullName: "Core",
    name: "Core",
    path: "src\\modules\\Core",
    level: 0,
    parent: "",
    priority: 95,
    autoLoad: true,
    childrenPriority: 90,
    entry: "src\\modules\\Core\\Core.vue",
    directories: ["layouts", "modules", "router", "store"],
    routes: [
      {
        name: "Core",
        parent: "App",
        path: "/",
        componentPath: "layouts/Core.vue"
      }
    ],
    store: {
      actions: {},
      getters: {},
      mutations: {}
    },
    storeFiles: {
      actions: "src\\modules\\Core\\store\\actions.js",
      getters: "src\\modules\\Core\\store\\getters.js",
      mutations: "src\\modules\\Core\\store\\mutations.js",
      state: "src\\modules\\Core\\store\\state.js"
    },
    modules: [
      {
        parentHierarchy: "Core",
        fullName: "Core.Debug",
        name: "Debug",
        path: "src\\modules\\Core\\modules\\Debug",
        level: 1,
        priority: 90,
        autoLoad: false,
        entry: "src\\modules\\Core\\modules\\Debug\\Debug.vue",
        directories: ["store"],
        store: {
          getters: {
            get: "Core/Debug/get"
          },
          mutations: {
            push: "Core/Debug/push"
          }
        },
        storeFiles: {
          getters: "src\\modules\\Core\\modules\\Debug\\store\\getters.js",
          mutations: "src\\modules\\Core\\modules\\Debug\\store\\mutations.js",
          state: "src\\modules\\Core\\modules\\Debug\\store\\state.js"
        }
      },
      {
        parentHierarchy: "Core",
        fullName: "Core.Env",
        name: "Env",
        path: "src\\modules\\Core\\modules\\Env",
        level: 1,
        priority: 90,
        autoLoad: true,
        entry: "src\\modules\\Core\\modules\\Env\\Env.vue",
        directories: ["store"],
        store: {
          getters: {
            all: "Core/Env/all"
          },
          mutations: {
            update: "Core/Env/update"
          }
        },
        storeFiles: {
          getters: "src\\modules\\Core\\modules\\Env\\store\\getters.js",
          mutations: "src\\modules\\Core\\modules\\Env\\store\\mutations.js",
          state: "src\\modules\\Core\\modules\\Env\\store\\state.js"
        }
      },
      {
        parentHierarchy: "Core",
        fullName: "Core.Hooks",
        name: "Hooks",
        path: "src\\modules\\Core\\modules\\Hooks",
        level: 1,
        priority: 90,
        autoLoad: true,
        entry: "src\\modules\\Core\\modules\\Hooks\\Hooks.vue",
        directories: ["components", "store"],
        store: {
          getters: {
            get: "Core/Hooks/get"
          },
          mutations: {
            add: "Core/Hooks/add",
            remove: "Core/Hooks/remove"
          }
        },
        storeFiles: {
          getters: "src\\modules\\Core\\modules\\Hooks\\store\\getters.js",
          mutations: "src\\modules\\Core\\modules\\Hooks\\store\\mutations.js",
          state: "src\\modules\\Core\\modules\\Hooks\\store\\state.js"
        }
      },
      {
        parentHierarchy: "Core",
        fullName: "Core.Language",
        name: "Language",
        path: "src\\modules\\Core\\modules\\Language",
        level: 1,
        priority: 90,
        autoLoad: true,
        entry: "src\\modules\\Core\\modules\\Language\\Language.vue",
        directories: ["store"],
        store: {
          mutations: {
            update: "Core/Language/update"
          }
        },
        storeFiles: {
          mutations:
            "src\\modules\\Core\\modules\\Language\\store\\mutations.js",
          state: "src\\modules\\Core\\modules\\Language\\store\\state.js"
        }
      },
      {
        parentHierarchy: "Core",
        fullName: "Core.Menu",
        name: "Menu",
        path: "src\\modules\\Core\\modules\\Menu",
        level: 1,
        priority: 99,
        autoLoad: true,
        entry: "src\\modules\\Core\\modules\\Menu\\Menu.vue",
        directories: ["components", "store"],
        store: {
          getters: {
            items: "Core/Menu/items"
          },
          mutations: {
            updateItems: "Core/Menu/updateItems",
            addItem: "Core/Menu/addItem"
          }
        },
        storeFiles: {
          getters: "src\\modules\\Core\\modules\\Menu\\store\\getters.js",
          mutations: "src\\modules\\Core\\modules\\Menu\\store\\mutations.js",
          state: "src\\modules\\Core\\modules\\Menu\\store\\state.js"
        }
      },
      {
        parentHierarchy: "Core",
        fullName: "Core.Url",
        name: "Url",
        path: "src\\modules\\Core\\modules\\Url",
        level: 1,
        priority: 90,
        autoLoad: true,
        entry: "src\\modules\\Core\\modules\\Url\\Url.vue",
        directories: ["store"],
        store: {
          getters: {
            get: "Core/Url/get"
          },
          mutations: {
            addUrl: "Core/Url/addUrl"
          }
        },
        storeFiles: {
          getters: "src\\modules\\Core\\modules\\Url\\store\\getters.js",
          mutations: "src\\modules\\Core\\modules\\Url\\store\\mutations.js",
          state: "src\\modules\\Core\\modules\\Url\\store\\state.js"
        }
      },
      {
        parentHierarchy: "Core",
        fullName: "Core.Users",
        name: "Users",
        path: "src\\modules\\Core\\modules\\Users",
        level: 1,
        priority: 90,
        autoLoad: true,
        childrenPriority: 85,
        entry: "src\\modules\\Core\\modules\\Users\\Users.vue",
        directories: ["layouts", "pages", "router", "store"],
        routes: [
          {
            parent: "Core",
            name: "Core.Users.Login",
            path: "users/login",
            redirect: "users/login/form",
            componentPath: "layouts/Login.vue",
            children: [
              {
                name: "Core.Users.Login.LoginForm",
                path: "form",
                meta: {
                  module: "Core.Users",
                  view: "Login"
                },
                componentPath: "pages/Login/Form.vue"
              },
              {
                name: "Core.Users.Login.2FA",
                path: "2fa",
                meta: {
                  module: "Core.Users",
                  view: "Login"
                },
                componentPath: "pages/Login/2FA.vue"
              },
              {
                name: "Core.Users.Login.Reminder",
                path: "reminder",
                meta: {
                  module: "Core.Users",
                  view: "Login"
                },
                componentPath: "pages/Login/Reminder.vue"
              }
            ]
          }
        ],
        store: {
          actions: {
            fetchData: "Core/Users/fetchData",
            login: "Core/Users/login",
            logout: "Core/Users/logout",
            remind: "Core/Users/remind"
          },
          getters: {
            isLoggedIn: "Core/Users/isLoggedIn",
            getMessage: "Core/Users/getMessage"
          },
          mutations: {
            isLoggedIn: "Core/Users/isLoggedIn",
            setMessage: "Core/Users/setMessage"
          }
        },
        storeFiles: {
          actions: "src\\modules\\Core\\modules\\Users\\store\\actions.js",
          getters: "src\\modules\\Core\\modules\\Users\\store\\getters.js",
          mutations: "src\\modules\\Core\\modules\\Users\\store\\mutations.js",
          state: "src\\modules\\Core\\modules\\Users\\store\\state.js"
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
