/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("Accounts_AccountsListTree_Js", {}, {
	modalContainer: false,
	moduleList: false,
	treeInstance: false,
	treeData: false,
	getContainer: function () {
		if (this.modalContainer == false) {
			this.modalContainer = jQuery('#centerPanel');
		}
		return this.modalContainer;
	},
	setContainer: function (container) {
		this.modalContainer = container;
	},
	getRecords: function (container) {
		if (this.treeData == false && container != 'undefined') {
			var treeValues = container.find('#treePopupValues').val();
			this.treeData = JSON.parse(treeValues);
		}
		return this.treeData;
	},
	generateTree: function (container) {
		var thisInstance = this;
		thisInstance.treeInstance = container.find("#treeContents");
		thisInstance.treeInstance.jstree({
			core: {
				data: thisInstance.getRecords(container),
				themes: {
					name: 'proton',
					responsive: true
				}
			},
			plugins: [
				"checkbox",
			]
		});
	},
	/**
	 * Function to register the change module
	 */
	registerModuleChangeEvent: function (contentsDiv) {
		var thisInstance = this;
		if (thisInstance.moduleList) {
			return;
		}

		contentsDiv.on('change', '#moduleList', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var selectedModule = currentTarget.val();
			var params = {mode: 'showTree', selectedModule: selectedModule};
			thisInstance.getView(params).then(
					function (data) {
						contentsDiv.html(data);
						thisInstance.setContainer(contentsDiv);
						thisInstance.registerEvents();
						app.changeSelectElementView(contentsDiv.find('select'));
					}
			);
		});
		thisInstance.moduleList = true;
	},
	registerFilterChangeEvent: function (contentsDiv) {
		var thisInstance = this;
		contentsDiv.on('change', '#moduleFilter', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var selectedFilter = currentTarget.val();
			var selected = [];
			$.each(thisInstance.treeInstance.jstree("get_selected", true), function (index, value) {
				selected.push(value.text);
			});
			var params = {
				mode: 'showAccountsList',
				selectedModule: contentsDiv.find('#moduleList').val(),
				selectedFilter: selectedFilter,
				selected: selected,
			};
			thisInstance.getView(params).then(
					function (data) {
						contentsDiv.find('#accountsListContents').html(data);
					}
			);
		});
	},
	/**
	 * Function to get the respective module layout editor through pjax
	 */
	getView: function (data) {
		var thisInstance = this;
		var container = thisInstance.getContainer();
		var aDeferred = jQuery.Deferred();
		var progressIndicatorElement = jQuery.progressIndicator({
			position: 'html',
			blockInfo: {
				enabled: true
			}
		});
		var params = {
			module: app.getModuleName(),
			view: app.getViewName(),
		};
		if (data != null) {
			params = jQuery.extend(data, params);
		}
		AppConnector.request(params).then(
				function (data) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					aDeferred.resolve(data);
				},
				function (error) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					aDeferred.reject();
				}
		);
		return aDeferred.promise();
	},
	registerSelectElement: function (container) {
		var thisInstance = this;
		var selectedModule = container.find('#moduleList').val();
		thisInstance.treeInstance.on("changed.jstree", function (e, data) {
			var selected = [];
			$.each(thisInstance.treeInstance.jstree("get_selected", true), function (index, value) {
				selected.push(value.text);
			});
			var params = {
				mode: 'showAccountsList',
				selectedModule: container.find('#moduleList').val(),
				selectedFilter: container.find('#moduleFilter').val(),
				selected: selected,
			};
			thisInstance.getView(params).then(
					function (data) {
						container.find('#accountsListContents').html(data);
					}
			);
		});
	},
	registerShowHideRightPanelEvent: function (container) {
		container.find('.toggleSiteBarRightButton').click(function (e) {
			var siteBarRight = $(this).closest('.siteBarRight');
			var content = $(this).closest('.row').find('.rowContent');
			var buttonImage = $(this).find('.glyphicon');
			if (siteBarRight.hasClass('col-md-4')) {
				siteBarRight.removeClass('col-md-4').addClass('hideSiteBar');
				content.removeClass('col-md-8').addClass('col-md-12');
				buttonImage.removeClass('glyphicon-chevron-right').addClass("glyphicon-chevron-left");
			} else {
				siteBarRight.addClass('col-md-4').removeClass('hideSiteBar');
				content.addClass('col-md-8').removeClass('col-md-12');
				buttonImage.removeClass('glyphicon-chevron-left').addClass("glyphicon-chevron-right");
			}
		});
	},
	registerEvents: function () {
		var container = this.getContainer();
		this.getRecords(container);
		this.generateTree(container);
		this.registerModuleChangeEvent(container);
		this.registerFilterChangeEvent(container);
		this.registerSelectElement(container);
		this.registerShowHideRightPanelEvent(container);

		//To remove
		setTimeout(function () {
			var bodyHeight = jQuery('.bodyContents').outerHeight();
			container.find('.siteBarRight').css('min-height', bodyHeight + 'px');
		}, 500);
	}
});
