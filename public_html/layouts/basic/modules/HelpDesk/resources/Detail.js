/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Detail_Js(
	'HelpDesk_Detail_Js',
	{
		setAccountsReference: function () {
			app.showRecordsList(
				{
					module: 'Accounts',
					src_module: 'HelpDesk',
					src_record: app.getRecordId()
				},
				(_modal, instance) => {
					instance.setSelectEvent((responseData) => {
						Vtiger_Detail_Js.getInstance()
							.saveFieldValues({
								field: 'parent_id',
								value: responseData.id
							})
							.done(function () {
								location.reload();
							});
					});
				}
			);
		}
	},
	{
		registerSetServiceContracts: function () {
			var thisInstance = this;
			$('.selectServiceContracts').on('click', 'ul li', function (e) {
				var element = jQuery(e.currentTarget);
				thisInstance
					.saveFieldValues({
						setRelatedFields: true,
						field: 'servicecontractsid',
						value: element.data('id')
					})
					.done(function (response) {
						location.reload();
					});
			});
		},
		/**
		 * Function to get response from hierarchy
		 * @param {array} params
		 * @returns {Promise}
		 */
		getHierarchyResponseData: function (params) {
			let thisInstance = this,
				aDeferred = $.Deferred();
			if (!$.isEmptyObject(thisInstance.hierarchyResponseCache)) {
				aDeferred.resolve(thisInstance.hierarchyResponseCache);
			} else {
				AppConnector.request(params).then(function (data) {
					thisInstance.hierarchyResponseCache = data;
					aDeferred.resolve(thisInstance.hierarchyResponseCache);
				});
			}
			return aDeferred.promise();
		},
		/**
		 * Function to display the hierarchy response data
		 * @param {array} data
		 */
		displayHierarchyResponseData: function (data) {
			const thisInstance = this;
			let callbackFunction = function () {
				app.showScrollBar($('#hierarchyScroll'), {
					height: '300px',
					railVisible: true,
					size: '6px'
				});
			};
			app.showModalWindow(data, function (modal) {
				App.Components.Scrollbar.xy($('#hierarchyScroll'));
				thisInstance.registerChangeStatusInHierarchy(modal);
				if (typeof callbackFunction == 'function' && $('#hierarchyScroll').height() > 300) {
					callbackFunction();
				}
			});
		},
		/**
		 * Registers read count of hierarchy if it is possible
		 */
		registerHierarchyRecordCount: function () {
			let hierarchyButton = $('.js-detail-hierarchy'),
				params = {
					module: app.getModuleName(),
					action: 'RelationAjax',
					record: app.getRecordId(),
					mode: 'getHierarchyCount'
				};
			if (hierarchyButton.length) {
				AppConnector.request(params).then(function (response) {
					if (response.success) {
						$('.hierarchy .badge').html(response.result);
					}
				});
			}
		},
		/**
		 * Shows hierarchy
		 */
		registerShowHierarchy: function () {
			let thisInstance = this,
				hierarchyButton = $('.detailViewTitle'),
				params = {
					module: app.getModuleName(),
					view: 'Hierarchy',
					record: app.getRecordId()
				};
			hierarchyButton.on('click', '.js-detail-hierarchy', function () {
				let progressIndicatorElement = $.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				thisInstance.getHierarchyResponseData(params).then(function (data) {
					thisInstance.displayHierarchyResponseData(data);
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
				});
			});
		},

		/**
		 * Function to register events on update hierarchy button
		 *
		 * @param {jQuery} container
		 */
		registerChangeStatusInHierarchy: function (container) {
			container.find('.js-update-hierarchy').on('click', function () {
				let params = {
					module: app.getModuleName(),
					action: 'ChangeStatus',
					recordsType: container.find('.js-selected-records').val(),
					status: container.find('.js-status').val(),
					record: app.getRecordId()
				};
				AppConnector.request(params).done(function (data) {
					if (data.success) {
						app.showNotify({ text: data.result.data, type: 'success' });
					}
					app.hideModalWindow();
				});
			});
		},
		/**
		 * Show confirmation on event click
		 * @param {jQuery} element
		 * @param {string} picklistName
		 */
		showProgressConfirmation: function (element, picklistName) {
			let picklistValue = $(element).data('picklistValue');
			app.showConfirmModal({
				title: $(element).data('picklistLabel'),
				text: app.vtranslate('JS_CHANGE_VALUE_CONFIRMATION'),
				confirmedCallback: () => {
					const progressIndicatorElement = $.progressIndicator();
					this.saveFieldValues({
						value: picklistValue,
						field: picklistName
					})
						.done((data) => {
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
							if (data.success) {
								window.location.reload();
							}
						})
						.fail(function (error, err) {
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
							app.errorLog(error, err);
						});
				}
			});
		},
		/**
		 * Function save field values
		 * @param {array} fieldDetailList
		 * @returns {Promise}
		 */
		saveFieldValues: function (fieldDetailList) {
			const self = this;
			var aDeferred = jQuery.Deferred();
			const recordId = app.getRecordId();
			var data = {};
			if (typeof fieldDetailList !== 'undefined') {
				data = fieldDetailList;
			}
			const saveData = (reload = true) => {
				data['record'] = recordId;
				data['module'] = app.getModuleName();
				data['action'] = 'SaveAjax';
				var params = {};
				params.data = data;
				params.async = false;
				params.dataType = 'json';
				AppConnector.request(params).done(function (reponseData) {
					aDeferred.resolve(reponseData);
					if (reload) {
						window.location.reload();
					}
				});
			};
			if (
				fieldDetailList.field === 'ticketstatus' &&
				(CONFIG.checkIfRecordHasTimeControl || CONFIG.checkIfRelatedTicketsAreClosed)
			) {
				AppConnector.request({
					action: 'CheckValidateToClose',
					module: app.getModuleName(),
					record: recordId,
					status: fieldDetailList.value
				}).done((response) => {
					if (response.result.hasTimeControl.result && response.result.relatedTicketsClosed.result) {
						saveData(false);
					} else {
						let addTimeControlCb = saveData;
						if (!response.result.relatedTicketsClosed.result) {
							app.showNotify({
								text: response.result.relatedTicketsClosed.message,
								type: 'info'
							});
							addTimeControlCb = () => {
								this.saveFieldValues(fieldDetailList);
							};
						}
						if (!response.result.hasTimeControl.result) {
							app.showNotify({
								text: response.result.hasTimeControl.message,
								type: 'info'
							});
							self.addTimeControl(
								{
									recordId: recordId,
									url: `index.php?module=OSSTimeControl&view=Edit&sourceModule=HelpDesk&sourceRecord=${recordId}&relationOperation=true&subprocess=${recordId}&subprocess=${recordId}`
								},
								addTimeControlCb
							);
						}
					}
					aDeferred.resolve({ success: false });
				});
				return aDeferred.promise();
			} else {
				saveData(false);
				return aDeferred.promise();
			}
		},
		/**
		 * Add time control when closed ticket
		 * @param {array} params
		 * @returns {Promise}
		 */
		addTimeControl(params, callback = () => {}) {
			let aDeferred = jQuery.Deferred();
			let referenceModuleName = 'OSSTimeControl';
			let parentId = params.recordId;
			let parentModule = 'HelpDesk';
			let quickCreateParams = {};
			let relatedParams = {};
			let relatedField = 'subprocess';
			let fullFormUrl = params.url;
			relatedParams[relatedField] = parentId;
			let eliminatedKeys = new Array('view', 'module', 'mode', 'action');

			let preQuickCreateSave = function (data) {
				let index, queryParam, queryParamComponents;
				let queryParameters = [];

				if (typeof fullFormUrl !== 'undefined' && fullFormUrl.indexOf('?') !== -1) {
					let urlSplit = fullFormUrl.split('?');
					let queryString = urlSplit[1];
					queryParameters = queryString.split('&');
					for (index = 0; index < queryParameters.length; index++) {
						queryParam = queryParameters[index];
						queryParamComponents = queryParam.split('=');
						if (queryParamComponents[0] == 'mode' && queryParamComponents[1] == 'Calendar') {
							data.find('a[data-tab-name="Task"]').trigger('click');
						}
					}
				}
				jQuery('<input type="hidden" name="sourceModule" value="' + parentModule + '" />').appendTo(data);
				jQuery('<input type="hidden" name="sourceRecord" value="' + parentId + '" />').appendTo(data);
				jQuery('<input type="hidden" name="relationOperation" value="true" />').appendTo(data);

				if (typeof relatedField !== 'undefined') {
					let field = data.find('[name="' + relatedField + '"]');
					if (field.length == 0) {
						jQuery('<input type="hidden" name="' + relatedField + '" value="' + parentId + '" />').appendTo(data);
					}
				}
				for (index = 0; index < queryParameters.length; index++) {
					queryParam = queryParameters[index];
					queryParamComponents = queryParam.split('=');
					if (
						jQuery.inArray(queryParamComponents[0], eliminatedKeys) == '-1' &&
						data.find('[name="' + queryParamComponents[0] + '"]').length == 0
					) {
						jQuery(
							'<input type="hidden" name="' + queryParamComponents[0] + '" value="' + queryParamComponents[1] + '" />'
						).appendTo(data);
					}
				}
			};
			if (typeof fullFormUrl !== 'undefined' && fullFormUrl.indexOf('?') !== -1) {
				let urlSplit = fullFormUrl.split('?');
				let queryString = urlSplit[1];
				let queryParameters = queryString.split('&');
				for (let index = 0; index < queryParameters.length; index++) {
					let queryParam = queryParameters[index];
					let queryParamComponents = queryParam.split('=');
					if (jQuery.inArray(queryParamComponents[0], eliminatedKeys) == '-1') {
						relatedParams[queryParamComponents[0]] = queryParamComponents[1];
					}
				}
			}

			quickCreateParams['data'] = relatedParams;
			quickCreateParams['callbackFunction'] = callback;
			quickCreateParams['callbackPostShown'] = preQuickCreateSave;
			quickCreateParams['noCache'] = true;
			App.Components.QuickCreate.createRecord(referenceModuleName, quickCreateParams);
			return aDeferred.promise();
		},
		registerEvents: function () {
			this._super();
			this.registerSetServiceContracts();
			this.registerHierarchyRecordCount();
			this.registerShowHierarchy();
		}
	}
);
