window.modules = [
  {
    "parentHierarchy": "",
    "fullName": "Base",
    "name": "Base",
    "path": "src\\modules\\Base",
    "level": 0,
    "parent": "",
    "priority": 0,
    "autoLoad": true,
    "entry": "src\\modules\\Base\\Base.min.js",
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
        "componentPath": "layouts/Base.js"
      }
    ],
    "store": {
      "actions": {},
      "getters": {},
      "mutations": {}
    },
    "modules": [
      {
        "parentHierarchy": "Base",
        "fullName": "Base.Home",
        "name": "Home",
        "path": "src\\modules\\Base\\modules\\Home",
        "level": 1,
        "priority": 0,
        "autoLoad": true,
        "entry": "src\\modules\\Base\\modules\\Home\\Home.min.js",
        "directories": [
          "pages",
          "router"
        ],
        "routes": [
          {
            "name": "Base.HomeIndex",
            "parent": "Base",
            "path": "home",
            "componentPath": "/pages/Index.js",
            "children": [
              {
                "name": "Base.HomeIndex.Home",
                "path": "",
                "componentPath": "pages/Home.js"
              }
            ]
          }
        ]
      },
      {
        "parentHierarchy": "Base",
        "fullName": "Base.ModuleExample",
        "name": "ModuleExample",
        "path": "src\\modules\\Base\\modules\\ModuleExample",
        "level": 1,
        "priority": 0,
        "autoLoad": true,
        "entry": "src\\modules\\Base\\modules\\ModuleExample\\ModuleExample.min.js",
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
            "componentPath": "pages/ModuleExample.js"
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
        }
      }
    ]
  },
  {
    "parentHierarchy": "",
    "fullName": "Core",
    "name": "Core",
    "path": "src\\modules\\Core",
    "level": 0,
    "parent": "",
    "priority": 100,
    "autoLoad": true,
    "childrenPriority": 90,
    "entry": "src\\modules\\Core\\Core.min.js",
    "directories": [
      "components",
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
        "componentPath": "layouts/Core.js"
      }
    ],
    "store": {
      "actions": {},
      "getters": {},
      "mutations": {}
    },
    "modules": [
      {
        "parentHierarchy": "Core",
        "fullName": "Core.Debug",
        "name": "Debug",
        "path": "src\\modules\\Core\\modules\\Debug",
        "level": 1,
        "priority": 99,
        "autoLoad": true,
        "entry": "src\\modules\\Core\\modules\\Debug\\Debug.min.js",
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
        }
      },
      {
        "parentHierarchy": "Core",
        "fullName": "Core.Env",
        "name": "Env",
        "path": "src\\modules\\Core\\modules\\Env",
        "level": 1,
        "priority": 90,
        "autoLoad": true,
        "entry": "src\\modules\\Core\\modules\\Env\\Env.min.js",
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
        }
      },
      {
        "parentHierarchy": "Core",
        "fullName": "Core.Hooks",
        "name": "Hooks",
        "path": "src\\modules\\Core\\modules\\Hooks",
        "level": 1,
        "priority": 96,
        "autoLoad": true,
        "entry": "src\\modules\\Core\\modules\\Hooks\\Hooks.min.js",
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
        }
      },
      {
        "parentHierarchy": "Core",
        "fullName": "Core.Language",
        "name": "Language",
        "path": "src\\modules\\Core\\modules\\Language",
        "level": 1,
        "priority": 90,
        "autoLoad": true,
        "entry": "src\\modules\\Core\\modules\\Language\\Language.min.js",
        "directories": [
          "store"
        ],
        "store": {
          "mutations": {
            "update": "Core/Language/update"
          }
        }
      },
      {
        "parentHierarchy": "Core",
        "fullName": "Core.Menu",
        "name": "Menu",
        "path": "src\\modules\\Core\\modules\\Menu",
        "level": 1,
        "priority": 99,
        "autoLoad": true,
        "childrenPriority": 98,
        "entry": "src\\modules\\Core\\modules\\Menu\\Menu.min.js",
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
        }
      },
      {
        "parentHierarchy": "Core",
        "fullName": "Core.Url",
        "name": "Url",
        "path": "src\\modules\\Core\\modules\\Url",
        "level": 1,
        "priority": 90,
        "autoLoad": true,
        "entry": "src\\modules\\Core\\modules\\Url\\Url.min.js",
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
        }
      },
      {
        "parentHierarchy": "Core",
        "fullName": "Core.Users",
        "name": "Users",
        "path": "src\\modules\\Core\\modules\\Users",
        "level": 1,
        "priority": 90,
        "autoLoad": true,
        "childrenPriority": 85,
        "entry": "src\\modules\\Core\\modules\\Users\\Users.min.js",
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
            "componentPath": "layouts/Login.js",
            "children": [
              {
                "name": "Core.Users.Login.LoginForm",
                "path": "form",
                "meta": {
                  "module": "Core.Users",
                  "view": "Login"
                },
                "componentPath": "pages/Login/Form.js"
              },
              {
                "name": "Core.Users.Login.2FA",
                "path": "2fa",
                "meta": {
                  "module": "Core.Users",
                  "view": "Login"
                },
                "componentPath": "pages/Login/2FA.js"
              },
              {
                "name": "Core.Users.Login.Reminder",
                "path": "reminder",
                "meta": {
                  "module": "Core.Users",
                  "view": "Login"
                },
                "componentPath": "pages/Login/Reminder.js"
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
            "isLoggedIn": "Core/Users/isLoggedIn"
          }
        }
      }
    ]
  },
  {
    "parentHierarchy": "",
    "fullName": "Settings",
    "name": "Settings",
    "path": "src\\modules\\Settings",
    "level": 0,
    "parent": "",
    "priority": 0,
    "autoLoad": true,
    "entry": "src\\modules\\Settings\\Settings.min.js",
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
        "componentPath": "layouts/Settings.js"
      }
    ],
    "store": {
      "actions": {},
      "getters": {},
      "mutations": {}
    },
    "modules": [
      {
        "parentHierarchy": "Settings",
        "fullName": "Settings.ModuleExample",
        "name": "ModuleExample",
        "path": "src\\modules\\Settings\\modules\\ModuleExample",
        "level": 1,
        "priority": 0,
        "autoLoad": true,
        "entry": "src\\modules\\Settings\\modules\\ModuleExample\\ModuleExample.min.js",
        "directories": [
          "pages",
          "router"
        ],
        "routes": [
          {
            "name": "Settings.ModuleExample",
            "parent": "Settings",
            "path": "module-example",
            "componentPath": "pages/ModuleExample.js"
          }
        ]
      }
    ]
  }
]