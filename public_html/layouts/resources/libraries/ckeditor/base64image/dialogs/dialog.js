/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

CKEDITOR.dialog.add('base64image-dialog', function (editor) {
	let self = null,
		selectedImg = null,
		orgWidth = null,
		orgHeight = null,
		imgPreview = null,
		sourceElements = [],
		imgScale = 1,
		lock = true,
		maxUploadSize = CONFIG['maxUploadLimit'];

	/* Check File Reader Support */
	function fileSupport() {
		let r = false,
			n = null;
		try {
			if (FileReader) {
				let n = document.createElement('input');
				if (n && 'files' in n) r = true;
			}
		} catch (e) {
			r = false;
		}
		n = null;
		return r;
	}
	let isFReaderSupported = fileSupport();

	/* Load preview image */
	function imagePreviewLoad(s) {
		/* no preview */
		if (typeof s != 'string' || !s) {
			imgPreview.getElement().setHtml('');
			return;
		}

		/* Create image */
		let i = new Image();

		/* Display loading text in preview element */
		$(imgPreview.getElement().$).progressIndicator({ position: 'html' });

		/* When image is loaded */
		i.onload = function () {
			/* Remove preview */
			imgPreview.getElement().setHtml('');

			/* Set attributes */
			if (orgWidth == null || orgHeight == null) {
				self.setValueOf('tab-properties', 'width', this.width);
				self.setValueOf('tab-properties', 'height', this.height);
				imgScale = 1;
				if (this.height > 0 && this.width > 0) imgScale = this.width / this.height;
				if (imgScale <= 0) imgScale = 1;
			} else {
				orgWidth = null;
				orgHeight = null;
			}
			this.id = editor.id + 'previewimage';
			this.setAttribute('style', 'max-width:400px;max-height:100px;');
			this.setAttribute('alt', '');

			/* Insert preview image */
			try {
				let p = imgPreview.getElement().$;
				if (p) p.appendChild(this);
			} catch (e) {}
		};

		/* Error Function */
		i.onerror = function () {
			imgPreview.getElement().setHtml('');
		};
		i.onabort = function () {
			imgPreview.getElement().setHtml('');
		};

		/* Load image */
		i.src = s;
	}

	function imagePreview(src) {
		imgPreview.getElement().setHtml('');
		if (isFReaderSupported) {
			$(imgPreview.getElement().$).progressIndicator({ position: 'html' });
			readImageAsBase64()
				.done(function (base) {
					imgPreview.getElement().setHtml('');
					imagePreviewLoad(base);
				})
				.fail(function () {
					imgPreview.getElement().setHtml('');
				});
		}
	}

	function readImageAsBase64() {
		const aDeferred = jQuery.Deferred();
		let fileI = self.getContentElement('tab-source', 'file'),
			n = null;
		try {
			n = fileI.getInputElement().$;
		} catch (e) {
			n = null;
		}
		if (n && 'files' in n && n.files && n.files.length > 0 && n.files[0]) {
			if (('type' in n.files[0] && !n.files[0].type.match('image.*')) || !FileReader) {
				aDeferred.reject();
				return aDeferred.promise();
			}
			if (n.files[0].size > maxUploadSize) {
				app.showNotify({
					text: app.vtranslate('JS_UPLOADED_FILE_SIZE_EXCEEDS'),
					type: 'error'
				});
				aDeferred.reject();
				return aDeferred.promise();
			}
			let fr = new FileReader();
			fr.onload = (function (f) {
				return function (e) {
					aDeferred.resolve(e.target.result);
				};
			})(n.files[0]);
			fr.onerror = function () {
				aDeferred.reject();
			};
			fr.onabort = function () {
				aDeferred.reject();
			};
			fr.readAsDataURL(n.files[0]);
		}
		return aDeferred.promise();
	}

	function getImageDimensions() {
		let o = {
			w: self.getContentElement('tab-properties', 'width').getValue(),
			h: self.getContentElement('tab-properties', 'height').getValue(),
			uw: 'px',
			uh: 'px'
		};
		if (o.w.indexOf('%') >= 0) o.uw = '%';
		if (o.h.indexOf('%') >= 0) o.uh = '%';
		o.w = parseInt(o.w, 10);
		o.h = parseInt(o.h, 10);
		if (isNaN(o.w)) o.w = 0;
		if (isNaN(o.h)) o.h = 0;
		return o;
	}

	function imageDimensions(src) {
		let o = getImageDimensions();
		let u = 'px';
		if (src == 'width') {
			if (o.uw == '%') u = '%';
			o.h = Math.round(o.w / imgScale);
		} else {
			if (o.uh == '%') u = '%';
			o.w = Math.round(o.h * imgScale);
		}
		if (u == '%') {
			o.w += '%';
			o.h += '%';
		}
		self.getContentElement('tab-properties', 'width').setValue(o.w),
			self.getContentElement('tab-properties', 'height').setValue(o.h);
	}

	function integerValue(elem) {
		let v = elem.getValue(),
			u = '';
		if (v.indexOf('%') >= 0) u = '%';
		v = parseInt(v, 10);
		if (isNaN(v)) v = 0;
		elem.setValue(v + u);
	}

	function validateFile() {
		const fieldInfo = $(editor.element.$).data('fieldinfo');
		let length = editor.getData().length,
			selectedImg = editor.getSelection();
		if (selectedImg) selectedImg = selectedImg.getSelectedElement();
		if (!selectedImg || selectedImg.getName() !== 'img') selectedImg = null;
		if (selectedImg) {
			length = length - selectedImg.getOuterHtml().length;
		}
		const aDeferred = jQuery.Deferred();
		readImageAsBase64()
			.done((base) => {
				length += base.length;
				if (length > fieldInfo['maximumlength']) {
					app.showNotify({
						text: app.vtranslate('JS_MAXIMUM_TEXT_SIZE_IN_BYTES') + ' ' + fieldInfo['maximumlength'],
						type: 'error'
					});
				}
				AppConnector.request({
					module: app.getModuleName(),
					action: 'Fields',
					mode: 'validateFile',
					fieldName: fieldInfo['name'],
					base64: base
				})
					.done((data) => {
						if (data.result.validate) {
							aDeferred.resolve();
						} else {
							app.showNotify({
								text: data.result.validateError,
								type: 'error'
							});
							aDeferred.reject();
						}
					})
					.fail(function () {
						aDeferred.resolve();
					});
			})
			.fail(() => {
				aDeferred.reject();
			});
		return aDeferred.promise();
	}

	if (isFReaderSupported) {
		sourceElements = [
			{
				type: 'hbox',
				widths: ['70px'],
				style: 'margin-top:40px;',
				children: [
					{
						type: 'file',
						id: 'file',
						label: '',
						size: maxUploadSize,
						onChange: function (a) {
							validateFile()
								.done(() => {
									imagePreview('file');
								})
								.fail(function () {
									self.getContentElement('tab-source', 'file').getInputElement().$.value = null;
									imgPreview.getElement().setHtml('');
								});
						}
					}
				]
			},
			{
				type: 'html',
				id: 'preview',
				html: new CKEDITOR.template('<div style="text-align:center;"></div>').output()
			}
		];
	}
	/* Dialog */
	return {
		title: editor.lang.common.image,
		minWidth: 450,
		minHeight: 180,
		onLoad: function () {
			/* Get image preview element */
			imgPreview = this.getContentElement('tab-source', 'preview');

			/* Constrain proportions or not */
			this.getContentElement('tab-properties', 'lock')
				.getInputElement()
				.on(
					'click',
					function () {
						if (this.getValue()) lock = true;
						else lock = false;
						if (lock) imageDimensions('width');
					},
					this.getContentElement('tab-properties', 'lock')
				);

			/* Change Attributes Events  */
			this.getContentElement('tab-properties', 'width')
				.getInputElement()
				.on('keyup', function () {
					if (lock) imageDimensions('width');
				});
			this.getContentElement('tab-properties', 'height')
				.getInputElement()
				.on('keyup', function () {
					if (lock) imageDimensions('height');
				});
			this.getContentElement('tab-properties', 'vmargin')
				.getInputElement()
				.on(
					'keyup',
					function () {
						integerValue(this);
					},
					this.getContentElement('tab-properties', 'vmargin')
				);
			this.getContentElement('tab-properties', 'hmargin')
				.getInputElement()
				.on(
					'keyup',
					function () {
						integerValue(this);
					},
					this.getContentElement('tab-properties', 'hmargin')
				);
			this.getContentElement('tab-properties', 'border')
				.getInputElement()
				.on(
					'keyup',
					function () {
						integerValue(this);
					},
					this.getContentElement('tab-properties', 'border')
				);
		},
		onShow: function () {
			this.getContentElement('tab-source', 'file')
				.getInputElement()
				.$.setAttribute('accept', 'image/jpeg, image/png, image/gif');
			/* Remove preview */
			imgPreview.getElement().setHtml('');

			(self = this), (orgWidth = null), (orgHeight = null), (imgScale = 1), (lock = true);

			/* selected image or null */
			selectedImg = editor.getSelection();
			if (selectedImg) selectedImg = selectedImg.getSelectedElement();
			if (!selectedImg || selectedImg.getName() !== 'img') selectedImg = null;

			/* Set input values */
			self.setValueOf('tab-properties', 'lock', lock);
			self.setValueOf('tab-properties', 'vmargin', '0');
			self.setValueOf('tab-properties', 'hmargin', '0');
			self.setValueOf('tab-properties', 'border', '0');
			self.setValueOf('tab-properties', 'align', 'none');
			if (selectedImg) {
				/* Set input values from selected image */
				if (typeof selectedImg.getAttribute('width') == 'string') orgWidth = selectedImg.getAttribute('width');
				if (typeof selectedImg.getAttribute('height') == 'string') orgHeight = selectedImg.getAttribute('height');
				if ((orgWidth == null || orgHeight == null) && selectedImg.$) {
					orgWidth = selectedImg.$.width;
					orgHeight = selectedImg.$.height;
				}
				if (orgWidth != null && orgHeight != null) {
					self.setValueOf('tab-properties', 'width', orgWidth);
					self.setValueOf('tab-properties', 'height', orgHeight);
					orgWidth = parseInt(orgWidth, 10);
					orgHeight = parseInt(orgHeight, 10);
					imgScale = 1;
					if (!isNaN(orgWidth) && !isNaN(orgHeight) && orgHeight > 0 && orgWidth > 0) imgScale = orgWidth / orgHeight;
					if (imgScale <= 0) imgScale = 1;
				}

				if (typeof selectedImg.getAttribute('src') == 'string') {
					if (selectedImg.getAttribute('src').indexOf('data:') === 0) {
						imagePreview('base64');
						imagePreviewLoad(selectedImg.getAttribute('src'));
					}
				}
				if (typeof selectedImg.getAttribute('alt') == 'string')
					self.setValueOf('tab-properties', 'alt', selectedImg.getAttribute('alt'));
				if (typeof selectedImg.getAttribute('hspace') == 'string')
					self.setValueOf('tab-properties', 'hmargin', selectedImg.getAttribute('hspace'));
				if (typeof selectedImg.getAttribute('vspace') == 'string')
					self.setValueOf('tab-properties', 'vmargin', selectedImg.getAttribute('vspace'));
				if (typeof selectedImg.getAttribute('border') == 'string')
					self.setValueOf('tab-properties', 'border', selectedImg.getAttribute('border'));
				if (typeof selectedImg.getAttribute('align') == 'string') {
					switch (selectedImg.getAttribute('align')) {
						case 'top':
						case 'text-top':
							self.setValueOf('tab-properties', 'align', 'top');
							break;
						case 'baseline':
						case 'bottom':
						case 'text-bottom':
							self.setValueOf('tab-properties', 'align', 'bottom');
							break;
						case 'left':
							self.setValueOf('tab-properties', 'align', 'left');
							break;
						case 'right':
							self.setValueOf('tab-properties', 'align', 'right');
							break;
					}
				}
				self.selectPage('tab-properties');
			}
		},
		onOk: function () {
			/* Get image source */
			let src = '';
			try {
				src = CKEDITOR.document.getById(editor.id + 'previewimage').$.src;
			} catch (e) {
				src = '';
			}
			if (typeof src != 'string' || src == null || src === '') return;

			validateFile().always(() => {
				/* selected image or new image */
				if (selectedImg) {
					var newImg = selectedImg;
				} else {
					var newImg = editor.document.createElement('img');
				}
				newImg.setAttribute('src', src);
				src = null;

				/* Set attributes */
				newImg.setAttribute('alt', self.getValueOf('tab-properties', 'alt').replace(/^\s+/, '').replace(/\s+$/, ''));
				let attr = {
						width: ['width', 'width:#;', 'integer', 1],
						height: ['height', 'height:#;', 'integer', 1],
						vmargin: ['vspace', 'margin-top:#;margin-bottom:#;', 'integer', 0],
						hmargin: ['hspace', 'margin-left:#;margin-right:#;', 'integer', 0],
						align: ['align', ''],
						border: ['border', 'border:# solid black;', 'integer', 0]
					},
					css = [],
					value,
					cssValue,
					attrValue,
					unit,
					k;
				for (k in attr) {
					value = self.getValueOf('tab-properties', k);
					attrValue = value;
					cssValue = value;
					unit = 'px';

					if (k == 'align') {
						switch (value) {
							case 'top':
							case 'bottom':
								attr[k][1] = 'vertical-align:#;';
								break;
							case 'left':
							case 'right':
								attr[k][1] = 'float:#;';
								break;
							default:
								value = null;
								break;
						}
					}

					if (attr[k][2] == 'integer') {
						if (value.indexOf('%') >= 0) unit = '%';
						value = parseInt(value, 10);
						if (isNaN(value)) value = null;
						else if (value < attr[k][3]) value = null;
						if (value != null) {
							if (unit == '%') {
								attrValue = value + '%';
								cssValue = value + '%';
							} else {
								attrValue = value;
								cssValue = value + 'px';
							}
						}
					}

					if (value != null) {
						newImg.setAttribute(attr[k][0], attrValue);
						css.push(attr[k][1].replace(/#/g, cssValue));
					}
				}
				if (css.length > 0) newImg.setAttribute('style', css.join(''));

				/* Insert new image */
				if (!selectedImg) editor.insertElement(newImg);

				/* Resize image */
				if (editor.plugins.imageresize) editor.plugins.imageresize.resize(editor, newImg, 800, 800);

				editor.updateElement();
			});
		},

		/* Dialog form */
		contents: [
			{
				id: 'tab-source',
				label: editor.lang.common.generalTab,
				elements: sourceElements
			},
			{
				id: 'tab-properties',
				label: editor.lang.common.advancedTab,
				elements: [
					{
						type: 'text',
						id: 'alt',
						label: editor.lang.image.alt
					},
					{
						type: 'hbox',
						widths: ['15%', '15%', '70%'],
						children: [
							{
								type: 'text',
								width: '45px',
								id: 'width',
								label: editor.lang.common.width
							},
							{
								type: 'text',
								width: '45px',
								id: 'height',
								label: editor.lang.common.height
							},
							{
								type: 'checkbox',
								id: 'lock',
								label: editor.lang.image.lockRatio,
								style: 'margin-top:18px;'
							}
						]
					},
					{
						type: 'hbox',
						widths: ['23%', '30%', '30%', '17%'],
						style: 'margin-top:10px;',
						children: [
							{
								type: 'select',
								id: 'align',
								label: editor.lang.common.align,
								items: [
									[editor.lang.common.notSet, 'none'],
									[editor.lang.common.alignTop, 'top'],
									[editor.lang.common.alignBottom, 'bottom'],
									[editor.lang.common.alignLeft, 'left'],
									[editor.lang.common.alignRight, 'right']
								]
							},
							{
								type: 'text',
								width: '45px',
								id: 'vmargin',
								label: editor.lang.image.vSpace
							},
							{
								type: 'text',
								width: '45px',
								id: 'hmargin',
								label: editor.lang.image.hSpace
							},
							{
								type: 'text',
								width: '45px',
								id: 'border',
								label: editor.lang.image.border
							}
						]
					}
				]
			}
		]
	};
});
