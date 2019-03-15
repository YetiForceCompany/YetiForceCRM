export default {
  "App": {
    "routes": {
      "App": {
        "path": "/app",
        "name": "App",
        "routes": {}
      },
      "Debug": {
        "routes": {}
      },
      "Env": {
        "routes": {}
      },
      "Hooks": {
        "routes": {}
      },
      "Language": {
        "routes": {}
      },
      "Menu": {
        "routes": {}
      },
      "Url": {
        "routes": {}
      },
      "Users": {
        "routes": {
          "Login": {
            "path": "/users/login",
            "name": "App.Users.Login",
            "routes": {
              "LoginForm": {
                "path": "/users/login/form",
                "name": "App.Users.Login.LoginForm",
                "routes": {}
              },
              "2FA": {
                "path": "/users/login/2fa",
                "name": "App.Users.Login.2FA",
                "routes": {}
              },
              "Reminder": {
                "path": "/users/login/reminder",
                "name": "App.Users.Login.Reminder",
                "routes": {}
              }
            }
          }
        }
      }
    }
  },
  "Base": {
    "routes": {
      "Base": {
        "path": "/base",
        "name": "Base",
        "routes": {}
      },
      "Home": {
        "routes": {
          "HomeIndex": {
            "path": "/home",
            "name": "Base.HomeIndex",
            "routes": {
              "Home": {
                "path": "/home/",
                "name": "Base.HomeIndex.Home",
                "routes": {}
              }
            }
          }
        }
      },
      "ModuleExample": {
        "routes": {
          "ModuleExample": {
            "path": "/module-example",
            "name": "Base.ModuleExample",
            "routes": {}
          }
        }
      }
    }
  },
  "Settings": {
    "routes": {
      "Settings": {
        "path": "/settings",
        "name": "Settings",
        "routes": {}
      },
      "ModuleExample": {
        "routes": {
          "ModuleExample": {
            "path": "/module-example",
            "name": "Settings.ModuleExample",
            "routes": {}
          }
        }
      }
    }
  }
}