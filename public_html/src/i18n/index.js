/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
let messages = {}
if (typeof window !== 'undefined' && window.env !== 'undefined') {
  messages = window.env.Language.translations
}

let i18n = null
function createI18n({ app }) {
  if (i18n === null) {
    Vue.use(VueI18n)
    app.i18n = new VueI18n({
      locale: '_Base',
      fallbackLocale: '_Base',
      silentTranslationWarn: window.env.Env.dev,
      messages
    })
    i18n = app.i18n
  }

  return i18n
}

export default createI18n
