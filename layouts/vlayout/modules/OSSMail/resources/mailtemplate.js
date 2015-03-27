/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/

$('#vtmodulemenulink').ready(function() {
    var getUrlVars = function() {
        var vars = {};
        var parts = document.referrer.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m, key, value) {
            vars[key] = value;
        });
        return vars;
    }

    var openType = window.top !== window.self;
    if (openType) {
        var parntUrl = window.top.location.origin + window.top.location.pathname + '?module=OSSMailTemplates&action=';
    } else {
        var parntUrl = document.referrer;
        parntUrl = parntUrl.split('/index.php');
        parntUrl = parntUrl[0] + '/index.php?module=OSSMailTemplates&action=';
    }
    jQuery.ajax({
        type: 'Get',
        url: parntUrl + 'GetListTpl',
        async: false,
        success: function(data) {
            var module = getUrlVars()['mod'];
            jQuery(data.result).each(function(index, item) {
                if(module == item.module || item.type == 'PLL_MODULE'){
                   jQuery('#tplmenu .toolbarmenu').append('<li><a href="#" data-module="' + item.module + '" data-tplid="' + item.id + '" class="active">' + item.name + '</a></li>');  
                }
            });
            if (openType == false) {
                var selectModule = getUrlVars()['module'];

                jQuery('#tplmenu .toolbarmenu li a').each(function() {
                    var tplModule = jQuery(this).data('module');
                    if (tplModule != selectModule) {
                        jQuery(this).parent().hide();
                    }
                    else {
                        jQuery(this).parent().show();
                    }
                });
            }
        }
    });
    jQuery.ajax({
        type: 'Get',
        url: parntUrl + 'GetListModule',
        async: false,
        success: function(data) {
            var item = data.result;
            var module = getUrlVars()['mod'];
            var modules = [];
            for(var type in item){
                for(var i in item[type]){
                    if((item[type][i]['name'] == module || item[type][i]['type'] == 'PLL_MODULE') && jQuery.inArray(item[type][i]['name'], modules) == -1){
                        modules.push(item[type][i]['name']);
                        jQuery('#vtmodulemenu .toolbarmenu').append('<li><a href="#" data-module="' + item[type][i]['name'] + '" class="active">' + item[type][i]['tr_name'] + '</a></li>');
                    }
                }
            }
        }
    });
    jQuery('#vtmodulemenu .toolbarmenu li a').on('click', function() {
        var selectModule = jQuery(this).data('module');
        jQuery('#tplmenu .toolbarmenu li a').each(function() {
            var tplModule = jQuery(this).data('module');
            if (tplModule != selectModule && selectModule) {
                jQuery(this).parent().hide();
            } else {
                jQuery(this).parent().show();
            }
        });
    });
    jQuery('#tplmenu .toolbarmenu li a').on('click', function() {
        var id = jQuery(this).data('tplid');
        var recordId = getUrlVars()['record'],
                module = getUrlVars()['mod'],
                view = getUrlVars()['view'];
        if (view == 'List') {
            var chElement = jQuery(window.opener.document).find('.listViewEntriesCheckBox')[0];
            recordId = jQuery(chElement).val();
        }
        jQuery.ajax({
            type: 'Get',
            url: parntUrl + 'GetTpl',
            data: {
                id: id,
                record_id: recordId,
                select_module: module
            },
            success: function(data) {
                var old_subject = jQuery('[name="_subject"]').val();
                var html = jQuery("<div/>").html(data.result['content']).html();
                jQuery('[name="_subject"]').val(old_subject + data.result['subject']);
                if (window.tinyMCE && (ed = tinyMCE.get(rcmail.env.composebody))) {
                    var old_body = tinyMCE.activeEditor.getContent();
                    tinymce.activeEditor.setContent(html + old_body);
                } else {
                    var old_body = jQuery('#composebody').val();
                    jQuery('#composebody').val(html + old_body);
                }
            }
        });
    });
});