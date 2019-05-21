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
	registerEvents: function () {
		this._super();
		this.registerSetServiceContracts();
		this.registerHierarchyRecordCount();
		this.registerShowHierarchy();
	}
});
