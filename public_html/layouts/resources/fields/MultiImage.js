/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

class MultiImage {
	/**
	 * Create class instance
	 *
	 * @param {HTMLElement|jQuery} inputElement - input type file element inside component
	 */
	constructor(element) {
		const thisInstance = this;
		this.elements = {};
		this.options = {
			showCarousel: true
		};
		this.detailView = false;
		this.elements.fileInput = element.find('.js-multi-image__file').eq(0);
		if (this.elements.fileInput.length === 0) {
			this.detailView = true;
		}
		this.elements.component = element.eq(0);
		this.elements.form = element.closest('form').eq(0);
		$(this.elements.form).on('submit', this.onFormSubmit);
		this.elements.addButton = this.elements.component.find('.js-multi-image__file-btn').eq(0);
		this.elements.values = this.elements.component.find('.js-multi-image__values').eq(0);
		this.elements.progressBar = this.elements.component.find('.js-multi-image__progress-bar').eq(0);
		this.elements.progress = this.elements.component.find('.js-multi-image__progress').eq(0);
		this.elements.result = this.elements.component.find('.js-multi-image__result').eq(0);
		this.fieldInfo = this.elements.values.data('fieldinfo');
		this.options.formats = this.fieldInfo.formats;
		this.options.limit = this.fieldInfo.limit;
		this.options.maxFileSize = this.fieldInfo.maxFileSize || CONFIG.maxUploadLimit;
		this.options.maxFileSizeDisplay = this.fieldInfo.maxFileSizeDisplay || '';
		if (!this.detailView) {
			this.files = JSON.parse(this.elements.values.val());
		} else {
			this.files = this.elements.values.data('value');
		}
		if (!this.detailView) {
			this.elements.fileInput.detach();
			this.elements.addButton.click(this.addButtonClick.bind(this));
			this.elements.fileInput.fileupload({
				dataType: 'json',
				replaceFileInput: false,
				fileInput: this.fileInput,
				autoUpload: false,
				submit: this.submit.bind(this),
				add: this.add.bind(this),
				progressall: this.progressAll.bind(this),
				change: this.change.bind(this),
				drop: this.change.bind(this),
				dragover: this.dragOver.bind(this),
				fail: this.uploadError.bind(this),
				done: this.uploadSuccess.bind(this)
			});
			this.elements.component.on('dragleave', this.dragLeave.bind(this));
			this.elements.component.on('dragend', this.dragLeave.bind(this));
			this.elements.fileInput.fileupload('option', 'dropZone', $(this.elements.component));
			this.enableDragNDrop();
		}
		this.elements.component.on('click', '.js-multi-image__popover-img', function () {
			thisInstance.zoomPreview($(this).data('hash'));
		});
		this.elements.component.on('click', '.js-multi-image__popover-btn-zoom', function (e) {
			e.preventDefault();
			thisInstance.zoomPreview($(this).data('hash'));
		});
		this.elements.component.on('click', '.js-multi-image__popover-btn-download', function (e) {
			e.preventDefault();
			thisInstance.download($(this).data('hash'));
		});
		if (!this.detailView) {
			this.elements.component.on('click', '.js-multi-image__popover-btn-delete', function (e) {
				e.preventDefault();
				thisInstance.deleteFile($(this).data('hash'));
			});
		}
		this.loadExistingFiles();
	}

	/**
	 * Prevent form submission before file upload end
	 * @param e
	 */
	onFormSubmit(e) {
		if (App.Fields.MultiImage.currentFileUploads) {
			e.preventDefault();
			e.stopPropagation();
			e.stopImmediatePropagation();
			app.showAlert(app.vtranslate('JS_WAIT_FOR_FILE_UPLOAD'));
			return false;
		}
		return true;
	}

	/**
	 * Prevent form submission
	 *
	 * @param {Event} e
	 */
	addButtonClick(e) {
		e.preventDefault();
		this.elements.fileInput.trigger('click');
	}

	/**
	 * Submit event handler from jQuery-file-upload
	 *
	 * @param {Event} e
	 * @param {Object} data
	 */
	submit(_e, data) {
		data.formData = {
			hash: data.files[0].hash
		};
		App.Fields.MultiImage.currentFileUploads++;
		this.progressInstance = $.progressIndicator({
			position: 'replace',
			blockInfo: {
				enabled: true,
				elementToBlock: this.elements.component
			}
		});
	}

	/**
	 * Get file information
	 *
	 * @param {String} hash - file id
	 * @returns {Object}
	 */
	getFileInfo(hash) {
		for (let i = 0, len = this.files.length; i < len; i++) {
			const file = this.files[i];
			if (file.hash === hash) {
				return file;
			}
		}
		app.errorLog(`File '${hash}' not found.`);
		app.showNotify({
			text: app.vtranslate('JS_INVALID_FILE_HASH') + ` [${hash}]`,
			type: 'error'
		});
	}

	/**
	 * Add property to file info object
	 *
	 * @param {String} hash - file id
	 * @param {String} propertyName
	 * @param {any} value
	 * @returns {Object}
	 */
	addFileInfoProperty(hash, propertyName, value) {
		const fileInfo = this.getFileInfo(hash);
		fileInfo[propertyName] = value;
		return fileInfo;
	}

	/**
	 * Error event handler from file upload request
	 *
	 * @param {Event} e
	 * @param {Object} data
	 */
	uploadError(_e, data) {
		this.progressInstance.progressIndicator({ mode: 'hide' });
		app.errorLog('File upload error.');
		const { jqXHR, files } = data;
		const response = jqXHR.responseJSON;
		// first try to show error for concrete file
		if (
			response !== null &&
			typeof response !== 'undefined' &&
			typeof response.result !== 'undefined' &&
			typeof response.result.attach !== 'undefined' &&
			Array.isArray(response.result.attach)
		) {
			response.result.attach.forEach((fileAttach) => {
				App.Fields.MultiImage.currentFileUploads--;
				this.deleteFile(fileAttach.hash, false);
				if (typeof fileAttach.error === 'string') {
					app.showNotify({
						textTrusted: false,
						text: fileAttach.error,
						type: 'error'
					});
				} else {
					app.showNotify({
						textTrusted: false,
						title: app.vtranslate('JS_FILE_UPLOAD_ERROR'),
						text: fileAttach.name,
						type: 'error'
					});
				}
			});
			this.updateFormValues();
			return;
		}
		// else show default upload error
		files.forEach((file) => {
			App.Fields.MultiImage.currentFileUploads--;
			this.deleteFile(file.hash, false);
			app.showNotify({
				textTrusted: false,
				title: app.vtranslate('JS_FILE_UPLOAD_ERROR'),
				text: file.name,
				type: 'error'
			});
		});
		this.updateFormValues();
	}

	/**
	 * Success event handler from file upload request
	 *
	 * @param {Event} e
	 * @param {Object} data
	 */
	uploadSuccess(e, data) {
		this.progressInstance.progressIndicator({ mode: 'hide' });
		const { result } = data;
		const attach = result.result.attach;
		attach.forEach((fileAttach) => {
			const hash = fileAttach.hash;
			if (!hash) {
				return app.errorLog(new Error(app.vtranslate('JS_INVALID_FILE_HASH')));
			}
			if (typeof fileAttach.key === 'undefined') {
				return this.uploadError(e, data);
			}
			if (typeof fileAttach.info !== 'undefined' && fileAttach.info) {
				app.showNotify({
					textTrusted: false,
					type: 'notice',
					title: fileAttach.info,
					text: fileAttach.name
				});
			}
			const fileInfo = this.getFileInfo(hash);
			this.addFileInfoProperty(hash, 'key', fileAttach.key);
			this.addFileInfoProperty(hash, 'size', fileAttach.size);
			this.addFileInfoProperty(hash, 'sizeDisplay', fileAttach.sizeDisplay || fileAttach.size);
			this.addFileInfoProperty(hash, 'name', fileAttach.name);
			this.addFileInfoProperty(hash, 'type', fileAttach.type || '');
			this.generatePreviewFromFile(fileInfo.file, (template, imageSrc) => {
				this.addFileInfoProperty(hash, 'previewElement', this.addPreviewPopover(fileInfo, template, imageSrc));
				this.redraw();
			});
			App.Fields.MultiImage.currentFileUploads--;
		});
		this.updateFormValues();
	}

	/**
	 * Update form input values
	 */
	updateFormValues() {
		this.elements.fileInput.val(null);
		const formValues = this.files.map((file) => {
			return { key: file.key, name: file.name, size: file.size, type: file.type || '' };
		});
		this.elements.values.val(JSON.stringify(formValues));
	}

	/**
	 * Validate file
	 *
	 * @param {Object} file
	 * @returns {boolean}
	 */
	validateFormat(file) {
		let valid = false;
		this.options.formats.forEach((format) => {
			if (file.type === 'image/' + format) {
				valid = true;
			}
		});
		if (!valid) {
			app.showNotify({
				title: `${app.vtranslate('JS_INVALID_FILE_TYPE')}`,
				text: `${app.vtranslate('JS_AVAILABLE_FILE_TYPES')}: ${this.options.formats.join(', ')}`,
				type: 'error'
			});
		}
		return valid;
	}

	/**
	 * Validate maximum file size
	 * @param {Object} file
	 * @returns {Boolean}
	 */
	validateSize(file) {
		let result = typeof file.size === 'number' && file.size < this.options.maxFileSize;
		if (!result) {
			app.showNotify({
				text: `${app.vtranslate('JS_UPLOADED_FILE_SIZE_EXCEEDS')} <br> [${this.options.maxFileSizeDisplay}]`,
				type: 'error'
			});
		}
		return result;
	}

	/**
	 * Show limit error
	 */
	showLimitError() {
		this.elements.fileInput.val('');
		app.showNotify({
			text: `${app.vtranslate('JS_FILE_LIMIT')} [${this.options.limit}]`,
			type: 'error'
		});
	}

	/**
	 * Get only valid files from list
	 *
	 * @param {Array} files
	 * @returns {Array}
	 */
	filterValidFiles(files) {
		if (files.length + this.files.length > this.options.limit) {
			this.showLimitError();
			return [];
		}
		return files.filter((file) => {
			return this.validateFormat(file) && this.validateSize(file);
		});
	}

	/**
	 * Set files hash
	 * @param {Array} files
	 * @returns {Array}
	 */
	setFilesHash(files) {
		const addedFiles = [];
		for (let i = 0, len = files.length; i < len; i++) {
			const file = files[i];
			if (typeof file.hash === 'undefined') {
				if (this.files.length < this.options.limit) {
					file.hash = App.Fields.Text.generateRandomHash(CONFIG.userId);
					this.files.push({ hash: file.hash, imageSrc: file.imageSrc, name: file.name, file });
					addedFiles.push(file);
				} else {
					this.showLimitError();
					return addedFiles;
				}
			}
		}
		return addedFiles;
	}

	/**
	 * Add event handler from jQuery-file-upload
	 *
	 * @param {Event} e
	 * @param {object} data
	 */
	add(_e, data) {
		if (data.files.length > 0) {
			data.submit();
		}
	}

	/**
	 * Progressall event handler from jQuery-file-upload
	 *
	 * @param {Event} e
	 * @param {Object} data
	 */
	progressAll(_e, data) {
		const progress = parseInt((data.loaded / data.total) * 100, 10);
		this.elements.progressBar.css({ width: progress + '%' });
		if (progress === 100) {
			setTimeout(() => {
				this.elements.progress.addClass('d-none');
				this.elements.progressBar.css({ width: '0%' });
			}, 1000);
		} else {
			this.elements.progress.removeClass('d-none');
		}
	}

	/**
	 * Dragover event handler from jQuery-file-upload
	 *
	 * @param {Event} e
	 */
	dragOver(_e) {
		this.elements.component.addClass('c-multi-image__drop-effect');
	}

	/**
	 * Dragleave event handler
	 * @param {Event} e
	 */
	dragLeave(_e) {
		this.elements.component.removeClass('c-multi-image__drop-effect');
	}

	/**
	 * Download file according to source type (base64/file from server)
	 *
	 * @param {String} hash
	 */
	download(hash) {
		const fileInfo = this.getFileInfo(hash);
		if (fileInfo.imageSrc.substr(0, 8).toLowerCase() === 'file.php') {
			return this.downloadFile(hash);
		} else {
			return this.downloadBase64(hash);
		}
	}

	/**
	 * Download file that exists on the server already
	 * @param {String} hash
	 */
	downloadFile(hash) {
		const fileInfo = this.getFileInfo(hash);
		const link = document.createElement('a');
		$(link).css('display', 'none');
		if (typeof link.download === 'string') {
			document.body.appendChild(link); // Firefox requires the link to be in the body
			link.download = fileInfo.name;
			link.href = fileInfo.imageSrc;
			link.click();
			document.body.removeChild(link); // remove the link when done
		} else {
			location.replace(fileInfo.imageSrc);
		}
	}

	/**
	 * Download file from base64 image
	 *
	 * @param {String} hash
	 */
	downloadBase64(hash) {
		const fileInfo = this.getFileInfo(hash);
		const imageUrl =
			`data:application/octet-stream;filename=${fileInfo.name};base64,` + fileInfo.imageSrc.split(',')[1];
		const link = document.createElement('a');
		$(link).css('display', 'none');
		if (typeof link.download === 'string') {
			document.body.appendChild(link); // Firefox requires the link to be in the body
			link.download = fileInfo.name;
			link.href = imageUrl;
			link.click();
			document.body.removeChild(link); // remove the link when done
		} else {
			location.replace(imageUrl);
		}
	}

	/**
	 * Display modal window with large preview
	 *
	 * @param {string} hash
	 */
	zoomPreview(hash) {
		const self = this;
		let fileInfo = this.getFileInfo(hash);
		const titleTemplate = () => {
			const titleObject = document.createElement('span');
			const icon = document.createElement('i');
			icon.setAttribute('class', `fa fa-image`);
			titleObject.appendChild(icon);
			titleObject.appendChild(document.createTextNode(` ${fileInfo.name}`));
			return titleObject.innerHTML;
		};

		let buttons = [];
		if (!self.detailView) {
			buttons.push({
				text: app.vtranslate('JS_DELETE'),
				icon: 'fa fa-trash-alt',
				class: 'float-left btn btn-danger js-delete'
			});
		}
		buttons.push(
			{
				text: app.vtranslate('JS_DOWNLOAD'),
				icon: 'fa fa-download',
				class: 'float-left btn btn-success js-success'
			},
			{
				text: app.vtranslate('JS_CLOSE'),
				icon: 'fa fa-times',
				class: 'btn btn-warning',
				data: { dismiss: 'modal' }
			}
		);
		app.showModalHtml({
			class: 'modal-lg',
			header: titleTemplate(),
			footerButtons: buttons,
			body: self.options.showCarousel
				? self.generateCarousel(hash)
				: `<img src="${fileInfo.imageSrc}" class="w-100" />`,
			cb: (modal) => {
				modal.on('click', '.js-delete', function () {
					self.deleteFile(fileInfo.hash);
					app.hideModalWindow();
				});
				modal.on('click', '.js-success', function () {
					self.download(fileInfo.hash);
					app.hideModalWindow();
				});
				if (self.options.showCarousel) {
					modal.find(`#carousel-${hash}`).on('slid.bs.carousel', (e) => {
						fileInfo = self.getFileInfo($(e.relatedTarget).data('hash'));
						modal.find('.js-modal-title').html(titleTemplate());
					});
				}
			}
		});
	}

	/**
	 * Remove file from preview and from file list
	 *
	 * @param {String} hash
	 */
	deleteFileCallback(hash) {
		const fileInfo = this.getFileInfo(hash);
		if (fileInfo.previewElement) {
			fileInfo.previewElement.popover('dispose').remove();
		}
		this.files = this.files.filter((file) => file.hash !== fileInfo.hash);
		this.updateFormValues();
	}

	/**
	 * Delete image from input field
	 * Should be called with this pointing on button element with data-hash attribute
	 *
	 * @param {string} hash
	 * @param {boolean} showConfirmation - dialog?
	 */
	deleteFile(hash, showConfirmation = true) {
		if (showConfirmation) {
			const fileInfo = this.getFileInfo(hash);
			app.showConfirmModal({
				title: fileInfo.name,
				text: app.vtranslate('JS_DELETE_FILE_CONFIRMATION'),
				titleTrusted: false,
				icon: 'fa fa-trash-alt',
				confirmedCallback: () => {
					this.deleteFileCallback(hash);
				}
			});
		} else {
			this.deleteFileCallback(hash);
		}
	}

	/**
	 * File change event handler from jQuery-file-upload
	 *
	 * @param {Event} e
	 * @param {object} data
	 */
	change(e, data) {
		data.files = this.filterValidFiles(data.files);
		data.files = this.setFilesHash(data.files);
		this.dragLeave(e);
	}

	/**
	 * Generate and apply popover to preview
	 *
	 * @param {File} file
	 * @param {string} template
	 * @param {string} imageSrc
	 * @returns {jQuery}
	 */
	addPreviewPopover(file, template, imageSrc) {
		const thisInstance = this;
		let fileSize = '';
		const fileInfo = this.getFileInfo(file.hash);
		let size = fileInfo.sizeDisplay || fileInfo.size;
		if (size) {
			fileSize = `<div class="p-1 bg-white border rounded small position-absolute">${size}</div>`;
		}
		let deleteBtn = '';
		if (!this.detailView) {
			deleteBtn = `<button class="btn btn-sm btn-danger c-btn-collapsible js-multi-image__popover-btn-delete" type="button" data-hash="${
				file.hash
			}" data-js="click"><i class="fa fa-trash-alt"></i> <span class="c-btn-collapsible__text">${app.vtranslate(
				'JS_DELETE'
			)}</span></button>`;
		}

		const titleObject = document.createElement('span');
		titleObject.appendChild(document.createTextNode(fileInfo.name));

		return $(template).popover({
			container: thisInstance.elements.component,
			title: `<div class="u-text-ellipsis"><i class="fa fa-image"></i> ` + titleObject.innerHTML + `</div>`,
			html: true,
			sanitize: false,
			trigger: 'focus',
			placement: 'top',
			content: `<img src="${imageSrc}" class="w-100 js-multi-image__popover-img c-multi-image__popover-img" data-hash="${file.hash}" data-js="click"/>`,
			template: `<div class="popover" role="tooltip">
				<div class="arrow"></div>
				<h3 class="popover-header"></h3>
				<div class="popover-body"></div>
				<div class="text-right popover-footer js-multi-image__popover-actions">
					${fileSize}
					${deleteBtn}
					<button class="btn btn-sm btn-success c-btn-collapsible js-multi-image__popover-btn-download" type="button" data-hash="${
						file.hash
					}" data-js="click"><i class="fa fa-download"></i> <span class="c-btn-collapsible__text">${app.vtranslate(
				'JS_DOWNLOAD'
			)}</span></button>
					<button class="btn btn-sm btn-primary c-btn-collapsible js-multi-image__popover-btn-zoom" type="button" data-hash="${
						file.hash
					}" data-js="click"><i class="fa fa-search-plus"></i> <span class="c-btn-collapsible__text">${app.vtranslate(
				'JS_ZOOM_IN'
			)}</span></button>
				</div>
			</div>`
		});
	}

	/**
	 * Remove preview popover
	 *
	 * @param {String} hash
	 */
	removePreviewPopover(hash) {
		const fileInfo = this.getFileInfo(hash);
		if (typeof fileInfo.previewElement !== 'undefined') {
			fileInfo.previewElement.popover('dispose');
		}
	}

	/**
	 * Hide popovers when user starts moving file preview
	 *
	 * @param {Event} e
	 * @param {Object} ui
	 */
	sortOver(_e, _ui) {
		this.elements.result.find('.js-multi-image__preview').popover('hide');
	}

	/**
	 * Update file position according to elements order
	 *
	 * @param {Event} e
	 * @param {Object} ui
	 */
	sortStop(_e, _ui) {
		const actualElements = this.elements.result.find('.js-multi-image__preview').toArray();
		this.files = actualElements.map((element) => {
			for (let i = 0, len = this.files.length; i < len; i++) {
				const elementHash = $(element).data('hash');
				if (this.files[i].hash === elementHash) {
					return this.files[i];
				}
			}
		});
		this.redraw();
	}

	/**
	 * Redraw view according to in-memory positions
	 */
	redraw() {
		this.files.forEach((file) => {
			this.elements.result.append(file.previewElement);
		});
		this.updateFormValues();
	}

	/**
	 * Enable drag and drop files repositioning
	 */
	enableDragNDrop() {
		this.elements.result
			.sortable({
				handle: '.js-multi-image__preview-img',
				items: '.js-multi-image__preview',
				over: this.sortOver.bind(this),
				stop: this.sortStop.bind(this)
			})
			.disableSelection()
			.on('mousedown', '.js-multi-image__preview-img', function () {
				this.focus(); // focus to show popover
			});
	}

	/**
	 * Generate preview of images and append to multi image results view
	 *
	 * @param {Array} files - array of Files
	 * @param {function} callback
	 */
	generatePreviewElements(files, callback) {
		let preview = (file, template, imageSrc) => {
			file.preview = this.addPreviewPopover(file, template, imageSrc);
			this.addFileInfoProperty(file.hash, 'previewElement', file.preview);
			callback(file.preview);
		};
		files.forEach((file) => {
			if (file instanceof File) {
				this.generatePreviewFromFile(file, (template, imageSrc) => {
					preview(file, template, imageSrc);
				});
			} else {
				this.generatePreviewFromValue(file, (template, imageSrc) => {
					preview(file, template, imageSrc);
				});
			}
		});
	}

	/**
	 * Generate preview of image as html string
	 *
	 * @param {File} file
	 * @param {function} callback
	 */
	generatePreviewFromFile(file, callback) {
		const fr = new FileReader();
		fr.onload = () => {
			file.imageSrc = fr.result;
			this.addFileInfoProperty(file.hash, 'imageSrc', file.imageSrc);
			callback(this.createPreview(file), fr.result);
		};
		fr.readAsDataURL(file);
	}

	/**
	 * Generate preview of image as html string from existing values
	 *
	 * @param {File} file
	 * @param {function} callback
	 */
	generatePreviewFromValue(file, callback) {
		callback(this.createPreview(file), file.imageSrc);
	}
	/**
	 * Create Preview element
	 * @param {Object} file
	 * @returns {String}
	 */
	createPreview(file) {
		const container = document.createElement('div');
		const item = document.createElement('div');
		item.setAttribute('class', 'd-inline-block mr-1 js-multi-image__preview');
		item.setAttribute('id', `js-multi-image__preview-hash-${file.hash}`);
		item.setAttribute('data-hash', file.hash);

		const subElement = document.createElement('div');
		subElement.setAttribute('class', 'img-thumbnail js-multi-image__preview-img c-multi-image__preview-img');
		subElement.setAttribute('data-hash', file.hash);
		subElement.setAttribute('style', `background-image:url(${file.imageSrc})`);
		subElement.setAttribute('tabindex', '0');
		subElement.setAttribute('title', file.name);
		item.appendChild(subElement);
		container.appendChild(item);

		return container.innerHTML;
	}

	/**
	 * Load files that were in valueInput as json string
	 */
	loadExistingFiles() {
		this.files = this.files
			.map((file) => {
				file.hash = App.Fields.Text.generateRandomHash(CONFIG.userId);
				return file;
			})
			.slice(0, this.options.limit);
		this.generatePreviewElements(this.files, (element) => {
			this.elements.result.append(element);
		});
		this.updateFormValues();
	}

	/**
	 * Generate carousel for all files in large preview
	 *
	 * @param {String} hash
	 */
	generateCarousel(hash) {
		if (this.files.length <= 1) {
			const fileInfo = this.getFileInfo(hash);
			return `<img class="d-block w-100" src="${fileInfo.imageSrc}">`;
		}
		let template = `<div id="carousel-${hash}" class="carousel slide c-carousel" data-ride="carousel" data-js="container">
		  <div class="carousel-inner">`;
		this.files.forEach((file) => {
			template += `<div class="carousel-item c-carousel__item`;
			if (file.hash === hash) {
				template += ` active`;
			}
			template += `" data-hash="${file.hash}">
		      <img class="d-block w-100 c-carousel__image" src="${file.imageSrc}">
		    </div>`;
		});
		template += `<a class="carousel-control-prev c-carousel__prevnext-btn c-carousel__prev-btn" href="#carousel-${hash}" role="button" data-slide="prev" data-js="click">
		    <span class="fas fa-caret-left fa-2x c-carousel__prev-icon mr-1" aria-hidden="true"></span>
		  </a>
		  <a class="carousel-control-next c-carousel__prevnext-btn c-carousel__next-btn" href="#carousel-${hash}" role="button" data-slide="next" data-js="click">
		    <span class="fas fa-caret-right fa-2x c-carousel__next-icon ml-1" aria-hidden="true"></span>
		  </a>
		</div>`;
		return template;
	}
}
