window.modules = [
  {
    parentHierarchy: "",
    fullName: "App",
    name: "App",
    path: "src\\modules\\App",
    level: 0,
    parent: "",
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
        parent: "App",
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
        parent: "App",
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
    fullName: "Settings",
    name: "Settings",
    path: "src\\modules\\Settings",
    level: 0,
    parent: "",
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
        fullName: "Settings.Menu",
        name: "Menu",
        path: "src\\modules\\Settings\\modules\\Menu",
        level: 1,
        parent: "Settings",
        entry: "src\\modules\\Settings\\modules\\Menu\\Menu.vue",
        directories: ["pages", "router"],
        routes: [
          {
            name: "Settings.Menu",
            parent: "Settings",
            path: "menu",
            componentPath: "pages/Menu"
          }
        ]
      },
      {
        parentHierarchy: "Settings",
        fullName: "Settings.ModuleExample",
        name: "ModuleExample",
        path: "src\\modules\\Settings\\modules\\ModuleExample",
        level: 1,
        parent: "Settings",
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
