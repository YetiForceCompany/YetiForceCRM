/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 *************************************************************************************/
'use strict';

Vtiger_Edit_Js(
	'Documents_Edit_Js',
	{},
	{
		INTERNAL_FILE_LOCATION_TYPE: 'I',
		EXTERNAL_FILE_LOCATION_TYPE: 'E',

		isFileLocationInternalType: function (fileLocationElement) {
			if (fileLocationElement.val() == this.INTERNAL_FILE_LOCATION_TYPE) {
				return true;
			}
			return false;
		},

		isFileLocationExternalType: function (fileLocationElement) {
			if (fileLocationElement.val() == this.EXTERNAL_FILE_LOCATION_TYPE) {
				return true;
			}
			return false;
		},

		convertFileSizeInToDisplayFormat: function (fileSizeInBytes) {
			var i = -1;
			var byteUnits = [' kB', ' MB', ' GB', ' TB', 'PB', 'EB', 'ZB', 'YB'];
			do {
				fileSizeInBytes = fileSizeInBytes / 1024;
				i++;
			} while (fileSizeInBytes > 1024);

			return Math.max(fileSizeInBytes, 0.1).toFixed(1) + byteUnits[i];
		},

		registerFileLocationTypeChangeEvent: function (container) {
			var thisInstance = this;
			container.on('change', 'select[name="filelocationtype"]', function (e) {
				var fileLocationTypeElement = container.find('[name="filelocationtype"]');
				var fileNameElement = container.find('[name="filename"]');
				var newFileNameElement;
				if (thisInstance.isFileLocationInternalType(fileLocationTypeElement)) {
					newFileNameElement = jQuery('<input type="file"/>');
				} else {
					newFileNameElement = jQuery('<input type="text" />');
				}
				var oldElementAttributeList = fileNameElement.get(0).attributes;

				for (var index = 0; index < oldElementAttributeList.length; index++) {
					var attributeObject = oldElementAttributeList[index];
					//Dont update the type attribute
					if (attributeObject.name == 'type' || attributeObject.name == 'value' || attributeObject.name == 'class') {
						continue;
					}
					var value = attributeObject.value;
					var className = '';
					if (attributeObject.name == 'data-fieldinfo') {
						value = JSON.parse(value);
						if (thisInstance.isFileLocationExternalType(fileLocationTypeElement)) {
							value['type'] = 'url';
							className = 'form-control';
						} else {
							value['type'] = 'file';
						}
						value = JSON.stringify(value);
					}
					newFileNameElement.attr(attributeObject.name, value);
					newFileNameElement.addClass(className);
				}
				fileNameElement.replaceWith(newFileNameElement);
				var fileNameElementTd = newFileNameElement.closest('.fieldValue');
				var uploadFileDetails = fileNameElementTd.find('.uploadedFileDetails');
				if (thisInstance.isFileLocationExternalType(fileLocationTypeElement)) {
					uploadFileDetails.addClass('d-none').removeClass('show');
				} else {
					uploadFileDetails.addClass('show').removeClass('d-none');
				}
			});
		},

		registerFileChangeEvent: function (container) {
			var thisInstance = this;

			container.on('change', 'input[name="filename"]', function (e) {
				if (e.target.type === 'text') {
					return false;
				}
				let element = container.find('[name="filename"]');
				//ignore all other types than file
				if (element.attr('type') !== 'file') {
					return;
				}
				let uploadFileSizeHolder = element.closest('.fileUploadContainer').find('.uploadedFileSize');
				let fileSize = element.get(0).files[0].size;
				if (fileSize > CONFIG['maxUploadLimit']) {
					app.showAlert(app.vtranslate('JS_UPLOADED_FILE_SIZE_EXCEEDS'));
					element.val('');
					uploadFileSizeHolder.text('');
				} else {
					uploadFileSizeHolder.text(thisInstance.convertFileSizeInToDisplayFormat(fileSize));
				}
			});
		},

		/**
		 * Function to save the quickcreate module
		 * @param accepts form element as parameter
		 * @returns {Promise}
		 */
		quickCreateSave: function (form) {
			var thisInstance = this;
			var aDeferred = jQuery.Deferred();
			//Using formData object to send data to server as a multipart/form-data form submit
			var formData = new FormData(form[0]);
			var fileLocationTypeElement = form.find('[name="filelocationtype"]');
			var params = {
				url: 'index.php',
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false
			};
			AppConnector.request(params)
				.done(function (data) {
					aDeferred.resolve(data);
				})
				.fail(function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
				});
			return aDeferred.promise();
		},
		registerBasicEvents: function (container) {
			this._super(container);
			this.registerFileLocationTypeChangeEvent(container);
			this.registerFileChangeEvent(container);
		},

		registerEvents: function () {
			this._super();
		}
	}
);
