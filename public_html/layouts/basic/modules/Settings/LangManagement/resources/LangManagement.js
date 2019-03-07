/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

var Settings_Index_Js = {
	initEvants: function () {
		let container = $('.LangManagement');
		container.find('.add_lang').on('click', Settings_Index_Js.ShowLangMondal);
		container.find('.edit_lang a').on('click', function (e) {
			jQuery('#edit_lang').html('');
			document.showDiff = false;
			Settings_Index_Js.LoadEditLang(this)
		});
		container.find('.js-add-languages-modal').on('click', () => {
			const progressIndicatorElement = $.progressIndicator({
				'position': 'html',
				'blockInfo': {
					'enabled': true
				}
			});
			app.showModalWindow(null, 'index.php?module=YetiForce&parent=Settings&view=DownloadLanguageModal', () => {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
			});
		});
		$('.AddNewLangMondal .btn-primary').on('click', Settings_Index_Js.AddLangMondal);
		$('.AddNewTranslationMondal .btn-primary').on('click', Settings_Index_Js.AddTranslationMondal);
		$('#lang_list tr').each(function (index, element) {
			element = $(element);
			Settings_Index_Js.initEvant(element);
		});
		this.registerUpdateLanguageBtn(container);
	},
	registerUpdateLanguageBtn(container) {
		container.find('.js-update').on('click', function (e) {
			let icon = $(e.target).find('.js-update__icon'),
				progress = $.progressIndicator({
					'message': app.vtranslate('JS_LOADING_PLEASE_WAIT'),
					'blockInfo': {
						'enabled': true
					}
				});
			icon.addClass('fa-spin');
			AppConnector.request({
				module: 'YetiForce',
				parent: 'Settings',
				action: 'DownloadLanguage',
				prefix: $(e.target).data('prefix')
			}).done(function (data) {
				Vtiger_Helper_Js.showPnotify({
					text: data['result']['message'],
					type: data['result']['type']
				});
				if (data['result']['type'] === 'success') {
					location.reload();
				} else {
					progress.progressIndicator({'mode': 'hide'});
					icon.removeClass('fa-spin');
				}
			});
		});
	},
	LoadEditLang: function (e) {
		var element = jQuery(e);
		var position = element.attr('href');
		if (typeof position === "undefined") {
			position = '#' + element.attr('id');
		}
		var progress = $.progressIndicator({
			'message': app.vtranslate('LBL_Loader'),
			'position': position,
			'blockInfo': {
				'enabled': true
			}
		});
		var param = {
			module: 'LangManagement',
			parent: app.getParentModuleName(),
			view: 'Edit'
		};
		if ($(".LangManagement " + position + " #langs_list").val() != undefined) {
			param.lang = $(".LangManagement " + position + " #langs_list").val();
		}
		if ($(".LangManagement #mods_list").val() != undefined) {
			param.mod = $(".LangManagement " + position + " #mods_list").val();
		}
		if (document.showDiff == true) {
			param.sd = 1;
		}
		AppConnector.request(param).done(function (data) {
			jQuery(position).html(data);
			Settings_Index_Js.initEditLang(position);
			progress.progressIndicator({'mode': 'hide'});
		});
	},
	initEditLang: function (position) {
		App.Fields.Picklist.changeSelectElementView($(".LangManagement .layoutContent .active .select2"), 'select2').on("change", function (e) {
			e = jQuery(this).closest('.active');
			Settings_Index_Js.LoadEditLang(e);
		});
		$('#edit_lang .translation').on('change', function (e) {
			Settings_Index_Js.changeTranslation(e, position)
		});
		$('#edit_lang .js-add-translation').on('click', Settings_Index_Js.ShowTranslationMondal);
		$('#edit_lang .js-delete').on('click', function (e) {
			Settings_Index_Js.deleteTranslation(e, position)
		});
		$('.LangManagement ' + position + ' .show_differences').on('click', Settings_Index_Js.ShowDifferences);
		$.extend($.fn.dataTable.defaults, {
			"searching": true,
			"ordering": false,
			"bFilter": false,
			"bLengthChange": false,
			"bPaginate": false,
			"bInfo": false,
			"pageLength": -1,
			"language": {
				"sZeroRecords": app.vtranslate('No matching records found'),
				"sSearch": app.vtranslate('Search'),
				"sEmptyTable": app.vtranslate('No data available in table'),
			}
		});
		$('' + position + ' .listViewEntriesTable').dataTable();
	},
	ShowDifferences: function (e) {
		if ($(this).is(':checked')) {
			document.showDiff = true;
		} else {
			document.showDiff = false;
		}
		e = $(this).closest('.active');
		Settings_Index_Js.LoadEditLang(e);
	},
	changeTranslation: function (e, position, mod) {

		var target = $(e.currentTarget);
		if (typeof e.currentTarget === "undefined")
			target = jQuery(e);
		var closestTrElement = target.closest('tr');
		var progress = $.progressIndicator({
			'message': app.vtranslate('LBL_Loader'),
			'position': position,
			'blockInfo': {
				'enabled': true
			}
		});
		if (mod == undefined) {
			mod = jQuery(".LangManagement " + position + " #mods_list").data('target') ? jQuery(".LangManagement " + position + " #mods_list").data('target') : jQuery(".LangManagement " + position + " #mods_list").val();
		}
		Settings_Index_Js.registerSaveEvent('saveTranslation', {
			lang: target.data('lang'),
			mod: mod,
			type: target.data('type'),
			variable: closestTrElement.data('langkey'),
			val: target.val(),
			is_new: target.hasClass("empty_value"),
		});
		target.removeClass("empty_value");
		progress.progressIndicator({'mode': 'hide'});
	},
	deleteTranslation: function (e, position) {
		var target = $(e.currentTarget);
		if (typeof e.currentTarget === "undefined") {
			target = e;
		}
		var closestTrElement = target.closest('tr');
		var progress = $.progressIndicator({
			'message': app.vtranslate('LBL_Loader'),
			'position': position,
			'blockInfo': {
				'enabled': true
			}
		});
		Settings_Index_Js.registerSaveEvent('deleteTranslation', {
			lang: $(".LangManagement #langs_list").val(),
			mod: $(".LangManagement " + position + " #mods_list").data('target') ? $(".LangManagement " + position + " #mods_list").data('target') : $(".LangManagement " + position + " #mods_list").val(),
			langkey: closestTrElement.data('langkey'),
			type: closestTrElement.data('type'),
		});
		progress.progressIndicator({'mode': 'hide'});
		e = target.closest('.active');
		Settings_Index_Js.LoadEditLang(e);
	},
	initEvant: function (element) {
		const options = {
			title: app.vtranslate('LBL_AreYouSure'),
			trigger: 'manual',
			placement: 'left',
			html: true,
			sanitize: false,
			content: '<div class="popover_block"><button type="button" role="button" class="btn btn-danger deleteItem marginLeft10">' + app.vtranslate('LBL_YES') + '</button> <button type="button"  role="button" class="btn btn-warning pull-right cancel">' + app.vtranslate('Cancel') + '</button></div>'
		}
		const makeSureOptions = {
			title: app.vtranslate('JS_ARE_YOU_SURE_TO_SET_AS_DEFAULT'),
			trigger: 'manual',
			placement: 'left',
			html: true,
			sanitize: false,
			content: '<div class="popover_block"><button type="button" role="button" class="btn btn-danger setDefaultItem">' + app.vtranslate('LBL_YES') + '</button>   <button type="button" role="button" class="btn btn-warning pull-right cancel">' + app.vtranslate('Cancel') + '</button></div>'
		}
		element.find('#deleteItemC').on('click', function (e) {
			$(e.currentTarget).popover(options).popover('show');
			$('.popover_block .deleteItem').on('click', function () {
				Settings_Index_Js.DeleteLang(element, e);
				$(e.currentTarget).popover('hide');
			});
			$('.popover_block .cancel').on('click', function () {
				$(e.currentTarget).popover('hide');
			});
		});
		element.find('#setAsDefault').on('click', function (e) {
			$(e.currentTarget).popover(makeSureOptions).popover('show');
			$('.popover_block .setDefaultItem').on('click', function () {
				$(e.currentTarget).popover('hide');
				Settings_Index_Js.setAsDefaultLang(element, e);
			});
			$('.popover_block .cancel').on('click', function () {
				$(e.currentTarget).popover('hide');
			});
		});
	},
	ShowLangMondal: function (e) {
		let cloneModal = $('.AddNewLangMondal').clone(true, true);
		app.showModalWindow($(cloneModal));
	},
	ShowTranslationMondal: function (e) {
		var langs_list = $(".LangManagement #langs_list").val();
		var langs_fields = '';
		var cloneModal = $('.AddNewTranslationMondal').clone(true, true);
		cloneModal.find('input[name="langs"]').val(JSON.stringify(langs_list));
		$.each(langs_list, function (key) {
			langs_fields += '<div class="form-group"><label class="col-md-4 col-form-label">' + langs_list[key] + ':</label><div class="col-md-8"><input name="' + langs_list[key] + '" class="form-control" type="text" /></div></div>';
		});
		cloneModal.find('.add_translation_block').html(langs_fields);

		app.showModalWindow($(cloneModal));
		$(cloneModal).css("z-index", "9999999");
	},
	AddLangMondal: function (e) {
		const currentTarget = $(e.currentTarget),
			container = currentTarget.closest('.modalContainer');
		let SaveEvent = Settings_Index_Js.registerSaveEvent('add', {
			'type': 'Add',
			'label': container.find("input[name='label']").val(),
			'name': container.find("input[name='name']").val(),
			'prefix': container.find("input[name='prefix']").val()
		});
		if (SaveEvent.resp) {
			window.location.reload();
		}
	},
	AddTranslationMondal: function (e) {
		var currentTarget = $(e.currentTarget);
		var container = currentTarget.closest('.modalContainer');
		var SaveEvent = Settings_Index_Js.registerSaveEvent('addTranslation', $.extend({mod: $(".LangManagement #mods_list").val()}, container.find(".AddTranslationForm").serializeFormData()));
		if (SaveEvent.resp) {
			container.find('.AddNewTranslationMondal').modal('hide');
		}
		Settings_Index_Js.LoadEditLang(jQuery('#edit_lang'));
		e.preventDefault();
	},
	DeleteLang: function (closestTrElement, e) {
		Settings_Index_Js.registerSaveEvent('delete', {'prefix': closestTrElement.data('prefix')});
		closestTrElement.hide();
	},
	setAsDefaultLang: function (closestTrElement, e) {
		var SaveEvent = Settings_Index_Js.registerSaveEvent('setAsDefault', {'prefix': closestTrElement.data('prefix')});
		$(e.currentTarget).closest('td').find('#deleteItemC').remove();
		$(e.currentTarget).remove();
		var prefix = SaveEvent.result['prefixOld'];
		var tbodyElement = closestTrElement.closest('tbody');
		let OldTrDefaultLang = tbodyElement.find('tr[data-prefix="' + prefix + '"]');
		let buttonDelete = '';
		if (!OldTrDefaultLang.data('isDefault')) {
			buttonDelete = '<button class="btn btn-danger btn-sm marginLeftZero" data-toggle="confirmation" id="deleteItemC">' +
				'<span class="fas fa-trash fa-xs"></span>' + app.vtranslate('JS_DELETE') + '</button>';
		}
		OldTrDefaultLang.find('td:last').append(
			buttonDelete +
			'<button class="btn btn-success btn-sm marginLeftZero" data-toggle="confirmation" id="setAsDefault">' +
			'<span class="fas fa-check fa-xs"></span>' +
			app.vtranslate('JS_DEFAULT') + '</button>'
		);
		Settings_Index_Js.initEvant(OldTrDefaultLang);
	},
	registerSaveEvent: function (mode, data) {
		var response = '';
		var resp = '';
		var params = {}
		params.data = {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			action: 'SaveAjax',
			mode: mode,
		}
		params.data = $.extend(params.data, data);
		params.async = false;
		params.dataType = 'json';
		AppConnector.request(params).done(function (data) {
			response = data['result'];
			var params = {
				text: response['message'] ? response['message'] : app.vtranslate('JS_ERROR'),
			};
			if (response['success'] == true) {
				params.type = 'info';
			}
			Vtiger_Helper_Js.showPnotify(params);
			resp = response['success'];
		});
		return {resp: resp, params: params.data.params, result: response};
	},
	registerStats: function () {
		var thisInstance = this;
		jQuery('.showStats').on('click', function () {
			var params = {}
			var langs = jQuery('[name="langs"]').val();
			params.data = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'GetChart',
				langBase: jQuery('[name="langs_basic"]').val(),
				langs: langs
			}
			AppConnector.request(params).done(function (data) {
				var response = data['result'];
				if (response['success'] && response['data'].length !== 0) {
					thisInstance.showStats(response['data'], response['modules']);
				}
			});
		})
	},
	showStats: function (data, modules) {
		var thisInstance = this;
		var html = '<div class="col-md-12"><div class="panel panel-default"><div class="panel-body">';
		var langStats = 0;
		var shortages = [];
		for (var i in modules) {
			for (var k in modules[i]) {
				if (data[k].length == 1) {
					langStats += data[k][0];
					continue;
				}
				var max = data[k][0];
				langStats += max;
				delete data[k][0];
				html += '<div class="row moduleRow" data-module="' + k + '"><label class="col-md-3 form-control-plaintext col-form-label mt-2">' + modules[i][k] + ': </label><div class="form-control-plaintext col-md-9">'
				for (var q in data[k]) {
					if (typeof shortages[q] === "undefined") {
						shortages[q] = 0;
					}
					shortages[q] += data[k][q].length;
					var x = data[k][q].length * 100 / max
					html += '<button class="btn btn-xs btn-primary" data-lang="' + q + '"> ' + jQuery('select option[value="' + q + '"]').text() + ' - ' + x.toFixed(2) + '% </button>&nbsp;';
				}
				html += '</div></div>';
			}
		}
		html += '</div></div></div>';
		this.getDataCharts(shortages, langStats);
		var element = jQuery('.statsData').html(html);
		app.showScrollBar(element.find('.panel-body'), {
			height: '400px',
			railVisible: true,
		});
		thisInstance.registerStatsEvent();
	},
	registerStatsEvent: function () {
		var thisInstance = this;
		jQuery('.statsData .btn').on('click', function (e) {
			var progress = $.progressIndicator({
				position: 'html',
				blockInfo: {
					'enabled': true
				}
			});
			var element = jQuery(e.currentTarget);
			var row = element.closest('.moduleRow');
			var url =
				'index.php?module=' + app.getModuleName() +
				'&parent=' + app.getParentModuleName() +
				'&view=GetLabels' +
				'&langBase=' + jQuery('[name="langs_basic"]').val() +
				'&lang=' + element.data('lang') +
				'&sourceModule=' + row.data('module');
			app.showModalWindow(null, url, function (data) {
				progress.progressIndicator({'mode': 'hide'});
				data.find('button:not(.close)').on('click', function (e) {
					var button = jQuery(e.currentTarget);
					var input = button.closest('tr').find('input');
					thisInstance.changeTranslation(input, 'html', input.data('mod'))
				});
			});
		})
	},
	getDataCharts: function (shortages, max) {
		var k = 1;
		var data = [];
		var chartData = {
			labels: [],
			datasets: [
				{
					data: data,
					backgroundColor: [],
					datalabels: {
						font: {
							weight: 'bold'
						},
						color: 'white',
						anchor: 'end',
						align: 'start',
					}
				}
			],
		};
		for (var i in shortages) {
			var x = shortages[i] * 100 / max;
			var langName = jQuery('select option[value="' + i + '"]').text();
			data.push(Math.round(x * 100) / 100);
			chartData.datasets[0].backgroundColor.push(App.Fields.Colors.getRandomColor());
			chartData.labels.push(langName);
			++k;
		}
		if (data.length > 0) {
			jQuery('.widgetData').val(JSON.stringify(chartData));
			this.showCharts()
		}
	},
	showCharts: function () {
		var instance = Vtiger_Widget_Js.getInstance(jQuery('.chartBlock'), 'Bar');
		instance.init(jQuery('.chartBlock'));
		instance.loadChart({
			scales: {
				xAxes: [{
					ticks: {
						minRotation: 0
					}
				}]
			}
		});
	},
	registerEvents: function () {
		Settings_Index_Js.initEvants();
		this.registerStats();
	}

}
$(document).ready(function () {
	document.showDiff = false;
	Settings_Index_Js.registerEvents();
})
