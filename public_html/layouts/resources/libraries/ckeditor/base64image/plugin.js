/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

CKEDITOR.plugins.add('base64image', {
	requires: 'dialog',
	icons: 'base64image',
	hidpi: true,
	init: function (editor) {
		if (editor.addFeature) {
			editor.addFeature({
				allowedContent: 'img[alt,id,!src]{width,height};'
			});
		}
		editor.on('paste', (event, a, b) => {
			this.onPaste(event);
		});
		const pluginName = 'base64image-dialog';
		editor.ui.addToolbarGroup('base64image', 'insert');
		editor.ui.addButton('base64image', {
			label: editor.lang.common.image,
			command: pluginName,
			toolbar: 'insert'
		});
		CKEDITOR.dialog.add(pluginName, this.path + 'dialogs/dialog.js');
		editor.addCommand(
			pluginName,
			new CKEDITOR.dialogCommand(pluginName, {
				allowedContent:
					'img[alt,!src]{border-style,border-width,float,height,margin,margin-bottom,margin-left,margin-right,margin-top,width}',
				requiredContent: 'img[alt,src]',
				contentTransformations: [
					['img{width}: sizeToStyle', 'img[width]: sizeToAttribute'],
					['img{float}: alignmentToStyle', 'img[align]: alignmentToAttribute']
				]
			})
		);
		editor.on('doubleclick', function (evt) {
			if (evt.data.element && !evt.data.element.isReadOnly() && evt.data.element.getName() === 'img') {
				evt.data.dialog = pluginName;
				editor.getSelection().selectElement(evt.data.element);
			}
		});
		if (editor.addMenuItem) {
			editor.addMenuGroup('imageToBase64Group');
			editor.addMenuItem('imageToBase64Item', {
				label: editor.lang.common.image,
				icon: this.path + 'icons/base64image.png',
				command: pluginName,
				group: 'imageToBase64Group'
			});
		}
		if (editor.contextMenu) {
			editor.contextMenu.addListener(function (element) {
				if (element && element.getName() === 'img') {
					editor.getSelection().selectElement(element);
					return { imageToBase64Item: CKEDITOR.TRISTATE_ON };
				}
				return null;
			});
		}
	},
	onPaste: function (event) {
		const self = this,
			allowedTypes = 'image/jpeg|image/png|image/gif',
			dataTransfer = event.data.dataTransfer,
			editor = event.editor,
			count = dataTransfer.getFilesCount();
		for (let index = 0; index < count; index++) {
			let file = dataTransfer.getFile(index);
			if (file.type.match(allowedTypes)) {
				self
					.validateFile(file, editor)
					.done(function (base) {
						let image,
							selectedImg = editor.getSelection();
						if (selectedImg) selectedImg = selectedImg.getSelectedElement();
						if (!selectedImg || selectedImg.getName() !== 'img') selectedImg = null;
						if (selectedImg) {
							image = selectedImg;
						} else {
							image = editor.document.createElement('img');
						}
						image.setAttribute('src', base);
						if (!selectedImg) editor.insertElement(image);
					})
					.fail(function (error) {
						editor.showNotification(error, 'warning');
					});
			} else {
				editor.showNotification(
					app.vtranslate('JS_INVALID_FILE_TYPE') +
						'<br>' +
						app.vtranslate('JS_AVAILABLE_FILE_TYPES') +
						': ' +
						allowedTypes.replace(/\|/g, ', '),
					'warning'
				);
			}
		}
	},
	validateFile: function (file, editor) {
		const aDeferred = jQuery.Deferred();
		if (file.size > CONFIG['maxUploadLimit']) {
			aDeferred.reject(app.vtranslate('JS_UPLOADED_FILE_SIZE_EXCEEDS'));
		}
		this.readAndValidate(file, editor)
			.done(function (base) {
				aDeferred.resolve(base);
			})
			.fail(function (error) {
				aDeferred.reject(error);
			});
		return aDeferred.promise();
	},
	readAndValidate: function (file, editor) {
		const aDeferred = jQuery.Deferred(),
			fieldInfo = $(editor.element.$).data('fieldinfo');
		let length = editor.getData().length,
			selectedImg = editor.getSelection();
		if (selectedImg) selectedImg = selectedImg.getSelectedElement();
		if (!selectedImg || selectedImg.getName() !== 'img') selectedImg = null;
		if (selectedImg) {
			length = length - selectedImg.getOuterHtml().length;
		}
		const fileReader = new FileReader();
		fileReader.onload = function (evt) {
			length += evt.target.result.length;
			if (length > fieldInfo['maximumlength']) {
				return aDeferred.reject(app.vtranslate('JS_MAXIMUM_TEXT_SIZE_IN_BYTES') + ' ' + fieldInfo['maximumlength']);
			}
			AppConnector.request({
				module: app.getModuleName(),
				action: 'Fields',
				mode: 'validateFile',
				fieldName: fieldInfo['name'],
				base64: evt.target.result
			})
				.done((data) => {
					if (data.result.validate) {
						aDeferred.resolve(evt.target.result);
					} else {
						aDeferred.reject(data.result.validateError);
					}
				})
				.fail(function () {
					aDeferred.reject();
				});
		};
		fileReader.readAsDataURL(file);
		return aDeferred.promise();
	}
});
