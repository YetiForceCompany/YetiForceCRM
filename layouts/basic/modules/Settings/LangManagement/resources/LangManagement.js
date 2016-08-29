/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
var Settings_Index_Js = {
	initEvants: function () {
		$('.LangManagement .add_lang').click(Settings_Index_Js.ShowLangMondal);
		$('.LangManagement .edit_lang a').click(function (e) {
			jQuery('#edit_lang').html('');
			document.showDiff = false;
			Settings_Index_Js.LoadEditLang(this)
		});
		$('.LangManagement .editHelpIcon a').click(function (e) {
			jQuery('#editHelpIcon').html('');
			document.showDiff = false;
			Settings_Index_Js.LoadEditLang(this)
		});
		$('.AddNewLangMondal .btn-primary').click(Settings_Index_Js.AddLangMondal);
		$('.AddNewTranslationMondal .btn-primary').click(Settings_Index_Js.AddTranslationMondal);
		$('#lang_list tr').each(function (index, element) {
			element = $(element);
			Settings_Index_Js.initEvant(element);
		})
	},
	LoadEditLang: function (e) {
		var element = jQuery(e);
		var position = element.attr('href');
		var tpl = element.data('mode');
		if (typeof position == 'undefined') {
			position = '#' + element.attr('id');
		}
		var progress = $.progressIndicator({
			'message': app.vtranslate('LBL_Loader'),
			'position': position,
			'blockInfo': {
				'enabled': true
			}
		});
		var url = '';
		if ($(".LangManagement " + position + " #langs_list").val() != undefined) {
			url += '&lang=' + $(".LangManagement " + position + " #langs_list").val();
		}
		if ($(".LangManagement #mods_list").val() != undefined) {
			url += '&mod=' + $(".LangManagement " + position + " #mods_list").val();
		}
		if (document.showDiff == true) {
			url += '&sd=1';
		}
		if (typeof tpl != 'undefined') {
			url += '&tpl=' + tpl;
		}
		$.get("index.php?module=LangManagement&parent=Settings&view=Edit" + url, function (data) {
			jQuery(position).html(data);
			Settings_Index_Js.initEditLang(tpl, position);
			progress.progressIndicator({'mode': 'hide'});
		});
	},
	initEditLang: function (tpl, position) {
		var thisInstance = this;
		element = $(".LangManagement .layoutContent .active #langs_list");
		app.changeSelectElementView(element, 'selectize', {plugins: ['remove_button']}).on("change", function (e) {
			e = jQuery(this).closest('.active');
			Settings_Index_Js.LoadEditLang(e);
		});
		thisInstance.registerHoverCkEditor();
		thisInstance.registerHelpInfo();

		app.changeSelectElementView($(".LangManagement .active #helpInfoView"), 'selectize', {plugins: ['remove_button']}).on('change', function (e) {
			Settings_Index_Js.saveView(e, position)
		})
		app.changeSelectElementView($(".LangManagement .layoutContent .active #mods_list"), 'select2').on("change", function (e) {
			e = jQuery(this).closest('.active');
			Settings_Index_Js.LoadEditLang(e);
		});
		if (tpl != 'editHelpIcon') {
			$('#edit_lang .translation').change(function (e) {
				Settings_Index_Js.changeTranslation(e, position)
			});
			$('#edit_lang .add_translation').click(Settings_Index_Js.ShowTranslationMondal);
			$('#edit_lang .delete_translation').click(function (e) {
				Settings_Index_Js.deleteTranslation(e, position)
			});
		}
		$('.LangManagement ' + position + ' .show_differences').click(Settings_Index_Js.ShowDifferences);
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
	registerHoverCkEditor: function () {
		var thisInstance = this;
		jQuery('tr td button.editButton').on('click', function (e) {
			elementTd = jQuery(this).closest('td');
			thisInstance.registerEventForCkEditor(this);
			thisInstance.addClickOutSideEvent(elementTd);
		});
	},
	addClickOutSideEvent: function (element) {
		var thisInstance = this;
		element.one('clickoutside', function () {
			thisInstance.destroyEventForCkEditor(element);
		});
	},
	registerHelpInfo: function () {
		var form = jQuery('.LangManagement');
		form.find('.HelpInfoPopover').popover({trigger: 'hover', html: 'true', })
	},
	/**
	 * Function to register event for ckeditor
	 */
	registerEventForCkEditor: function (e) {
		var thisInstance = this;
		var element = jQuery(e);
		var elementTd = element.closest('td');
		var textarea = elementTd.find('textarea.ckEditorSource');
		element.addClass('hide');
		textarea.removeClass('hide');
		thisInstance.loadCkEditorElement(textarea);
	},
	/**
	 * Function to destroy ckeditor element
	 */
	destroyEventForCkEditor: function (element) {
		var thisInstance = this;
		var textarea = element.find('textarea.ckEditorSource');
		var elementId = textarea.attr('id');
		if (typeof elementId != 'undefined' && textarea.css('display') == 'none') {
			ckeditor = CKEDITOR.instances[elementId];
			var target = ckeditor.getData();
			if (textarea.val() != target) {
				textarea.val(target);
				if (target) {
					Settings_Index_Js.changeTranslation(textarea, '#editHelpIcon');
					element.find('.HelpInfoPopover').attr('data-content', target);
				} else {
					Settings_Index_Js.deleteTranslation(textarea, '#editHelpIcon');
					textarea.addClass('empty_value');
					element.find('.HelpInfoPopover').attr('data-content', '');
				}
			}
			ckeditor.destroy();
			textarea.addClass('hide');
			element.find('button.editButton').removeClass('hide');
		}
	},
	loadCkEditorElement: function (noteContentElement) {
		var thisInstance = this;
		var customConfig = {};
		if (noteContentElement.css('display') != 'none') {
			customConfig = {
				disableNativeSpellChecker: true,
				scayt_autoStartup: false,
				removePlugins: 'scayt',
				height: '5em',
				toolbar: null,
				toolbarGroups: [
					{name: 'document', groups: ['mode', 'document', 'doctools']},
					{name: 'basicstyles', groups: ['basicstyles', 'cleanup']},
					{name: 'clipboard', groups: ['clipboard', 'undo']}
				]}
			var ckEditorInstance = new Vtiger_CkEditor_Js();
			ckEditorInstance.loadCkEditor(noteContentElement, customConfig);
		}
	},
	saveView: function (e, position) {
		var target = $(e.currentTarget);
		if (typeof e.currentTarget == 'undefined')
			target = jQuery(e);
		var closestTrElement = target.closest('tr');
		var progress = $.progressIndicator({
			'message': app.vtranslate('LBL_Loader'),
			'position': position,
			'blockInfo': {
				'enabled': true
			}
		});
		var SaveEvent = Settings_Index_Js.registerSaveEvent('saveView', {
			'fieldid': target.data('fieldid'),
			'mod': $(".LangManagement #mods_list").data('target') ? $(".LangManagement #mods_list").data('target') : $(".LangManagement #mods_list").val(),
			'value': target.val(),
		});
		progress.progressIndicator({'mode': 'hide'});
	},
	ShowDifferences: function (e) {
		var target = $(e.currentTarget);
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
		if (typeof e.currentTarget == 'undefined')
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
		Settings_Index_Js.registerSaveEvent('SaveTranslation', {
			'lang': target.data('lang'),
			'mod': mod,
			'type': target.data('type'),
			'langkey': closestTrElement.data('langkey'),
			'val': target.val(),
			'is_new': target.hasClass("empty_value"),
		});
		target.removeClass("empty_value");
		progress.progressIndicator({'mode': 'hide'});
	},
	deleteTranslation: function (e, position) {
		var target = $(e.currentTarget);
		if (typeof e.currentTarget == 'undefined') {
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
		Settings_Index_Js.registerSaveEvent('DeleteTranslation', {
			'lang': $(".LangManagement #langs_list").val(),
			'mod': $(".LangManagement " + position + " #mods_list").data('target') ? $(".LangManagement " + position + " #mods_list").data('target') : $(".LangManagement " + position + " #mods_list").val(),
			'langkey': closestTrElement.data('langkey'),
		});
		progress.progressIndicator({'mode': 'hide'});
		e = target.closest('.active');
		Settings_Index_Js.LoadEditLang(e);
	},
	initEvant: function (element) {
		element.find('input[type=checkbox]').change(Settings_Index_Js.CheckboxChange);
		var options = {
			title: app.vtranslate('LBL_AreYouSure'),
			trigger: 'manual',
			placement: 'left',
			html: 'true',
			content: '<div class="popover_block"><button class="btn btn-danger deleteItem marginLeft10">' + app.vtranslate('LBL_YES') + '</button>   <button class="btn btn-warning pull-right cancel">' + app.vtranslate('Cancel') + '</button></div>'
		}
		var makeSureOptions = {
			title: app.vtranslate('JS_ARE_YOU_SURE_TO_SET_AS_DEFAULT'),
			trigger: 'manual',
			placement: 'left',
			html: 'true',
			content: '<div class="popover_block"><button class="btn btn-danger setDefaultItem">' + app.vtranslate('LBL_YES') + '</button>   <button class="btn btn-warning pull-right cancel">' + app.vtranslate('Cancel') + '</button></div>'
		}
		element.find('#deleteItemC').click(function (e) {
			$(e.currentTarget).popover(options).popover('show');
			$('.popover_block .deleteItem').click(function () {
				Settings_Index_Js.DeleteLang(element, e);
				$(e.currentTarget).popover('hide');
			});
			$('.popover_block .cancel').click(function () {
				$(e.currentTarget).popover('hide');
			});
		});
		element.find('#setAsDefault').click(function (e) {
			$(e.currentTarget).popover(makeSureOptions).popover('show');
			$('.popover_block .setDefaultItem').click(function () {
				$(e.currentTarget).popover('hide');
				Settings_Index_Js.setAsDefaultLang(element, e);
			});
			$('.popover_block .cancel').click(function () {
				$(e.currentTarget).popover('hide');
			});
		});
	},
	CheckboxChange: function (e) {
		var target = $(e.currentTarget);
		var closestTrElement = target.closest('tr');
		Settings_Index_Js.registerSaveEvent('save', {'type': 'Checkbox', 'name': target.data('name'), 'prefix': closestTrElement.data('prefix'), 'val': target.is(':checked')});
	},
	ShowLangMondal: function (e) {
		var target = $(e.currentTarget);
		var cloneModal = $('.AddNewLangMondal').clone(true, true);
		app.showModalWindow($(cloneModal));
		$(cloneModal).css("z-index", "9999999");
	},
	ShowTranslationMondal: function (e) {
		var langs_list = $(".LangManagement #langs_list").val();
		var langs_fields = '';
		var cloneModal = $('.AddNewTranslationMondal').clone(true, true);
		cloneModal.find('input[name="langs"]').val(JSON.stringify(langs_list));
		$.each(langs_list, function (key) {
			langs_fields += '<div class="form-group"><label class="col-md-4 control-label">' + langs_list[key] + ':</label><div class="col-md-8"><input name="' + langs_list[key] + '" class="form-control" type="text" /></div></div>';
		});
		cloneModal.find('.add_translation_block').html(langs_fields);
		var target = $(e.currentTarget);

		app.showModalWindow($(cloneModal));
		$(cloneModal).css("z-index", "9999999");
	},
	AddLangMondal: function (e) {
		var currentTarget = $(e.currentTarget);
		var container = currentTarget.closest('.modalContainer');
		var SaveEvent = Settings_Index_Js.registerSaveEvent('add', {
			'type': 'Add',
			'label': container.find("input[name='label']").val(),
			'name': container.find("input[name='name']").val(),
			'prefix': container.find("input[name='prefix']").val()
		});
		if (SaveEvent.resp) {
			$('#lang_list table tbody').append('<tr data-prefix="' + SaveEvent.params.prefix + '"><td>' + SaveEvent.params.label + '</td><td>' + SaveEvent.params.name + '</td><td>' + SaveEvent.params.prefix + '</td><td><a href="index.php?module=LangManagement&parent=Settings&action=Export&lang=' + SaveEvent.params.prefix + '" class="btn btn-primary btn-xs marginLeft10">' + app.vtranslate('JS_EXPORT') + '</a> <button class="btn btn-success btn-xs marginLeft10" data-toggle="confirmation" id="setAsDefault">' + app.vtranslate('JS_DEFAULT') + '</button> <button class="btn btn-danger btn-xs" data-toggle="confirmation" data-original-title="" id="deleteItemC">' + app.vtranslate('Delete') + '</button></td></tr>');
			var element = $('#lang_list tr[data-prefix=' + SaveEvent.params.prefix + ']')
			Settings_Index_Js.initEvant(element);
			container.find('.AddNewLangMondal').modal('hide');
			$(".AddNewLangMondal input[name='label']").val('');
			$(".AddNewLangMondal input[name='name']").val('');
			$(".AddNewLangMondal input[name='prefix']").val('');
		}
	},
	AddTranslationMondal: function (e) {
		var currentTarget = $(e.currentTarget);
		var container = currentTarget.closest('.modalContainer');
		var SaveEvent = Settings_Index_Js.registerSaveEvent('AddTranslation', {
			'mod': $(".LangManagement #mods_list").val(),
			'form_data': container.find(".AddTranslationForm").serializeFormData()
		});
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
		OldTrDefaultLang = tbodyElement.find('tr[data-prefix="' + prefix + '"]')
		OldTrDefaultLang.find('td:last').prepend('<button class="btn btn-danger marginLeftZero" data-toggle="confirmation" data-original-title="" id="deleteItemC">' + app.vtranslate('Delete') + '</button> <button class="btn btn-primary marginLeftZero" data-toggle="confirmation" id="setAsDefault">' + app.vtranslate('JS_DEFAULT') + '</button>');
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
			params: data
		}
		params.async = false;
		params.dataType = 'json';
		AppConnector.request(params).then(
				function (data) {
					response = data['result'];
					var params = {
						text: response['message'],
						animation: 'show'
					};
					if (response['success'] == true) {
						params.type = 'info';
					}
					Vtiger_Helper_Js.showPnotify(params);
					resp = response['success'];
				},
				function (data, err) {

				}
		);
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
				langs: jQuery.isArray(langs) ? langs.join(',') : langs
			}
			AppConnector.request(params).then(
					function (data) {
						var response = data['result'];
						if (response['success'] && response['data'].length !== 0) {
							thisInstance.showStats(response['data'], response['modules']);
						}
					},
					function (data, err) {
					}
			);
		})
	},
	showStats: function (data, modules) {
		var thisInstance = this;
		var html = '<div class="col-md-8"><div class="panel panel-default"><div class="panel-body">';
		var langStats = 0;
		var shortages = [];
		for (var i in modules) {
			for (var k in modules[i]) {
				if (data[k].length == 1)
					continue;
				var max = data[k][0];
				langStats += max;
				delete data[k][0];
				html += '<div class="row moduleRow" data-module="' + k + '"><label class="col-md-3 form-control-static control-label marginTop2">' + modules[i][k] + ': </label><div class="form-control-static col-md-9">'
				for (var q in data[k]) {
					if (typeof shortages[q] == 'undefined') {
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
		var chartData = [];
		chartData['chart'] = {};
		var data = [];
		chartData['ticks'] = [];
		chartData['colors'] = ['#d18b2c'];
		for (var i in shortages) {
			var x = shortages[i] * 100 / max;
			var langName = jQuery('select option[value="' + i + '"]').text();
			data.push([k, x.toFixed(2)]);
			chartData['ticks'].push([k, langName]);
			++k;
		}
		if (data.length > 0) {
			chartData['chart'].data = data;
			chartData = jQuery.extend({}, chartData);
			chartData['valueLabels'] = {show: true, showAsHtml: true, align: "center", valign: 'middle'}
			jQuery('.widgetData').val(JSON.stringify(chartData));
			this.showCharts()
		}
	},
	showCharts: function () {
		var instance = Vtiger_Widget_Js.getInstance(jQuery('.chartBlock'), 'Bar');
		instance.init(jQuery('.chartBlock'));
		instance.loadChart();
		;
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
