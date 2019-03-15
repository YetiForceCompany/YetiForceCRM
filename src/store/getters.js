export default {
  "App": {
    "get": "App/get",
    "all": "App/all",
    "Base": {
      "Home": {},
      "ModuleExample": {
        "testVariable": "App/Base/ModuleExample/testVariable"
      }
    },
    "Core": {
      "Debug": {
        "get": "App/Core/Debug/get"
      },
      "Env": {
        "all": "App/Core/Env/all"
      },
      "Hooks": {
        "get": "App/Core/Hooks/get"
      },
      "Language": {},
      "Menu": {
        "items": "App/Core/Menu/items"
      },
      "Url": {
        "get": "App/Core/Url/get"
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