/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
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
    "entry": "src\\modules\\Base\\Base.vue.js",
    "directories": [
      "layouts",
      "modules",
      "router",
      "store"
    ],
    "routes": [
      {
        "parent": "App",
        "name": "Base",
        "path": "/",
        "componentPath": "layouts/Base"
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
        "fullName": "Base.Accounts",
        "name": "Accounts",
        "path": "src\\modules\\Base\\modules\\Accounts",
        "level": 1,
        "priority": 0,
        "autoLoad": true,
        "entry": "src\\modules\\Base\\modules\\Accounts\\Accounts.vue.js",
        "directories": [
          "router",
          "store"
        ],
        "routes": [
          {
            "name": "Base.Accounts",
            "parent": "Base",
            "path": "accounts",
            "componentPath": "../Basic/layouts/Basic",
            "redirect": "accounts/list",
            "children": [
              {
                "name": "Base.Basic.List",
                "path": "list",
                "componentPath": "../Basic/views/List/List",
                "meta": {
                  "moduleName": "Accounts"
                }
              },
              {
                "name": "Base.Basic.Detail",
                "path": "detail",
                "componentPath": "../Basic/views/Detail/Detail"
              }
            ]
          }
        ],
        "store": {
          "getters": {
            "getTestVariable": "Base/Accounts/getTestVariable",
            "getModuleName": "Base/Accounts/getModuleName",
            "getHeaders": "Base/Accounts/getHeaders",
            "getEntries": "Base/Accounts/getEntries",
            "List": {
              "getTestVariable": "Base/Accounts/List/getTestVariable",
              "getModuleName": "Base/Accounts/List/getModuleName"
            },
            "Detail": {
              "getTestVariable": "Base/Accounts/Detail/getTestVariable",
              "getModuleName": "Base/Accounts/Detail/getModuleName"
            }
          },
          "mutations": {
            "updateTestVariable": "Base/Accounts/updateTestVariable",
            "List": {
              "updateTestVariable": "Base/Accounts/List/updateTestVariable"
            },
            "Detail": {
              "updateTestVariable": "Base/Accounts/Detail/updateTestVariable"
            }
          },
          "actions": {
            "getData": "Base/Accounts/getData",
            "List": {
              "fetchData": "Base/Accounts/List/fetchData",
              "getData": "Base/Accounts/List/getData"
            },
            "Detail": {
              "getData": "Base/Accounts/Detail/getData"
            }
          },
          "modules": {
            "List": "Base/Accounts/List",
            "Detail": "Base/Accounts/Detail"
          },
          "namespaced": {
            "List": {},
            "Detail": {}
          }
        }
      },
      {
        "parentHierarchy": "Base",
        "fullName": "Base.Basic",
        "name": "Basic",
        "path": "src\\modules\\Base\\modules\\Basic",
        "level": 1,
        "priority": 0,
        "autoLoad": true,
        "entry": "src\\modules\\Base\\modules\\Basic\\Basic.vue.js",
        "directories": [
          "layouts",
          "router",
          "store",
          "views"
        ],
        "routes": [
          {
            "name": "Base.Basic",
            "parent": "Base",
            "path": "basic",
            "componentPath": "layouts/Basic",
            "redirect": "basic/list",
            "children": [
              {
                "name": "Base.Basic.List",
                "path": "list",
                "componentPath": "views/List/List"
              },
              {
                "name": "Base.Basic.Detail",
                "path": "detail",
                "componentPath": "views/Detail/Detail"
              }
            ]
          }
        ],
        "store": {
          "getters": {
            "getTestVariable": "Base/Basic/getTestVariable",
            "getModuleName": "Base/Basic/getModuleName",
            "List": {
              "getTestVariable": "Base/Basic/List/getTestVariable",
              "getModuleName": "Base/Basic/List/getModuleName"
            },
            "Detail": {
              "getTestVariable": "Base/Basic/Detail/getTestVariable",
              "getModuleName": "Base/Basic/Detail/getModuleName"
            }
          },
          "mutations": {
            "updateTestVariable": "Base/Basic/updateTestVariable",
            "List": {
              "updateTestVariable": "Base/Basic/List/updateTestVariable"
            },
            "Detail": {
              "updateTestVariable": "Base/Basic/Detail/updateTestVariable"
            }
          },
          "actions": {
            "getData": "Base/Basic/getData",
            "List": {
              "fetchData": "Base/Basic/List/fetchData",
              "getData": "Base/Basic/List/getData"
            },
            "Detail": {
              "getData": "Base/Basic/Detail/getData"
            }
          },
          "modules": {
            "List": "Base/Basic/List",
            "Detail": "Base/Basic/Detail"
          },
          "namespaced": {
            "List": {},
            "Detail": {}
          }
        }
      },
      {
        "parentHierarchy": "Base",
        "fullName": "Base.Chat",
        "name": "Chat",
        "path": "src\\modules\\Base\\modules\\Chat",
        "level": 1,
        "priority": 0,
        "autoLoad": true,
        "entry": "src\\modules\\Base\\modules\\Chat\\Chat.vue.js",
        "directories": [
          "components",
          "store"
        ],
        "store": {
          "actions": {
            "setDialog": "Base/Chat/setDialog",
            "maximizedDialog": "Base/Chat/maximizedDialog",
            "toggleLeftPanel": "Base/Chat/toggleLeftPanel",
            "toggleRightPanel": "Base/Chat/toggleRightPanel"
          },
          "getters": {
            "dialog": "Base/Chat/dialog",
            "maximizedDialog": "Base/Chat/maximizedDialog",
            "leftPanel": "Base/Chat/leftPanel",
            "rightPanel": "Base/Chat/rightPanel"
          },
          "mutations": {
            "dialog": "Base/Chat/dialog",
            "maximizedDialog": "Base/Chat/maximizedDialog",
            "leftPanel": "Base/Chat/leftPanel",
            "rightPanel": "Base/Chat/rightPanel"
          }
        }
      },
      {
        "parentHierarchy": "Base",
        "fullName": "Base.Home",
        "name": "Home",
        "path": "src\\modules\\Base\\modules\\Home",
        "level": 1,
        "priority": 0,
        "autoLoad": true,
        "entry": "src\\modules\\Base\\modules\\Home\\Home.vue.js",
        "directories": [
          "pages",
          "router"
        ],
        "routes": [
          {
            "name": "Base.HomeIndex",
            "parent": "Base",
            "path": "home",
            "alias": "/",
            "componentPath": "/pages/Index",
            "children": [
              {
                "name": "Base.HomeIndex.Home",
                "path": "",
                "componentPath": "pages/Home"
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
        "entry": "src\\modules\\Base\\modules\\ModuleExample\\ModuleExample.vue.js",
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
          "getters": {
            "getTestVariable": "Base/ModuleExample/getTestVariable",
            "getModuleName": "Base/ModuleExample/getModuleName",
            "List": {
              "getTestVariable": "Base/ModuleExample/List/getTestVariable",
              "getModuleName": "Base/ModuleExample/List/getModuleName"
            },
            "Detail": {
              "getTestVariable": "Base/ModuleExample/Detail/getTestVariable",
              "getModuleName": "Base/ModuleExample/Detail/getModuleName"
            }
          },
          "mutations": {
            "updateTestVariable": "Base/ModuleExample/updateTestVariable",
            "List": {
              "updateTestVariable": "Base/ModuleExample/List/updateTestVariable"
            },
            "Detail": {
              "updateTestVariable": "Base/ModuleExample/Detail/updateTestVariable"
            }
          },
          "actions": {
            "getData": "Base/ModuleExample/getData",
            "List": {
              "fetchData": "Base/ModuleExample/List/fetchData",
              "getData": "Base/ModuleExample/List/getData"
            },
            "Detail": {
              "getData": "Base/ModuleExample/Detail/getData"
            }
          },
          "modules": {
            "List": "Base/ModuleExample/List",
            "Detail": "Base/ModuleExample/Detail"
          },
          "namespaced": {
            "List": {},
            "Detail": {}
          }
        }
      },
      {
        "parentHierarchy": "Base",
        "fullName": "Base.ModuleExample2",
        "name": "ModuleExample2",
        "path": "src\\modules\\Base\\modules\\ModuleExample2",
        "level": 1,
        "priority": 0,
        "autoLoad": true,
        "entry": "src\\modules\\Base\\modules\\ModuleExample2\\ModuleExample2.vue.js",
        "directories": [
          "pages",
          "router",
          "store"
        ],
        "routes": [
          {
            "name": "Base.ModuleExample2",
            "parent": "Base",
            "path": "module-example2",
            "componentPath": "pages/ModuleExample2"
          }
        ],
        "store": {
          "actions": {
            "getData": "Base/ModuleExample2/getData"
          },
          "getters": {
            "testVariable": "Base/ModuleExample2/testVariable"
          },
          "mutations": {
            "updateTestVariable": "Base/ModuleExample2/updateTestVariable"
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
    "entry": "src\\modules\\Core\\Core.vue.js",
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
        "path": "/",
        "componentPath": "layouts/Core"
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
        "entry": "src\\modules\\Core\\modules\\Debug\\Debug.vue.js",
        "directories": [
          "pages",
          "router",
          "store"
        ],
        "routes": [],
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
        "entry": "src\\modules\\Core\\modules\\Env\\Env.vue.js",
        "directories": [
          "store"
        ],
        "store": {
          "getters": {
            "all": "Core/Env/all",
            "template": "Core/Env/template",
            "isWebSocketConnected": "Core/Env/isWebSocketConnected"
          },
          "mutations": {
            "update": "Core/Env/update",
            "isWebSocketConnected": "Core/Env/isWebSocketConnected"
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
        "entry": "src\\modules\\Core\\modules\\Hooks\\Hooks.vue.js",
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
        "fullName": "Core.Icons",
        "name": "Icons",
        "path": "src\\modules\\Core\\modules\\Icons",
        "level": 1,
        "priority": 90,
        "autoLoad": true,
        "entry": "src\\modules\\Core\\modules\\Icons\\Icons.vue.js",
        "directories": [
          "assets",
          "components",
          "store"
        ],
        "store": {
          "getters": {
            "get": "Core/Icons/get"
          },
          "mutations": {
            "setIcon": "Core/Icons/setIcon"
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
        "entry": "src\\modules\\Core\\modules\\Language\\Language.vue.js",
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
        "entry": "src\\modules\\Core\\modules\\Menu\\Menu.vue.js",
        "directories": [
          "components",
          "store"
        ],
        "store": {
          "actions": {
            "fetchData": "Core/Menu/fetchData"
          },
          "getters": {
            "items": "Core/Menu/items",
            "types": "Core/Menu/types"
          },
          "mutations": {
            "updateItems": "Core/Menu/updateItems",
            "addItem": "Core/Menu/addItem"
          }
        }
      },
      {
        "parentHierarchy": "Core",
        "fullName": "Core.Notification",
        "name": "Notification",
        "path": "src\\modules\\Core\\modules\\Notification",
        "level": 1,
        "priority": 90,
        "autoLoad": true,
        "entry": "src\\modules\\Core\\modules\\Notification\\Notification.vue.js",
        "directories": [
          "store"
        ],
        "store": {
          "actions": {
            "show": "Core/Notification/show"
          },
          "getters": {},
          "mutations": {}
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
        "entry": "src\\modules\\Core\\modules\\Url\\Url.vue.js",
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
        "entry": "src\\modules\\Core\\modules\\Users\\Users.vue.js",
        "directories": [
          "layouts",
          "pages",
          "router",
          "store",
          "url"
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
                  "langModule": "Users"
                },
                "componentPath": "pages/Login/Form"
              },
              {
                "name": "Core.Users.Login.2FA",
                "path": "2fa",
                "meta": {
                  "langModule": "Users"
                },
                "componentPath": "pages/Login/2FA"
              },
              {
                "name": "Core.Users.Login.Reminder",
                "path": "reminder",
                "meta": {
                  "langModule": "Users"
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
            "isBlockedIp": "Core/Users/isBlockedIp",
            "loginPageRememberCredentials": "Core/Users/loginPageRememberCredentials",
            "resetLoginPassword": "Core/Users/resetLoginPassword",
            "langInLoginView": "Core/Users/langInLoginView",
            "layoutInLoginView": "Core/Users/layoutInLoginView",
            "is2fa": "Core/Users/is2fa"
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
    "entry": "src\\modules\\Settings\\Settings.vue.js",
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
        "path": "/settings",
        "componentPath": "layouts/Settings"
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
        "fullName": "Settings.Menu",
        "name": "Menu",
        "path": "src\\modules\\Settings\\modules\\Menu",
        "level": 1,
        "priority": 0,
        "autoLoad": true,
        "entry": "src\\modules\\Settings\\modules\\Menu\\Menu.vue.js",
        "directories": [
          "components",
          "pages",
          "router"
        ],
        "routes": [
          {
            "name": "Settings.Menu",
            "path": "menu",
            "parent": "Settings",
            "componentPath": "pages/Index"
          }
        ]
      },
      {
        "parentHierarchy": "Settings",
        "fullName": "Settings.ModuleExample",
        "name": "ModuleExample",
        "path": "src\\modules\\Settings\\modules\\ModuleExample",
        "level": 1,
        "priority": 0,
        "autoLoad": true,
        "entry": "src\\modules\\Settings\\modules\\ModuleExample\\ModuleExample.vue.js",
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
        ]
      }
    ]
  }
]