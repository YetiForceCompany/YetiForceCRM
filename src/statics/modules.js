window.modules = [
  {
    parentHierarchy: "",
    fullName: "App",
    name: "App",
    path: "src\\modules\\App",
    level: 0,
    parent: "",
    directories: ["layouts", "modules", "router"],
    routes: [
      {
        name: "App",
        parent: "Layout",
        path: "app",
        componentPath: "layouts/App.vue"
      }
    ],
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
          actions: {
            "App/ModuleExample/getData": function(state) {
              console.log("get Data");
            }
          },
          getters: {
            "App/ModuleExample/testVariable": function(state) {
              return state.testVariable;
            }
          },
          mutations: {
            "App/ModuleExample/updateTestVariable": function(state, value) {
              state.testVariable = value;
            }
          },
          state: {
            testVariable: "test variable"
          }
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
    directories: ["layouts", "modules", "router"],
    routes: [
      {
        name: "Settings",
        parent: "Layout",
        path: "settings",
        componentPath: "layouts/Settings.vue"
      }
    ],
    modules: [
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
