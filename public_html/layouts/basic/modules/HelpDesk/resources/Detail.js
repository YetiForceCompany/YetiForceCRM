/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Detail_Js("HelpDesk_Detail_Js", {
	setAccountsReference: function () {
		app.showRecordsList({
			module: "Accounts",
			src_module: "HelpDesk",
			src_record: app.getRecordId()
		}, (modal, instance) => {
			instance.setSelectEvent((responseData) => {
				Vtiger_Detail_Js.getInstance().saveFieldValues({
					field: "parent_id",
					value: responseData.id
				}).done(function (response) {
					location.reload();
				});
			});
		});
	},
}, {
	registerSetServiceContracts: function () {
		var thisInstance = this;
		$('.selectServiceContracts').on('click', 'ul li', function (e) {
			var element = jQuery(e.currentTarget);
			thisInstance.saveFieldValues({
				setRelatedFields: true,
				field: "servicecontractsid",
				value: element.data('id')
			}).done(function (response) {
				location.reload();
			});
		});
	},
	/**
	 * Function to get response from hierarchy
	 * @param {array} params
	 * @returns {jQuery}
	 */
	getHierarchyResponseData: function (params) {
		let thisInstance = this,
			aDeferred = $.Deferred();
		if (!($.isEmptyObject(thisInstance.hierarchyResponseCache))) {
			aDeferred.resolve(thisInstance.hierarchyResponseCache);
		} else {
			AppConnector.request(params).then(
				function (data) {
					thisInstance.hierarchyResponseCache = data;
					aDeferred.resolve(thisInstance.hierarchyResponseCache);
				}
			);
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
			thisInstance.registerChangeStatusInHierarchy(modal);
			if (typeof callbackFunction == 'function' && $("#hierarchyScroll").height() > 300) {
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
				'position': 'html',
				'blockInfo': {
					'enabled': true
				}
			});
			thisInstance.getHierarchyResponseData(params).then(function (data) {
				thisInstance.displayHierarchyResponseData(data);
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
			});
		});
	},

	/**
	 * Function to register events on update hierarchy button
	 *
	 * @param {jQuery} container
	 */
	registerChangeStatusInHierarchy: function(container) {
		container.find('.js-update-hierarchy').on('click',function(){
			let params = {
				module: app.getModuleName(),
				action: 'ChangeStatus',
				recordsType: container.find('.js-selected-records').val(),
				status: container.find('.js-status').val(),
				record: app.getRecordId()
			};
			AppConnector.request(params).done(function (data) {
				if (data.success) {
					Vtiger_Helper_Js.showPnotify({text: data.result.data, type: 'success'});
				}
				app.hideModalWindow();
			});
		});

	},
	showProgressConfirmation: function(element, picklistName) {
		let lockSave = true;
		const self = this;
		if(CONFIG.checkIfRecordHasTimeControl || CONFIG.checkIfRelatedTicketsAreClosed){
			const recordId = app.getRecordId();
			if(lockSave){
				AppConnector.request({
					action: 'CheckValidateToClose',
					module: app.getModuleName(),
					record:recordId
				}).done(response => {

					if(response.result.hasTimeControl && response.result.relatedTicketsClosed){
					//	alert('ddd')

						this._super(element, picklistName);
					}
					if(!response.result.hasTimeControl){
						Vtiger_Helper_Js.showPnotify({
							text: 'Przed zamknięciem zgłoszenia należy uzupełnić czas pracy',
							type: 'info'
						});
						self.addTimeControl(
							{
								recordId: recordId,
								url: `index.php?module=OSSTimeControl&view=Edit&sourceModule=HelpDesk&sourceRecord=${recordId}&relationOperation=true&subprocess=${recordId}&subprocess=${recordId}`
							}
						);
					}
					if(!response.result.relatedTicketsClosed){
						Vtiger_Helper_Js.showPnotify({
							text: 'Nie zamkniete zgłoszenie',
							type: 'info'
						});
					}
				});
			}
		}
	},
	addTimeControl: function(params) {
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

		let preQuickCreateSave = function(data) {
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
		quickCreateParams['callbackFunction'] = function() {};
		quickCreateParams['callbackPostShown'] = preQuickCreateSave;
		quickCreateParams['noCache'] = true;
		Vtiger_Header_Js.getInstance().quickCreateModule(referenceModuleName, quickCreateParams);
		return aDeferred.promise();
	},
	registerEvents: function () {
		this._super();
		this.registerSetServiceContracts();
		this.registerHierarchyRecordCount();
		this.registerShowHierarchy();
		this.showProgressConfirmation();
	}
});
