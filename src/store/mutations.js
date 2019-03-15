export default {
  "App": {
    "setModules": "App/setModules",
    "Base": {
      "Home": {},
      "ModuleExample": {
        "updateTestVariable": "App/Base/ModuleExample/updateTestVariable"
      }
    },
    "Core": {
      "Debug": {
        "push": "App/Core/Debug/push"
      },
      "Env": {
        "update": "App/Core/Env/update"
      },
      "Hooks": {
        "add": "App/Core/Hooks/add",
        "remove": "App/Core/Hooks/remove"
      },
      "Language": {
        "update": "App/Core/Language/update"
      },
      "Menu": {
        "updateItems": "App/Core/Menu/updateItems",
        "addItem": "App/Core/Menu/addItem"
      },
      "Url": {
        "addUrl": "App/Core/Url/addUrl"
      },
      "Users": {
        "isLoggedIn": "App/Core/Users/isLoggedIn"
      }
    },
    "Settings": {
      "ModuleExample": {}
    }
  }
}