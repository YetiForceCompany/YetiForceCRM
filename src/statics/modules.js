window.modules = {
  "Home": {
    "name": "Home",
    "path": "src/modules/Home",
    "entry": "src/modules/Home/Home.vue",
    "directories": [
      "pages",
      "router"
    ],
    "routes": [
      {
        "name": "HomeIndex",
        "parent": "Layout",
        "path": "home",
        "componentPath": "/pages/Index.vue",
        "children": [
          {
            "name": "Home",
            "path": "",
            "componentPath": "pages/Home.vue"
          }
        ]
      }
    ]
  },
  "ModuleExample": {
    "name": "ModuleExample",
    "path": "src/modules/ModuleExample",
    "entry": "src/modules/ModuleExample/ModuleExample.vue",
    "directories": [
      "pages",
      "router"
    ],
    "routes": [
      {
        "name": "ModuleExample",
        "parent": "Home",
        "path": "module-example",
        "componentPath": "pages/ModuleExample.vue"
      }
    ]
  }
};