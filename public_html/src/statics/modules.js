window.modules = [
  {
    "parentHierarchy": "",
    "fullName": "Base",
    "name": "Base",
    "path": "src\\modules\\Base",
    "fullPath": "src\\modules\\Base",
    "level": 0,
    "parent": "",
    "priority": 0,
    "autoLoad": true,
    "entry": "src\\modules\\Base\\Base.js",
    "directories": [
      "layouts",
      "modules",
      "router",
      "store"
    ],
    "routes": [
      {
        "name": "Base",
        "parent": "App",
        "path": "/base",
        "componentPath": "layouts/Base"
      }
    ],
    "store": {
      "actions": {},
      "getters": {},
      "mutations": {}
    },
    "storeFiles": {
      "actions": "src\\modules\\Base\\store\\actions.js",
      "getters": "src\\modules\\Base\\store\\getters.js",
      "mutations": "src\\modules\\Base\\store\\mutations.js",
      "state": "src\\modules\\Base\\store\\state.js"
    },
    "entries": [
      "src/modules/Base/Base.js",
      "src/modules/Base/Base.vue",
      "src/modules/Base/layouts",
      "src/modules/Base/layouts/Base.js",
      "src/modules/Base/layouts/Base.vue",
      "src/modules/Base/router",
      "src/modules/Base/router/routes.js",
      "src/modules/Base/store",
      "src/modules/Base/store/actions.js",
      "src/modules/Base/store/getters.js",
      "src/modules/Base/store/index.js",
      "src/modules/Base/store/mutations.js",
      "src/modules/Base/store/state.js"
    ],
    "modules": [
      {
        "parentHierarchy": "Base",
        "fullName": "Base.Home",
        "name": "Home",
        "path": "src\\modules\\Base\\modules\\Home",
        "fullPath": "src\\modules\\Base\\modules\\Home",
        "level": 1,
        "priority": 0,
        "autoLoad": true,
        "entry": "src\\modules\\Base\\modules\\Home\\Home.js",
        "directories": [
          "pages",
          "router"
        ],
        "routes": [
          {
            "name": "Base.HomeIndex",
            "parent": "Base",
            "path": "home",
            "componentPath": "/pages/Index",
            "children": [
              {
                "name": "Base.HomeIndex.Home",
                "path": "",
                "componentPath": "pages/Home"
              }
            ]
          }
        ],
        "entries": [
          "src/modules/Base/modules/Home/Home.js",
          "src/modules/Base/modules/Home/Home.vue",
          "src/modules/Base/modules/Home/pages",
          "src/modules/Base/modules/Home/pages/Home.js",
          "src/modules/Base/modules/Home/pages/Home.vue",
          "src/modules/Base/modules/Home/router",
          "src/modules/Base/modules/Home/router/routes.js"
        ]
      },
      {
        "parentHierarchy": "Base",
        "fullName": "Base.ModuleExample",
        "name": "ModuleExample",
        "path": "src\\modules\\Base\\modules\\ModuleExample",
        "fullPath": "src\\modules\\Base\\modules\\ModuleExample",
        "level": 1,
        "priority": 0,
        "autoLoad": true,
        "entry": "src\\modules\\Base\\modules\\ModuleExample\\ModuleExample.js",
        "directories": [
          "pages",
          "router",
          "store"
        ],
        "routes": [
          {
            "name": "Base.ModuleExample",
            "parent": "Base",
            "path": "module-example",
            "componentPath": "pages/ModuleExample"
          }
        ],
        "store": {
          "actions": {
            "getData": "Base/ModuleExample/getData"
          },
          "getters": {
            "testVariable": "Base/ModuleExample/testVariable"
          },
          "mutations": {
            "updateTestVariable": "Base/ModuleExample/updateTestVariable"
          }
        },
        "storeFiles": {
          "actions": "src\\modules\\Base\\modules\\ModuleExample\\store\\actions.js",
          "getters": "src\\modules\\Base\\modules\\ModuleExample\\store\\getters.js",
          "mutations": "src\\modules\\Base\\modules\\ModuleExample\\store\\mutations.js",
          "state": "src\\modules\\Base\\modules\\ModuleExample\\store\\state.js"
        },
        "entries": [
          "src/modules/Base/modules/ModuleExample/ModuleExample.js",
          "src/modules/Base/modules/ModuleExample/ModuleExample.vue",
          "src/modules/Base/modules/ModuleExample/pages",
          "src/modules/Base/modules/ModuleExample/pages/ModuleExample.js",
          "src/modules/Base/modules/ModuleExample/pages/ModuleExample.vue",
          "src/modules/Base/modules/ModuleExample/router",
          "src/modules/Base/modules/ModuleExample/router/routes.js",
          "src/modules/Base/modules/ModuleExample/store",
          "src/modules/Base/modules/ModuleExample/store/actions.js",
          "src/modules/Base/modules/ModuleExample/store/getters.js",
          "src/modules/Base/modules/ModuleExample/store/index.js",
          "src/modules/Base/modules/ModuleExample/store/mutations.js",
          "src/modules/Base/modules/ModuleExample/store/state.js"
        ]
      }
    ]
  },
  {
    "parentHierarchy": "",
    "fullName": "Core",
    "name": "Core",
    "path": "src\\modules\\Core",
    "fullPath": "src\\modules\\Core",
    "level": 0,
    "parent": "",
    "priority": 100,
    "autoLoad": true,
    "childrenPriority": 90,
    "entry": "src\\modules\\Core\\Core.js",
    "directories": [
      "layouts",
      "modules",
      "router",
      "store"
    ],
    "routes": [
      {
        "name": "Core",
        "parent": "App",
        "path": "/core",
        "componentPath": "layouts/Core"
      }
    ],
    "store": {
      "actions": {},
      "getters": {},
      "mutations": {}
    },
    "storeFiles": {
      "actions": "src\\modules\\Core\\store\\actions.js",
      "getters": "src\\modules\\Core\\store\\getters.js",
      "mutations": "src\\modules\\Core\\store\\mutations.js",
      "state": "src\\modules\\Core\\store\\state.js"
    },
    "entries": [
      "src/modules/Core/Core.js",
      "src/modules/Core/Core.vue",
      "src/modules/Core/layouts",
      "src/modules/Core/layouts/Core.js",
      "src/modules/Core/layouts/Core.vue",
      "src/modules/Core/module.config.json",
      "src/modules/Core/router",
      "src/modules/Core/router/routes.js",
      "src/modules/Core/store",
      "src/modules/Core/store/actions.js",
      "src/modules/Core/store/getters.js",
      "src/modules/Core/store/index.js",
      "src/modules/Core/store/mutations.js",
      "src/modules/Core/store/state.js"
    ],
    "modules": [
      {
        "parentHierarchy": "Core",
        "fullName": "Core.Debug",
        "name": "Debug",
        "path": "src\\modules\\Core\\modules\\Debug",
        "fullPath": "src\\modules\\Core\\modules\\Debug",
        "level": 1,
        "priority": 99,
        "autoLoad": true,
        "entry": "src\\modules\\Core\\modules\\Debug\\Debug.js",
        "directories": [
          "store"
        ],
        "store": {
          "getters": {
            "get": "Core/Debug/get"
          },
          "mutations": {
            "push": "Core/Debug/push"
          }
        },
        "storeFiles": {
          "getters": "src\\modules\\Core\\modules\\Debug\\store\\getters.js",
          "mutations": "src\\modules\\Core\\modules\\Debug\\store\\mutations.js",
          "state": "src\\modules\\Core\\modules\\Debug\\store\\state.js"
        },
        "entries": [
          "src/modules/Core/modules/Debug/Debug.js",
          "src/modules/Core/modules/Debug/Debug.vue",
          "src/modules/Core/modules/Debug/module.config.json",
          "src/modules/Core/modules/Debug/store",
          "src/modules/Core/modules/Debug/store/getters.js",
          "src/modules/Core/modules/Debug/store/index.js",
          "src/modules/Core/modules/Debug/store/mutations.js",
          "src/modules/Core/modules/Debug/store/state.js"
        ]
      },
      {
        "parentHierarchy": "Core",
        "fullName": "Core.Env",
        "name": "Env",
        "path": "src\\modules\\Core\\modules\\Env",
        "fullPath": "src\\modules\\Core\\modules\\Env",
        "level": 1,
        "priority": 90,
        "autoLoad": true,
        "entry": "src\\modules\\Core\\modules\\Env\\Env.js",
        "directories": [
          "store"
        ],
        "store": {
          "getters": {
            "all": "Core/Env/all"
          },
          "mutations": {
            "update": "Core/Env/update"
          }
        },
        "storeFiles": {
          "getters": "src\\modules\\Core\\modules\\Env\\store\\getters.js",
          "mutations": "src\\modules\\Core\\modules\\Env\\store\\mutations.js",
          "state": "src\\modules\\Core\\modules\\Env\\store\\state.js"
        },
        "entries": [
          "src/modules/Core/modules/Env/Env.js",
          "src/modules/Core/modules/Env/Env.vue",
          "src/modules/Core/modules/Env/store",
          "src/modules/Core/modules/Env/store/getters.js",
          "src/modules/Core/modules/Env/store/index.js",
          "src/modules/Core/modules/Env/store/mutations.js",
          "src/modules/Core/modules/Env/store/state.js"
        ]
      },
      {
        "parentHierarchy": "Core",
        "fullName": "Core.Hooks",
        "name": "Hooks",
        "path": "src\\modules\\Core\\modules\\Hooks",
        "fullPath": "src\\modules\\Core\\modules\\Hooks",
        "level": 1,
        "priority": 96,
        "autoLoad": true,
        "entry": "src\\modules\\Core\\modules\\Hooks\\Hooks.js",
        "directories": [
          "components",
          "store"
        ],
        "store": {
          "getters": {
            "get": "Core/Hooks/get"
          },
          "mutations": {
            "add": "Core/Hooks/add",
            "remove": "Core/Hooks/remove"
          }
        },
        "storeFiles": {
          "getters": "src\\modules\\Core\\modules\\Hooks\\store\\getters.js",
          "mutations": "src\\modules\\Core\\modules\\Hooks\\store\\mutations.js",
          "state": "src\\modules\\Core\\modules\\Hooks\\store\\state.js"
        },
        "entries": [
          "src/modules/Core/modules/Hooks/components",
          "src/modules/Core/modules/Hooks/components/Hook.js",
          "src/modules/Core/modules/Hooks/components/Hook.vue",
          "src/modules/Core/modules/Hooks/components/HookWrapper.js",
          "src/modules/Core/modules/Hooks/components/HookWrapper.vue",
          "src/modules/Core/modules/Hooks/Hooks.js",
          "src/modules/Core/modules/Hooks/Hooks.vue",
          "src/modules/Core/modules/Hooks/module.config.json",
          "src/modules/Core/modules/Hooks/store",
          "src/modules/Core/modules/Hooks/store/getters.js",
          "src/modules/Core/modules/Hooks/store/index.js",
          "src/modules/Core/modules/Hooks/store/mutations.js",
          "src/modules/Core/modules/Hooks/store/state.js"
        ]
      },
      {
        "parentHierarchy": "Core",
        "fullName": "Core.Language",
        "name": "Language",
        "path": "src\\modules\\Core\\modules\\Language",
        "fullPath": "src\\modules\\Core\\modules\\Language",
        "level": 1,
        "priority": 90,
        "autoLoad": true,
        "entry": "src\\modules\\Core\\modules\\Language\\Language.js",
        "directories": [
          "store"
        ],
        "store": {
          "mutations": {
            "update": "Core/Language/update"
          }
        },
        "storeFiles": {
          "mutations": "src\\modules\\Core\\modules\\Language\\store\\mutations.js",
          "state": "src\\modules\\Core\\modules\\Language\\store\\state.js"
        },
        "entries": [
          "src/modules/Core/modules/Language/Language.js",
          "src/modules/Core/modules/Language/Language.vue",
          "src/modules/Core/modules/Language/store",
          "src/modules/Core/modules/Language/store/index.js",
          "src/modules/Core/modules/Language/store/mutations.js",
          "src/modules/Core/modules/Language/store/state.js"
        ]
      },
      {
        "parentHierarchy": "Core",
        "fullName": "Core.Menu",
        "name": "Menu",
        "path": "src\\modules\\Core\\modules\\Menu",
        "fullPath": "src\\modules\\Core\\modules\\Menu",
        "level": 1,
        "priority": 99,
        "autoLoad": true,
        "childrenPriority": 98,
        "entry": "src\\modules\\Core\\modules\\Menu\\Menu.js",
        "directories": [
          "components",
          "store"
        ],
        "store": {
          "getters": {
            "items": "Core/Menu/items"
          },
          "mutations": {
            "updateItems": "Core/Menu/updateItems",
            "addItem": "Core/Menu/addItem"
          }
        },
        "storeFiles": {
          "getters": "src\\modules\\Core\\modules\\Menu\\store\\getters.js",
          "mutations": "src\\modules\\Core\\modules\\Menu\\store\\mutations.js",
          "state": "src\\modules\\Core\\modules\\Menu\\store\\state.js"
        },
        "entries": [
          "src/modules/Core/modules/Menu/components",
          "src/modules/Core/modules/Menu/components/Items",
          "src/modules/Core/modules/Menu/components/Items/Expander.js",
          "src/modules/Core/modules/Menu/components/Items/Expander.vue",
          "src/modules/Core/modules/Menu/components/Items/Item.js",
          "src/modules/Core/modules/Menu/components/Items/Item.vue",
          "src/modules/Core/modules/Menu/components/Items/RoutePush.js",
          "src/modules/Core/modules/Menu/components/Items/RoutePush.vue",
          "src/modules/Core/modules/Menu/components/LeftMenu.js",
          "src/modules/Core/modules/Menu/components/LeftMenu.vue",
          "src/modules/Core/modules/Menu/Menu.js",
          "src/modules/Core/modules/Menu/Menu.vue",
          "src/modules/Core/modules/Menu/module.config.json",
          "src/modules/Core/modules/Menu/store",
          "src/modules/Core/modules/Menu/store/getters.js",
          "src/modules/Core/modules/Menu/store/index.js",
          "src/modules/Core/modules/Menu/store/mutations.js",
          "src/modules/Core/modules/Menu/store/state.js"
        ]
      },
      {
        "parentHierarchy": "Core",
        "fullName": "Core.Url",
        "name": "Url",
        "path": "src\\modules\\Core\\modules\\Url",
        "fullPath": "src\\modules\\Core\\modules\\Url",
        "level": 1,
        "priority": 90,
        "autoLoad": true,
        "entry": "src\\modules\\Core\\modules\\Url\\Url.js",
        "directories": [
          "store"
        ],
        "store": {
          "getters": {
            "get": "Core/Url/get"
          },
          "mutations": {
            "addUrl": "Core/Url/addUrl"
          }
        },
        "storeFiles": {
          "getters": "src\\modules\\Core\\modules\\Url\\store\\getters.js",
          "mutations": "src\\modules\\Core\\modules\\Url\\store\\mutations.js",
          "state": "src\\modules\\Core\\modules\\Url\\store\\state.js"
        },
        "entries": [
          "src/modules/Core/modules/Url/store",
          "src/modules/Core/modules/Url/store/getters.js",
          "src/modules/Core/modules/Url/store/index.js",
          "src/modules/Core/modules/Url/store/mutations.js",
          "src/modules/Core/modules/Url/store/state.js",
          "src/modules/Core/modules/Url/Url.js",
          "src/modules/Core/modules/Url/Url.vue"
        ]
      },
      {
        "parentHierarchy": "Core",
        "fullName": "Core.Users",
        "name": "Users",
        "path": "src\\modules\\Core\\modules\\Users",
        "fullPath": "src\\modules\\Core\\modules\\Users",
        "level": 1,
        "priority": 90,
        "autoLoad": true,
        "childrenPriority": 85,
        "entry": "src\\modules\\Core\\modules\\Users\\Users.js",
        "directories": [
          "layouts",
          "pages",
          "router",
          "store"
        ],
        "routes": [
          {
            "parent": "Core",
            "name": "Core.Users.Login",
            "path": "users/login",
            "redirect": "users/login/form",
            "componentPath": "layouts/Login",
            "children": [
              {
                "name": "Core.Users.Login.LoginForm",
                "path": "form",
                "meta": {
                  "module": "Core.Users",
                  "view": "Login"
                },
                "componentPath": "pages/Login/Form"
              },
              {
                "name": "Core.Users.Login.2FA",
                "path": "2fa",
                "meta": {
                  "module": "Core.Users",
                  "view": "Login"
                },
                "componentPath": "pages/Login/2FA"
              },
              {
                "name": "Core.Users.Login.Reminder",
                "path": "reminder",
                "meta": {
                  "module": "Core.Users",
                  "view": "Login"
                },
                "componentPath": "pages/Login/Reminder"
              }
            ]
          }
        ],
        "store": {
          "actions": {
            "fetchData": "Core/Users/fetchData",
            "login": "Core/Users/login",
            "logout": "Core/Users/logout",
            "remind": "Core/Users/remind"
          },
          "getters": {
            "isLoggedIn": "Core/Users/isLoggedIn",
            "getMessage": "Core/Users/getMessage"
          },
          "mutations": {
            "isLoggedIn": "Core/Users/isLoggedIn",
            "setMessage": "Core/Users/setMessage"
          }
        },
        "storeFiles": {
          "actions": "src\\modules\\Core\\modules\\Users\\store\\actions.js",
          "getters": "src\\modules\\Core\\modules\\Users\\store\\getters.js",
          "mutations": "src\\modules\\Core\\modules\\Users\\store\\mutations.js",
          "state": "src\\modules\\Core\\modules\\Users\\store\\state.js"
        },
        "entries": [
          "src/modules/Core/modules/Users/layouts",
          "src/modules/Core/modules/Users/layouts/Login.js",
          "src/modules/Core/modules/Users/layouts/Login.vue",
          "src/modules/Core/modules/Users/module.config.json",
          "src/modules/Core/modules/Users/pages",
          "src/modules/Core/modules/Users/pages/Login",
          "src/modules/Core/modules/Users/pages/Login/2FA.js",
          "src/modules/Core/modules/Users/pages/Login/2FA.vue",
          "src/modules/Core/modules/Users/pages/Login/Form.js",
          "src/modules/Core/modules/Users/pages/Login/Form.vue",
          "src/modules/Core/modules/Users/pages/Login/Reminder.js",
          "src/modules/Core/modules/Users/pages/Login/Reminder.vue",
          "src/modules/Core/modules/Users/router",
          "src/modules/Core/modules/Users/router/routes.js",
          "src/modules/Core/modules/Users/store",
          "src/modules/Core/modules/Users/store/actions.js",
          "src/modules/Core/modules/Users/store/getters.js",
          "src/modules/Core/modules/Users/store/index.js",
          "src/modules/Core/modules/Users/store/mutations.js",
          "src/modules/Core/modules/Users/store/state.js",
          "src/modules/Core/modules/Users/Users.js",
          "src/modules/Core/modules/Users/Users.vue"
        ]
      }
    ]
  },
  {
    "parentHierarchy": "",
    "fullName": "Settings",
    "name": "Settings",
    "path": "src\\modules\\Settings",
    "fullPath": "src\\modules\\Settings",
    "level": 0,
    "parent": "",
    "priority": 0,
    "autoLoad": true,
    "entry": "src\\modules\\Settings\\Settings.js",
    "directories": [
      "layouts",
      "modules",
      "router",
      "store"
    ],
    "routes": [
      {
        "name": "Settings",
        "parent": "App",
        "path": "settings",
        "componentPath": "layouts/Settings"
      }
    ],
    "store": {
      "actions": {},
      "getters": {},
      "mutations": {}
    },
    "storeFiles": {
      "actions": "src\\modules\\Settings\\store\\actions.js",
      "getters": "src\\modules\\Settings\\store\\getters.js",
      "mutations": "src\\modules\\Settings\\store\\mutations.js",
      "state": "src\\modules\\Settings\\store\\state.js"
    },
    "entries": [
      "src/modules/Settings/layouts",
      "src/modules/Settings/layouts/Settings.js",
      "src/modules/Settings/layouts/Settings.vue",
      "src/modules/Settings/router",
      "src/modules/Settings/router/routes.js",
      "src/modules/Settings/Settings.js",
      "src/modules/Settings/Settings.vue",
      "src/modules/Settings/store",
      "src/modules/Settings/store/actions.js",
      "src/modules/Settings/store/getters.js",
      "src/modules/Settings/store/index.js",
      "src/modules/Settings/store/mutations.js",
      "src/modules/Settings/store/state.js"
    ],
    "modules": [
      {
        "parentHierarchy": "Settings",
        "fullName": "Settings.ModuleExample",
        "name": "ModuleExample",
        "path": "src\\modules\\Settings\\modules\\ModuleExample",
        "fullPath": "src\\modules\\Settings\\modules\\ModuleExample",
        "level": 1,
        "priority": 0,
        "autoLoad": true,
        "entry": "src\\modules\\Settings\\modules\\ModuleExample\\ModuleExample.js",
        "directories": [
          "pages",
          "router"
        ],
        "routes": [
          {
            "name": "Settings.ModuleExample",
            "parent": "Settings",
            "path": "module-example",
            "componentPath": "pages/ModuleExample"
          }
        ],
        "entries": [
          "src/modules/Settings/modules/ModuleExample/ModuleExample.js",
          "src/modules/Settings/modules/ModuleExample/ModuleExample.vue",
          "src/modules/Settings/modules/ModuleExample/pages",
          "src/modules/Settings/modules/ModuleExample/pages/ModuleExample.js",
          "src/modules/Settings/modules/ModuleExample/pages/ModuleExample.vue",
          "src/modules/Settings/modules/ModuleExample/router",
          "src/modules/Settings/modules/ModuleExample/router/routes.js"
        ]
      }
    ]
  }
]