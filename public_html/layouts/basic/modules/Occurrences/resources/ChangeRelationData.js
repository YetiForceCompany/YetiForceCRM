/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
jQuery.Class(
  'Occurrences_ChangeRelationData_Js',
  {},
  {
    registerEvents() {
      let container = $('.js-modal-data[data-view="ChangeRelationData"]');
      container.find('.js-modal__save').on('click', e => {
        let progress = $.progressIndicator({ position: 'html', blockInfo: { enabled: true } });
        let params = container.find('form').serializeFormData();
        AppConnector.request(params)
          .done(function(data) {
            progress.progressIndicator({ mode: 'hide' });
            app.hideModalWindow();
            let params = {};
            if (data.result) {
              params.text = app.vtranslate('JS_SAVE_NOTIFY_OK');
              params.type = 'success';
              var detailInstance = Vtiger_Detail_Js.getInstance();
              var selectedTabElement = detailInstance.getSelectedTab();
              if (selectedTabElement) {
                selectedTabElement.trigger('click');
              }
            } else {
              params.text = app.vtranslate('JS_ERROR');
              params.type = 'error';
            }
            Vtiger_Helper_Js.showPnotify(params);
          })
          .fail(function(textStatus, errorThrown) {});
      });
    }
  }
);

jQuery(document).ready(function(e) {
  var instance = new Occurrences_ChangeRelationData_Js();
  instance.registerEvents();
});
