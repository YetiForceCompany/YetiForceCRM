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
   * @example <caption>Example usage with named argument</caption>
   * store.dispatch([actions.Core.Notification.show]{color: 'negative'})
   */
  show({
    color = 'primary',
    icon = 'mdi-check',
    message = 'OK',
    position = 'bottom-right',
    actions = [{ label: i18n.t('LBL_CLOSE'), color: 'white' }],
    advanced = {}
  } = {}) {
    let basicOptions = {
      color: color,
      icon: icon,
      message: message,
      position: position,
      actions: actions
    }
    Quasar.plugins.Notify.create(Object.assign(basicOptions, advanced))
  }
}
