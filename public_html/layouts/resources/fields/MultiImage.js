/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
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
			zoomTitleAnimation: {
				in: 'fadeIn',
				out: 'fadeOut'
			},
			showCarousel: true,
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
				done: this.uploadSuccess.bind(this),
			});
			this.elements.component.on('dragleave', this.dragLeave.bind(this));
			this.elements.component.on('dragend', this.dragLeave.bind(this));
			this.elements.fileInput.fileupload('option', 'dropZone', $(this.elements.component));
			this.enableDragNDrop();
		}
		this.elements.component.on('click', '.js-multi-image__popover-img', function (e) {
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
		if (typeof $.fn.animateCss === "undefined") {
			$.fn.extend({
				animateCss: function (animationName, callback) {
					let animationEnd = (function (el) {
						let animations = {
							animation: 'animationend',
							OAnimation: 'oAnimationEnd',
							MozAnimation: 'mozAnimationEnd',
							WebkitAnimation: 'webkitAnimationEnd',
						};
						for (let t in animations) {
							if (el.style[t] !== undefined) {
								return animations[t];
							}
						}
					})(document.createElement('div'));
					this.addClass('animated ' + animationName).one(animationEnd, function () {
						$(this).removeClass('animated ' + animationName);

						if (typeof callback === 'function') callback();
					});
					return this;
				},
			});
		}
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
			bootbox.alert(app.vtranslate('JS_WAIT_FOR_FILE_UPLOAD'));
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
	submit(e, data) {
		data.formData = {
			hash: data.files[0].hash
		};
		App.Fields.MultiImage.currentFileUploads++;
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
		Vtiger_Helper_Js.showPnotify({text: app.vtranslate("JS_INVALID_FILE_HASH") + ` [${hash}]`});
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
	uploadError(e, data) {
		app.errorLog("File upload error.");
		const {jqXHR, files} = data;
		if (typeof jqXHR.responseJSON === "undefined" || jqXHR.responseJSON === null) {
			App.Fields.MultiImage.currentFileUploads--;
			return Vtiger_Helper_Js.showPnotify(app.vtranslate("JS_FILE_UPLOAD_ERROR"));
		}
		const response = jqXHR.responseJSON;
		// first try to show error for concrete file
		if (typeof response.result !== "undefined" && typeof response.result.attach !== "undefined" && Array.isArray(response.result.attach)) {
			response.result.attach.forEach((fileAttach) => {
				App.Fields.MultiImage.currentFileUploads--;
				this.deleteFile(fileAttach.hash, false);
				if (typeof fileAttach.error === 'string') {
					Vtiger_Helper_Js.showPnotify(fileAttach.error + ` [${fileAttach.name}]`);
				} else {
					Vtiger_Helper_Js.showPnotify(app.vtranslate("JS_FILE_UPLOAD_ERROR") + ` [${fileAttach.name}]`);
				}
			});
			this.updateFormValues();
			return;
		}
		// else show default upload error
		files.forEach((file) => {
			App.Fields.MultiImage.currentFileUploads--;
			this.deleteFile(file.hash, false);
			Vtiger_Helper_Js.showPnotify(app.vtranslate("JS_FILE_UPLOAD_ERROR") + ` [${file.name}]`);
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
		const {result} = data;
		const attach = result.result.attach;
		attach.forEach((fileAttach) => {
			const hash = fileAttach.hash;
			if (!hash) {
				return app.errorLog(new Error(app.vtranslate("JS_INVALID_FILE_HASH") + ` [${hash}]`));
			}
			if (typeof fileAttach.key === "undefined") {
				return this.uploadError(e, data);
			}
			if (typeof fileAttach.info !== "undefined" && fileAttach.info) {
				Vtiger_Helper_Js.showPnotify(fileAttach.info + ` [${fileAttach.name}]`);
			}
			const fileInfo = this.getFileInfo(hash);
			this.addFileInfoProperty(hash, 'key', fileAttach.key);
			this.addFileInfoProperty(hash, 'size', fileAttach.size);
			this.addFileInfoProperty(hash, 'name', fileAttach.name);
			this.removePreviewPopover(hash);
			this.addPreviewPopover(fileInfo.file, fileInfo.previewElement, fileInfo.imageSrc);
			App.Fields.MultiImage.currentFileUploads--;
		});
		this.updateFormValues();
	}

	/**
	 * Update form input values
	 */
	updateFormValues() {
		const formValues = this.files.map(file => {
			return {key: file.key, name: file.name, size: file.size};
		});
		this.elements.values.val(JSON.stringify(formValues));
	}

	/**
	 * Validate file
	 *
	 * @param {Object} file
	 * @returns {boolean}
	 */
	validateFile(file) {
		let valid = false;
		this.options.formats.forEach((format) => {
			if (file.type === 'image/' + format) {
				valid = true;
			}
		});
		if (!valid) {
			Vtiger_Helper_Js.showPnotify(`${app.vtranslate("JS_INVALID_FILE_TYPE")} [${file.name}]\n${app.vtranslate("JS_AVAILABLE_FILE_TYPES")}  [${this.options.formats.join(', ')}]`);
		}
		return valid;
	}

	/**
	 * Show limit error
	 */
	showLimitError() {
		this.elements.fileInput.val('');
		Vtiger_Helper_Js.showPnotify(`${app.vtranslate("JS_FILE_LIMIT")} [${this.options.limit}]`);
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
			return this.validateFile(file);
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
			if (typeof file.hash === "undefined") {
				if (this.files.length < this.options.limit) {
					file.hash = App.Fields.Text.generateRandomHash(CONFIG.userId);
					this.files.push({hash: file.hash, imageSrc: file.imageSrc, name: file.name, file});
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
	add(e, data) {
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
	progressAll(e, data) {
		const progress = parseInt(data.loaded / data.total * 100, 10);
		this.elements.progressBar.css({width: progress + "%"});
		if (progress === 100) {
			setTimeout(() => {
				this.elements.progress.addClass('d-none');
				this.elements.progressBar.css({width: "0%"});
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
	dragOver(e) {
		this.elements.component.addClass('c-multi-image__drop-effect');
	}

	/**
	 * Dragleave event handler
	 * @param {Event} e
	 */
	dragLeave(e) {
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
		const imageUrl = `data:application/octet-stream;filename=${fileInfo.name};base64,` + fileInfo.imageSrc.split(',')[1];
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
		const thisInstance = this;
		let fileInfo = this.getFileInfo(hash);
		const titleTemplate = () => `<i class="fa fa-image"></i> ${fileInfo.name}`;
		const bootboxOptions = {
			size: 'large',
			backdrop: true,
			onEscape: true,
			title: `<span id="bootbox-title-${hash}" class="animated ${this.options.zoomTitleAnimation.in}">${titleTemplate()}</span>`,
			message: `<img src="${fileInfo.imageSrc}" class="w-100" />`,
			buttons: {}
		};
		if (this.options.showCarousel) {
			bootboxOptions.message = this.generateCarousel(hash);
		}
		if (!this.detailView) {
			bootboxOptions.buttons.Delete = {
				label: `<i class="fa fa-trash-alt"></i> ${app.vtranslate('JS_DELETE')}`,
				className: "float-left btn btn-danger",
				callback() {
					thisInstance.deleteFile(fileInfo.hash);
				}
			};
		}
		bootboxOptions.buttons.Download = {
			label: `<i class="fa fa-download"></i> ${app.vtranslate('JS_DOWNLOAD')}`,
			className: "float-left btn btn-success",
			callback() {
				thisInstance.download(fileInfo.hash);
			}
		};
		bootboxOptions.buttons.Close = {
			label: `<i class="fa fa-times"></i> ${app.vtranslate('JS_CLOSE')}`,
			className: "btn btn-warning",
			callback: () => {
			},
		};
		bootbox.dialog(bootboxOptions);
		if (this.options.showCarousel) {
			$(`#bootbox-title-${hash}`).css({
				'animation-duration': '350ms'
			})
			$(`#carousel-${hash}`).on('slide.bs.carousel', (e) => {
				fileInfo = this.getFileInfo($(e.relatedTarget).data('hash'));
				const aniIn = this.options.zoomTitleAnimation.in;
				const aniOut = this.options.zoomTitleAnimation.out;
				$(`#bootbox-title-${hash}`).animateCss(aniOut, () => {
					$(`#bootbox-title-${hash}`).html(titleTemplate()).removeClass('animated ' + aniOut).animateCss(aniIn);
				});
			});
		}
	}

	/**
	 * Remove file from preview and from file list
	 *
	 * @param {String} hash
	 */
	deleteFileCallback(hash) {
		const fileInfo = this.getFileInfo(hash);
		fileInfo.previewElement.popover('dispose').remove();
		this.files = this.files.filter(file => file.hash !== fileInfo.hash);
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
			bootbox.confirm({
				title: `<i class="fa fa-trash-alt"></i> ${app.vtranslate("JS_DELETE_FILE")}`,
				message: `${app.vtranslate("JS_DELETE_FILE_CONFIRMATION")} <span class="font-weight-bold">${fileInfo.name}</span>?`,
				callback: (result) => {
					if (result) {
						this.deleteFileCallback(hash);
					}
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
		if (data.files.length) {
			this.generatePreviewElements(data.files, (element) => {
				this.redraw();
			});
		}
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
		if (typeof fileInfo.size !== "undefined") {
			fileSize = `<div class="p-1 bg-white border rounded small position-absolute">${fileInfo.size}</div>`;
		}
		let deleteBtn = '';
		if (!this.detailView) {
			deleteBtn = `<button class="btn btn-sm btn-danger c-btn-collapsible js-multi-image__popover-btn-delete" type="button" data-hash="${file.hash}" data-js="click"><i class="fa fa-trash-alt"></i> <span class="c-btn-collapsible__text">${app.vtranslate('JS_DELETE')}</span></button>`;
		}
		return $(template).popover({
			container: thisInstance.elements.component,
			title: `<div class="u-text-ellipsis"><i class="fa fa-image"></i> ${file.name}</div>`,
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
					<button class="btn btn-sm btn-success c-btn-collapsible js-multi-image__popover-btn-download" type="button" data-hash="${file.hash}" data-js="click"><i class="fa fa-download"></i> <span class="c-btn-collapsible__text">${app.vtranslate('JS_DOWNLOAD')}</span></button>
					<button class="btn btn-sm btn-primary c-btn-collapsible js-multi-image__popover-btn-zoom" type="button" data-hash="${file.hash}" data-js="click"><i class="fa fa-search-plus"></i> <span class="c-btn-collapsible__text">${app.vtranslate('JS_ZOOM_IN')}</span></button>
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
		if (typeof fileInfo.previewElement !== "undefined") {
			fileInfo.previewElement.popover('dispose');
		}
	}

	/**
	 * Hide popovers when user starts moving file preview
	 *
	 * @param {Event} e
	 * @param {Object} ui
	 */
	sortOver(e, ui) {
		this.elements.result.find('.js-multi-image__preview').popover('hide');
	}

	/**
	 * Update file position according to elements order
	 *
	 * @param {Event} e
	 * @param {Object} ui
	 */
	sortStop(e, ui) {
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
		this.elements.result.sortable({
			handle: '.js-multi-image__preview-img',
			items: '.js-multi-image__preview',
			over: this.sortOver.bind(this),
			stop: this.sortStop.bind(this),
		}).disableSelection().on('mousedown', '.js-multi-image__preview-img', function (e) {
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
		files.forEach((file) => {
			if (file instanceof File) {
				this.generatePreviewFromFile(file, (template, imageSrc) => {
					file.preview = this.addPreviewPopover(file, template, imageSrc);
					this.addFileInfoProperty(file.hash, 'previewElement', file.preview);
					callback(file.preview);
				});
			} else {
				this.generatePreviewFromValue(file, (template, imageSrc) => {
					file.preview = this.addPreviewPopover(file, template, imageSrc);
					this.addFileInfoProperty(file.hash, 'previewElement', file.preview);
					callback(file.preview);
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
			callback(`<div class="d-inline-block mr-1 js-multi-image__preview" id="js-multi-image__preview-hash-${file.hash}" data-hash="${file.hash}" data-js="container|click">
					<div class="img-thumbnail js-multi-image__preview-img c-multi-image__preview-img" data-hash="${file.hash}" data-js="drag" style="background-image:url(${fr.result})" tabindex="0" title="${file.name}"></div>
			</div>`, fr.result);
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
		callback(`<div class="d-inline-block mr-1 js-multi-image__preview" id="js-multi-image__preview-hash-${file.hash}" data-hash="${file.hash}" data-js="container|click">
				<div class="img-thumbnail js-multi-image__preview-img c-multi-image__preview-img" data-hash="${file.hash}" data-js="drag" style="background-image:url(${file.imageSrc})" tabindex="0" title="${file.name}"></div>
		</div>`, file.imageSrc);
	}

	/**
	 * Load files that were in valueInput as json string
	 */
	loadExistingFiles() {
		this.files = this.files.map((file) => {
			file.hash = App.Fields.Text.generateRandomHash(CONFIG.userId);
			return file;
		}).slice(0, this.options.limit);
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
