/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

class FileUpload {

	constructor(inputElement) {
		const thisInstance = this;
		this.files = [];
		this.component = $(inputElement).closest('.c-multi-image').eq(0);
		$(inputElement).fileupload({
			dataType: 'json',
			autoUpload: false,
			acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
			// event handlers
			done: thisInstance.done.bind(thisInstance),
			submit: thisInstance.submit.bind(thisInstance),
			add: thisInstance.add.bind(thisInstance),
			progressall: thisInstance.progressAll.bind(thisInstance),
			change: thisInstance.change.bind(thisInstance),
			drop: thisInstance.change.bind(thisInstance),
		});
		$(inputElement).fileupload('option', 'dropZone', $(this.component));
		$(this.component).on('click', '.c-multi-image__preview__popover-img', function (e) {
			thisInstance.zoomPreview($(this).data('hash'));
		});
		$(this.component).on('click', '.c-multi-image__preview__popover-btn-zoom', function (e) {
			thisInstance.zoomPreview($(this).data('hash'));
		});
		$(this.component).on('dblclick', '.c-multi-image__preview-img', function (e) {
			thisInstance.zoomPreview($(this).data('hash'));
		});
		$(this.component).on('click', '.c-multi-image__preview__popover-btn-delete', function (e) {
			thisInstance.deleteFile($(this).data('hash'));
		});
	}

	done(e, data) {
		$.each(data.result.files, function (index, file) {
			console.log('file', file);
		});
	}

	submit(e, data) {
		data.formData = {
			hash: data.files[0].hash // fileupload send only one file per request
		};
	}

	getFileInfo(hash) {
		for (let i = 0, len = this.files.length; i < len; i++) {
			const file = this.files[i];
			if (file.hash === hash) {
				return file;
			}
		}
	}

	addFileInfoProperty(hash, propertyName, value) {
		const fileInfo = this.getFileInfo(hash);
		fileInfo[propertyName] = value;
		return fileInfo;
	}

	add(e, data) {
		data.files.forEach((file) => {
			if (typeof file.hash === 'undefined') {
				file.hash = App.Fields.Text.generateRandomHash(CONFIG.userId);
				this.files.push({hash: file.hash, imageSrc: file.imageSrc, name: file.name, file});
			}
		});
		$(this.component).find('.c-multi-image__progress').removeClass('d-none');
		data.submit()
			.success((result, textStatus, jqXHR) => {
				console.log('upload success', this);
				$(this.component).find('.c-multi-image__progress').addClass('d-none');
			})
			.error((jqXHR, textStatus, errorThrown) => {
				console.log('error', this, errorThrown.message);
			})
			.complete((result, textStatus, jqXHR) => {
				console.log('upload complete', this, textStatus);
				$(this.component).find('.c-multi-image__progress').addClass('d-none');
			});
	}

	progressAll(e, data) {
		const progress = parseInt(data.loaded / data.total * 100, 10);
		$(this.component).find('.c-multi-image__progress-bar').css({width: progress + "%"});
	}

	/**
	 * Display modal window with large preview
	 *
	 * @param {string} hash
	 */
	zoomPreview(hash) {
		const thisInstance = this;
		const fileInfo = this.getFileInfo(hash);
		console.log(fileInfo)
		bootbox.dialog({
			size: 'large',
			backdrop: true,
			onEscape: true,
			title: `<i class="fa fa-image"></i> ${fileInfo.name}`,
			message: `<img src="${fileInfo.imageSrc}" class="w-100" />`,
			buttons: {
				Delete: {
					label: `<i class="fa fa-trash-alt"></i> ${app.vtranslate('JS_DELETE')}`,
					className: "float-left btn btn-danger",
					callback() {
						thisInstance.deleteFile(fileInfo.hash);
					}
				},
				Close: {
					label: `<i class="fa fa-times"></i> ${app.vtranslate('JS_CLOSE')}`,
					className: "btn btn-default",
					callback: () => {
					},
				}
			}
		});

	}

	/**
	 * Delete image from input field
	 * Should be called with this pointing on button element with data-hash attribute
	 * @param {string} hash
	 * @param {array} files - files that need to be send with request
	 */
	deleteFile(hash) {
		const fileInfo = this.getFileInfo(hash);
		bootbox.confirm({
			title: `<i class="fa fa-trash-alt"></i> ${app.vtranslate("JS_DELETE_FILE")}`,
			message: `${app.vtranslate("JS_DELETE_FILE_CONFIRMATION")} <span class="font-weight-bold">${fileInfo.name}</span>?`,
			callback: function (result) {
				if (result) {
					fileInfo.previewElement.popover('dispose').remove();

				}
			}
		});
	}

	/**
	 * File change event
	 * Should be called with this pointing on file input element inside .c-multi-image
	 *
	 * @param {Event} e
	 * @param {object} data
	 */
	change(e, data) {
		console.log('change', data);
		this.generatePreviewElements(data.files, (element) => {
			console.log('adding element', element)
			$(this.component).find('.c-multi-image__result').append(element);
		});
	}

	/**
	 * Generate and apply popover to preview
	 *
	 * @param {File} file
	 * @param {string} template
	 * @param {string} imageSrc
	 * @returns {*|jQuery}
	 */
	addPreviewPopover(file, template, imageSrc) {
		const thisInstance = this;
		return $(template).popover({
			container: thisInstance.component,
			title: `<div class="u-text-ellipsis"><i class="fa fa-image"></i> ${file.name}</div>`,
			html: true,
			trigger: 'focus',
			placement: 'top',
			content: `<img src="${imageSrc}" class="w-100 c-multi-image__preview__popover-img" data-hash="${file.hash}" />`,
			template: `<div class="popover" role="tooltip">
				<div class="arrow"></div>
				<h3 class="popover-header"></h3>
				<div class="popover-body"></div>
				<div class="text-right popover-footer c-multi-image__preview__popover-actions">
					<button class="btn btn-sm btn-danger c-multi-image__preview__popover-btn-delete" data-hash="${file.hash}"><i class="fa fa-trash-alt"></i> ${app.vtranslate('JS_DELETE')}</button>
					<button class="btn btn-sm btn-primary c-multi-image__preview__popover-btn-zoom" data-hash="${file.hash}"><i class="fa fa-search-plus"></i> ${app.vtranslate('JS_ZOOM_IN')}</button>
				</div>
			</div>`
		});
	}

	/**
	 * Generate preview of images and append to multi image results view
	 *
	 * @param {Array} files - array of Files
	 * @param {function} callback
	 */
	generatePreviewElements(files, callback) {
		files.forEach((file) => {
			if (file instanceof File) {
				this.generatePreviewFromFile(file, (template, imageSrc) => {
					file.preview = this.addPreviewPopover(file, template, imageSrc);
					this.addFileInfoProperty(file.hash, 'previewElement', file.preview);
					callback(file.preview);
				});
			} else {

			}
		});
	}

	/**
	 * Generate preview of image as html string
	 * @param {File} file
	 * @param {function} callback
	 */
	generatePreviewFromFile(file, callback) {
		const fr = new FileReader();
		fr.onload = () => {
			file.imageSrc = fr.result;
			this.addFileInfoProperty(file.hash, 'imageSrc', file.imageSrc);
			callback(`<div class="d-inline-block mr-1 mb-1 c-multi-image__preview" id="c-multi-image__preview-hash-${file.hash}" data-hash="${file.hash}">
					<div class="img-thumbnail c-multi-image__preview-img" data-hash="${file.hash}" style="background-image:url(${fr.result})" tabindex="0" title="${file.name}"></div>
			</div>`, fr.result);
		};
		fr.readAsDataURL(file);
	}

}