/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
"use strict";

Settings_Vtiger_Index_Js(
	"Settings_Colors_Index_Js",
	{},
	{
		/**
		 * Var to store colors container
		 */
		container: false,
		/**
		 * Function to get the colors container
		 */
		getContainer: function() {
			if (this.container == false) {
				this.container = jQuery(".js-colors-container");
			}
			return this.container;
		},
		registerEvents: function() {
			this.registerModuleTabEvent();
			this.registerModuleChangeEvent();
			this.registerModulePickListChangeEvent();
			this.initEvants();
		},
		initEvants: function() {
			var thisInstance = this;
			var container = $(".UserColors");
			container.find(".updateUserColor").on("click", function(e) {
				thisInstance.updateUserColor(e, thisInstance);
			});
			container.find(".generateUserColor").on("click", this.generateUserColor);
			container.find(".removeUserColor").on("click", this.removeUserColor);
			container.find(".updateGroupColor").on("click", function(e) {
				thisInstance.updateGroupColor(e, thisInstance);
			});
			container.find(".generateGroupColor").on("click", this.generateGroupColor);
			container.find(".removeGroupColor").on("click", this.removeGroupColor);
			container.find(".updateModuleColor").on("click", function(e) {
				thisInstance.updateModuleColor(e, thisInstance);
			});
			container.find(".generateModuleColor").on("click", this.generateModuleColor);
			container.find(".removeModuleColor").on("click", this.removeModuleColor);
			container.find(".activeModuleColor").on("click", this.activeModuleColor);
			container.find(".addPicklistColorColumn").on("click", this.addPicklistColorColumn);
			container.find(".updatePicklistValueColor").on("click", function(e) {
				thisInstance.updatePicklistValueColor(e, thisInstance);
			});
			container.find(".generatePicklistValueColor").on("click", this.generatePicklistValueColor);
			container.find(".removePicklistValueColor").on("click", this.removePicklistValueColor);
			container.find(".updateColor").on("click", function(e) {
				thisInstance.updateCalendarColor(e, thisInstance);
			});
			container.find(".js-remove-color").on("click", e => {
				thisInstance.removeFieldColor(e, thisInstance);
			});
			container.find(".js-generate-color").on("click", e => {
				thisInstance.generateColor(e, thisInstance);
			});
			container.find(".js-update-color").on("click", function(e) {
				thisInstance.updateFieldColor(e, container, thisInstance);
			});
			container.find("#update_event").on("click", this.updateEvent);
			container.find(".generateColor").on("click", this.generateCalendarColor);
			container.find(".removeCalendarColor").on("click", this.removeCalendarColor);
		},
		registerColorPicker: function(data, colorObject) {
			data.find(".js-color-picker").colorpicker({
				format: "hex",
				inline: true,
				container: true,
				color: colorObject.data("color")
			});
		},
		updateUserColor: function(e, thisInstance) {
			var target = $(e.currentTarget);
			var editColorModal = jQuery(".UserColors .editColorContainer");
			var clonedContainer = editColorModal.clone(true, true);
			var colorPreview = $("#calendarColorPreviewUser" + target.data("record"));
			var callBackFunction = function(data) {
				data
					.find(".editColorContainer")
					.removeClass("d-none")
					.show();
				var selectedColor = data.find(".selectedColor");
				selectedColor.val(colorPreview.data("color"));
				//register color picker
				thisInstance.registerColorPicker(data, colorPreview);
				data.find('[name="saveButton"]').on("click", function(e) {
					var progress = $.progressIndicator({
						message: app.vtranslate("JS_LOADING_PLEASE_WAIT"),
						blockInfo: {
							enabled: true
						}
					});
					AppConnector.request({
						module: "Colors",
						parent: "Settings",
						action: "SaveAjax",
						mode: "updateUserColor",
						color: selectedColor.val(),
						record: target.data("record")
					}).done(function(data) {
						Vtiger_Helper_Js.showPnotify({
							text: data["result"]["message"],
							type: "success"
						});
						return data["result"];
					});
					colorPreview.css("background", selectedColor.val());
					target.data("color", selectedColor.val());
					progress.progressIndicator({ mode: "hide" });
					app.hideModalWindow();
				});
			};
			app.showModalWindow(
				clonedContainer,
				function(data) {
					if (typeof callBackFunction == "function") {
						callBackFunction(data);
					}
				},
				{ width: "1000px" }
			);
		},
		generateUserColor: function(e) {
			var target = $(e.currentTarget);
			var colorPreview = $("#calendarColorPreviewUser" + target.data("record"));
			AppConnector.request({
				module: "Colors",
				parent: "Settings",
				action: "SaveAjax",
				mode: "updateUserColor",
				record: target.data("record")
			}).done(function(data) {
				colorPreview.css("background", data["result"].color);
				colorPreview.data("color", data["result"].color);
				Vtiger_Helper_Js.showPnotify({
					text: data["result"]["message"],
					type: "success"
				});
			});
		},
		removeUserColor: function(e) {
			var target = $(e.currentTarget);
			var colorPreview = $("#calendarColorPreviewUser" + target.data("record"));
			AppConnector.request({
				module: "Colors",
				parent: "Settings",
				action: "SaveAjax",
				mode: "removeUserColor",
				record: target.data("record")
			}).done(function(data) {
				colorPreview.css("background", "");
				colorPreview.data("color", "");
				Vtiger_Helper_Js.showPnotify({
					text: data["result"]["message"],
					type: "success"
				});
			});
		},
		updateGroupColor: function(e, thisInstance) {
			var target = $(e.currentTarget);
			var editColorModal = jQuery(".UserColors .editColorContainer");
			var clonedContainer = editColorModal.clone(true, true);
			var colorPreview = $("#calendarColorPreviewGroup" + target.data("record"));
			var callBackFunction = function(data) {
				data
					.find(".editColorContainer")
					.removeClass("d-none")
					.show();
				var selectedColor = data.find(".selectedColor");
				selectedColor.val(colorPreview.data("color"));
				//register color picker
				thisInstance.registerColorPicker(data, colorPreview);
				//save the user calendar with color
				data.find('[name="saveButton"]').on("click", function(e) {
					var progress = $.progressIndicator({
						message: app.vtranslate("JS_LOADING_PLEASE_WAIT"),
						blockInfo: {
							enabled: true
						}
					});
					AppConnector.request({
						module: "Colors",
						parent: "Settings",
						action: "SaveAjax",
						mode: "updateGroupColor",
						color: selectedColor.val(),
						record: target.data("record")
					}).done(function(data) {
						Vtiger_Helper_Js.showPnotify({
							text: data["result"]["message"],
							type: "success"
						});
						return data["result"];
					});
					colorPreview.css("background", selectedColor.val());
					target.data("color", selectedColor.val());
					progress.progressIndicator({ mode: "hide" });
					app.hideModalWindow();
				});
			};
			app.showModalWindow(
				clonedContainer,
				function(data) {
					if (typeof callBackFunction == "function") {
						callBackFunction(data);
					}
				},
				{ width: "1000px" }
			);
		},
		generateGroupColor: function(e) {
			var target = $(e.currentTarget);
			var colorPreview = $("#calendarColorPreviewGroup" + target.data("record"));
			AppConnector.request({
				module: "Colors",
				parent: "Settings",
				action: "SaveAjax",
				mode: "updateGroupColor",
				record: target.data("record")
			}).done(function(data) {
				colorPreview.css("background", data["result"].color);
				colorPreview.data("color", data["result"].color);
				Vtiger_Helper_Js.showPnotify({
					text: data["result"]["message"],
					type: "success"
				});
			});
		},
		removeGroupColor: function(e) {
			var target = $(e.currentTarget);
			var colorPreview = $("#calendarColorPreviewGroup" + target.data("record"));
			AppConnector.request({
				module: "Colors",
				parent: "Settings",
				action: "SaveAjax",
				mode: "removeGroupColor",
				record: target.data("record")
			}).done(function(data) {
				colorPreview.css("background", "");
				colorPreview.data("color", "");
				Vtiger_Helper_Js.showPnotify({
					text: data["result"]["message"],
					type: "success"
				});
			});
		},
		updateModuleColor: function(e, thisInstance) {
			var target = $(e.currentTarget);
			var editColorModal = jQuery(".UserColors .editColorContainer");
			var clonedContainer = editColorModal.clone(true, true);
			var colorPreview = $("#calendarColorPreviewModule" + target.data("record"));
			var callBackFunction = function(data) {
				data
					.find(".editColorContainer")
					.removeClass("d-none")
					.show();
				var selectedColor = data.find(".selectedColor");
				selectedColor.val(colorPreview.data("color"));
				//register color picker
				thisInstance.registerColorPicker(data, colorPreview);
				//save the user calendar with color
				data.find('[name="saveButton"]').on("click", function(e) {
					var progress = $.progressIndicator({
						message: app.vtranslate("JS_LOADING_PLEASE_WAIT"),
						blockInfo: {
							enabled: true
						}
					});
					AppConnector.request({
						module: "Colors",
						parent: "Settings",
						action: "SaveAjax",
						mode: "updateModuleColor",
						color: selectedColor.val(),
						record: target.data("record")
					}).done(function(data) {
						Vtiger_Helper_Js.showPnotify({
							text: data["result"]["message"],
							type: "success"
						});
						return data["result"];
					});
					colorPreview.css("background", selectedColor.val());
					target.data("color", selectedColor.val());
					progress.progressIndicator({ mode: "hide" });
					app.hideModalWindow();
				});
			};
			app.showModalWindow(
				clonedContainer,
				function(data) {
					if (typeof callBackFunction == "function") {
						callBackFunction(data);
					}
				},
				{ width: "1000px" }
			);
		},
		generateModuleColor: function(e) {
			var target = $(e.currentTarget);
			var colorPreview = $("#calendarColorPreviewModule" + target.data("record"));
			AppConnector.request({
				module: "Colors",
				parent: "Settings",
				action: "SaveAjax",
				mode: "updateModuleColor",
				record: target.data("record")
			}).done(function(data) {
				colorPreview.css("background", data["result"].color);
				colorPreview.data("color", data["result"].color);
				Vtiger_Helper_Js.showPnotify({
					text: data["result"]["message"],
					type: "success"
				});
			});
		},
		removeModuleColor: function(e) {
			var target = $(e.currentTarget);
			var colorPreview = $("#calendarColorPreviewModule" + target.data("record"));
			AppConnector.request({
				module: "Colors",
				parent: "Settings",
				action: "SaveAjax",
				mode: "removeModuleColor",
				record: target.data("record")
			}).done(function(data) {
				colorPreview.css("background", "");
				colorPreview.data("color", "");
				Vtiger_Helper_Js.showPnotify({
					text: data["result"]["message"],
					type: "success"
				});
			});
		},
		activeModuleColor: function(e) {
			var target = $(e.currentTarget);
			var colorPreview = $("#calendarColorPreviewModule" + target.data("record"));
			AppConnector.request({
				module: "Colors",
				parent: "Settings",
				action: "SaveAjax",
				mode: "activeModuleColor",
				status: target.is(":checked"),
				color: colorPreview.data("color"),
				record: target.data("record")
			}).done(function(data) {
				Vtiger_Helper_Js.showPnotify({
					text: data["result"]["message"],
					type: "success"
				});
				colorPreview.css("background", data["result"].color);
				colorPreview.data("color", data["result"].color);
			});
		},
		addPicklistColorColumn: function(e) {
			var container = jQuery(".picklistViewContentDiv");
			var target = $(e.currentTarget);
			AppConnector.request({
				module: "Colors",
				parent: "Settings",
				action: "SaveAjax",
				mode: "addPicklistColorColumn",
				picklistModule: target.data("fieldmodule"),
				fieldId: target.data("fieldid")
			}).done(function(data) {
				Vtiger_Helper_Js.showPnotify({
					text: data["result"]["message"],
					type: "success"
				});
				container.find(".modulePickList").trigger("change");
			});
		},
		updatePicklistValueColor: function(e, thisInstance) {
			var container = jQuery(".picklistViewContentDiv");
			var target = $(e.currentTarget);
			var editColorModal = jQuery(".UserColors .editColorContainer");
			var clonedContainer = editColorModal.clone(true, true);
			var colorPreview = container.find("#calendarColorPreviewPicklistValue" + target.data("fieldvalueid"));
			var callBackFunction = function(data) {
				data
					.find(".editColorContainer")
					.removeClass("d-none")
					.show();
				var selectedColor = data.find(".selectedColor");
				selectedColor.val(colorPreview.data("color"));
				//register color picker
				thisInstance.registerColorPicker(data, colorPreview);
				//save the user calendar with color
				data.find('[name="saveButton"]').on("click", function(e) {
					var progress = $.progressIndicator({
						message: app.vtranslate("JS_LOADING_PLEASE_WAIT"),
						blockInfo: {
							enabled: true
						}
					});

					AppConnector.request({
						module: "Colors",
						parent: "Settings",
						action: "SaveAjax",
						mode: "updatePicklistValueColor",
						color: selectedColor.val(),
						fieldId: target.data("fieldid"),
						fieldValueId: target.data("fieldvalueid")
					}).done(function(data) {
						Vtiger_Helper_Js.showPnotify({
							text: data["result"]["message"],
							type: "success"
						});
						return data["result"];
					});
					colorPreview.css("background", selectedColor.val());
					target.data("color", selectedColor.val());
					progress.progressIndicator({ mode: "hide" });
					app.hideModalWindow();
				});
			};
			app.showModalWindow(
				clonedContainer,
				function(data) {
					if (typeof callBackFunction == "function") {
						callBackFunction(data);
					}
				},
				{ width: "1000px" }
			);
		},
		generatePicklistValueColor: function(e) {
			var container = jQuery(".picklistViewContentDiv");
			var target = $(e.currentTarget);
			var colorPreview = container.find("#calendarColorPreviewPicklistValue" + target.data("fieldvalueid"));
			var progress = $.progressIndicator({
				message: app.vtranslate("JS_LOADING_PLEASE_WAIT"),
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request({
				module: "Colors",
				parent: "Settings",
				action: "SaveAjax",
				mode: "updatePicklistValueColor",
				fieldId: target.data("fieldid"),
				fieldValueId: target.data("fieldvalueid")
			}).done(function(data) {
				colorPreview.css("background", data["result"].color);
				colorPreview.data("color", data["result"].color);
				Vtiger_Helper_Js.showPnotify({
					text: data["result"]["message"],
					type: "success"
				});
			});
			progress.progressIndicator({ mode: "hide" });
			app.hideModalWindow();
		},
		removePicklistValueColor: function(e) {
			var container = jQuery(".picklistViewContentDiv");
			var target = $(e.currentTarget);
			var colorPreview = container.find("#calendarColorPreviewPicklistValue" + target.data("fieldvalueid"));
			var progress = $.progressIndicator({
				message: app.vtranslate("JS_LOADING_PLEASE_WAIT"),
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request({
				module: "Colors",
				parent: "Settings",
				action: "SaveAjax",
				mode: "removePicklistValueColor",
				fieldId: target.data("fieldid"),
				fieldValueId: target.data("fieldvalueid")
			}).done(function(data) {
				colorPreview.css("background", "");
				colorPreview.data("color", "");
				Vtiger_Helper_Js.showPnotify({
					text: data["result"]["message"],
					type: "success"
				});
			});
			progress.progressIndicator({ mode: "hide" });
			app.hideModalWindow();
		},
		generateCalendarColor: function(e) {
			var target = $(e.currentTarget);
			var closestTrElement = target.closest("tr");
			AppConnector.request({
				module: "Colors",
				parent: "Settings",
				action: "SaveAjax",
				mode: "updateCalendarColor",
				id: closestTrElement.data("id"),
				table: closestTrElement.data("table"),
				field: closestTrElement.data("field")
			}).done(function(data) {
				Settings_Colors_Index_Js.showMessage({ type: "success", text: data.result.message });
				closestTrElement.find(".calendarColor").css("background", data.result.color);
				closestTrElement.data("color", data.result.color);
			});
		},
		updateCalendarColor: function(e, thisInstance) {
			var target = $(e.currentTarget);
			var closestTrElement = target.closest("tr");
			var editColorModal = jQuery(".UserColors .editColorContainer");
			var clonedContainer = editColorModal.clone(true, true);

			var callBackFunction = function(data) {
				data
					.find(".editColorContainer")
					.removeClass("d-none")
					.show();
				var selectedColor = data.find(".selectedColor");
				selectedColor.val(closestTrElement.data("color"));
				//register color picker
				thisInstance.registerColorPicker(data, closestTrElement);
				//save the user calendar with color
				data.find('[name="saveButton"]').on("click", function(e) {
					var progress = $.progressIndicator({
						message: app.vtranslate("Update labels"),
						blockInfo: {
							enabled: true
						}
					});
					AppConnector.request({
						module: "Colors",
						parent: "Settings",
						action: "SaveAjax",
						mode: "updateCalendarColor",
						color: selectedColor.val(),
						id: closestTrElement.data("id"),
						table: closestTrElement.data("table"),
						field: closestTrElement.data("field")
					}).done(function(data) {
						Vtiger_Helper_Js.showPnotify({
							text: data["result"]["message"],
							type: "success"
						});
						return data["result"];
					});
					closestTrElement.find(".calendarColor").css("background", selectedColor.val());
					closestTrElement.data("color", selectedColor.val());
					progress.progressIndicator({ mode: "hide" });
					app.hideModalWindow();
				});
			};
			app.showModalWindow(
				clonedContainer,
				function(data) {
					if (typeof callBackFunction == "function") {
						callBackFunction(data);
					}
				},
				{ width: "1000px" }
			);
		},
		removeCalendarColor: function(e) {
			var container = jQuery("#calendarColors");
			var target = $(e.currentTarget);
			var colorPreview = container.find("#calendarColorPreviewCalendar" + target.data("record"));
			var progress = $.progressIndicator({
				message: app.vtranslate("JS_LOADING_PLEASE_WAIT"),
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request({
				module: "Colors",
				parent: "Settings",
				action: "SaveAjax",
				mode: "removeCalendarColor",
				id: target.data("record")
			}).done(function(data) {
				colorPreview.css("background", "");
				colorPreview.data("color", "");
				Vtiger_Helper_Js.showPnotify({
					text: data["result"]["message"],
					type: "success"
				});
			});
			progress.progressIndicator({ mode: "hide" });
			app.hideModalWindow();
		},
		/**
		 * Function to handle changing tab
		 *
		 */
		registerModuleTabEvent: function() {
			const self = this;
			const container = self.getContainer();
			container.find(".js-change-tab").on("click", e => {
				const clickedTab = $(e.currentTarget);
				const mode = clickedTab.attr("data-mode");
				if (mode) {
					let progressIndicatorElement = jQuery.progressIndicator({
						position: "html",
						blockInfo: {
							enabled: true
						}
					});
					AppConnector.request({
						module: "Colors",
						parent: app.getParentModuleName(),
						view: "IndexAjax",
						mode: mode
					}).done(function(data) {
						const activeTabPanelHref = clickedTab.attr("href");
						const activeTabPanel = container.find(activeTabPanelHref + " .js-color-contents");
						activeTabPanel.html(data);
						progressIndicatorElement.progressIndicator({ mode: "hide" });
						activeTabPanel.find(".modulePickList").trigger("change");
						self.registerModuleChangeEvent();
						App.Fields.Picklist.changeSelectElementView(container);
					});
				}
			});
		},
		/**
		 * Function to handle changing module
		 *
		 */
		registerModuleChangeEvent: function() {
			const self = this;
			const container = self.getContainer();
			container.find(".js-selected-module").on("change", function(e) {
				const selectedModule = jQuery(e.currentTarget).val();
				const activeTab = container.find(".js-change-tab.active");
				if (selectedModule.length <= 0) {
					activeTab.trigger("click");
					return false;
				}
				let progressIndicatorElement = jQuery.progressIndicator({
					position: "html",
					blockInfo: {
						enabled: true
					}
				});
				AppConnector.request({
					module: "Colors",
					parent: app.getParentModuleName(),
					source_module: selectedModule,
					view: "IndexAjax",
					mode: activeTab.attr("data-mode")
				}).done(function(data) {
					container.find(activeTab.attr("href") + " .js-color-contents").html(data);
					progressIndicatorElement.progressIndicator({ mode: "hide" });
					App.Fields.Picklist.changeSelectElementView(container);
					self.registerModuleChangeEvent();
					self.initEvants();
					self.registerModulePickListChangeEvent();
					jQuery("#modulePickList").trigger("change");
				});
			});
		},
		registerModulePickListChangeEvent: function() {
			var thisInstance = this;
			var container = jQuery(".picklistViewContentDiv");
			container.find(".modulePickList").on("change", function(e) {
				var progressIndicatorElement = jQuery.progressIndicator({
					position: "html",
					blockInfo: {
						enabled: true
					}
				});
				AppConnector.request({
					module: "Colors",
					parent: app.getParentModuleName(),
					source_module: jQuery(".js-selected-module").val(),
					view: "IndexAjax",
					mode: "getPickListView",
					fieldId: jQuery(e.currentTarget).val()
				}).done(function(data) {
					container.html(data);
					App.Fields.Picklist.changeSelectElementView(jQuery(".pickListModulesSelectContainer"));
					App.Fields.Picklist.changeSelectElementView(jQuery(".pickListModulesPicklistSelectContainer"));
					thisInstance.registerModuleChangeEvent();
					thisInstance.registerModulePickListChangeEvent();
					$(".UserColors .addPicklistColorColumn").on("click", thisInstance.addPicklistColorColumn);
					$(".UserColors .updatePicklistValueColor").on("click", function(e) {
						thisInstance.updatePicklistValueColor(e, thisInstance);
					});
					$(".UserColors .generatePicklistValueColor").on("click", thisInstance.generatePicklistValueColor);
					$(".UserColors .removePicklistValueColor").on("click", thisInstance.removePicklistValueColor);
					progressIndicatorElement.progressIndicator({ mode: "hide" });
				});
			});
		},
		generateColor: function(e, self) {
			const container = self.getContainer();
			const fieldId = $(e.currentTarget).attr("data-field-id");
			let colorPreview = container.find('.js-color-preview[data-field-id="' + fieldId + '"]');
			let progress = $.progressIndicator({
				message: app.vtranslate("JS_LOADING_PLEASE_WAIT"),
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request({
				module: "Colors",
				parent: "Settings",
				action: "SaveAjax",
				mode: "updateFieldColor",
				fieldId: fieldId
			}).done(function(data) {
				colorPreview.css("background", data["result"].color);
				colorPreview.data("color", data["result"].color);
				Vtiger_Helper_Js.showPnotify({
					text: data["result"]["message"],
					type: "success"
				});
			});
			progress.progressIndicator({ mode: "hide" });
			app.hideModalWindow();
		},
		updateFieldColor: function(e, container, self) {
			let target = $(e.currentTarget);
			const fieldId = $(e.currentTarget).attr("data-field-id");
			let editColorModal = container.find(".editColorContainer");
			let clonedContainer = editColorModal.clone(true, true);
			let colorPreview = container.find('.js-color-preview[data-field-id="' + fieldId + '"]');
			let callBackFunction = function(data) {
				data
					.find(".editColorContainer")
					.removeClass("d-none")
					.show();
				let selectedColor = data.find(".selectedColor");
				selectedColor.val(colorPreview.data("color"));
				self.registerColorPicker(data, colorPreview);
				data.find('[name="saveButton"]').on("click", function(e) {
					let progress = $.progressIndicator({
						message: app.vtranslate("JS_LOADING_PLEASE_WAIT"),
						blockInfo: {
							enabled: true
						}
					});
					AppConnector.request({
						module: "Colors",
						parent: "Settings",
						action: "SaveAjax",
						mode: "updateFieldColor",
						color: selectedColor.val(),
						fieldId: fieldId
					}).done(function(data) {
						Vtiger_Helper_Js.showPnotify({
							text: data["result"]["message"],
							type: "success"
						});
						return data["result"];
					});
					colorPreview.css("background", selectedColor.val());
					target.data("color", selectedColor.val());
					progress.progressIndicator({ mode: "hide" });
					app.hideModalWindow();
				});
			};
			app.showModalWindow(
				clonedContainer,
				function(data) {
					if (typeof callBackFunction == "function") {
						callBackFunction(data);
					}
				},
				{ width: "1000px" }
			);
		},
		removeFieldColor: function(e, self) {
			const container = self.getContainer();
			const fieldId = $(e.currentTarget).attr("data-field-id");
			let colorPreview = container.find('.js-color-preview[data-field-id="' + fieldId + '"]');
			let progress = $.progressIndicator({
				message: app.vtranslate("JS_LOADING_PLEASE_WAIT"),
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request({
				module: "Colors",
				parent: "Settings",
				action: "SaveAjax",
				mode: "removeFieldColor",
				fieldId: fieldId
			}).done(function(data) {
				colorPreview.css("background", "");
				colorPreview.data("color", "");
				Vtiger_Helper_Js.showPnotify({
					text: data["result"]["message"],
					type: "success"
				});
			});
			progress.progressIndicator({ mode: "hide" });
			app.hideModalWindow();
		}
	}
);
