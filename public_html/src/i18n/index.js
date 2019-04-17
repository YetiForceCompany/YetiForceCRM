/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
let messages = {}
if (typeof window !== 'undefined' && window.env.Core !== undefined) {
  console.log(window.env.Core)
  messages = window.env.Core.Language.translations
}

let i18n = null
function createI18n({ app }) {
  if (i18n === null && window.env.Core !== undefined) {
    Vue.use(VueI18n)
    app.i18n = new VueI18n({
      locale: '_Base',
      fallbackLocale: '_Base',
      silentTranslationWarn: window.env.Core.Env.dev,
      messages
    })
    i18n = app.i18n
  }

  return i18n
}

export default createI18n
export { i18n }
