/**
 * Notification actions
 *
 * @description Notification actions
 * @license YetiForce Public License 3.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
import { i18n } from '/src/i18n/index.js'

export default {
  /**
   * Show notification
   * @example <caption>Example usage </caption>
   * store.dispatch([actions.Core.Notification.show], {color: 'negative'})
   */
  show({}, options = {}) {
    let defaults = {
      color: 'primary',
      icon: 'mdi-check',
      message: 'OK',
      position: 'bottom-right',
      actions: [{ label: i18n.t('LBL_CLOSE'), color: 'white' }]
    }
    if (options.color === 'negative') {
      defaults.icon = 'mdi-alert-octagon-outline'
      defaults.position = 'top'
    }
    Quasar.plugins.Notify.create(Object.assign(defaults, options))
  }
}
