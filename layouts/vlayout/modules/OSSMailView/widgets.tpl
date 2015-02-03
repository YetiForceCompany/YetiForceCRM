{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
<div id="mail_btn" style="overflow: auto;">	
	<span class="pull-right" style="text-align:right;">
		<button onclick="return false;" data-url="{$SENDURLDDATA}" data-mod="{$SMODULENAME}" data-record="{$SRECORD}" id="send_button" type="button" class="btn addButton"><strong>{vtranslate('LBL_CREATEMAIL', 'OSSMailView')}</strong></button>
	</span>
    <span class="pull-right" style="font-weight:normal; font-size:small;">
		<select name="mail-type" style="margin-right:5px; width:130px;">
			<option value="all" {if $TYPE eq 'all'} selected="selected"{/if}>{vtranslate('LBL_ALL', 'OSSMailView')}</option>
			<option value="0" {if $TYPE eq '0'} selected="selected"{/if}>{vtranslate('LBL_OUTCOMING', 'OSSMailView')}</option>
			<option value="1" {if $TYPE eq '1'} selected="selected"{/if}>{vtranslate('LBL_INCOMING', 'OSSMailView')}</option>
			<option value="2" {if $TYPE eq '2'} selected="selected"{/if}>{vtranslate('LBL_INTERNAL', 'OSSMailView')}</option>
		</select>
	</span>
</div>
<div class="mailRows">
	{foreach from=$RECOLDLIST item=row}
	<div class="row-fluid mailRow">
		<div class="span12" style="font-size:x-small;">
			<div class="pull-right muted" style="font-size:x-small;">
				<small title="{$row['date']}">{Vtiger_Util_Helper::formatDateDiffInStrings($row['date'])}</small>   
			</div>
			<h5 style="margin-left:2%;">{if $row['type'] eq 0}<img src="layouts/vlayout/modules/OSSMailView/wychodzaca.png" />{elseif $row['type'] eq 1}<img src="layouts/vlayout/modules/OSSMailView/przychodzaca.png" />{elseif $row['type'] eq 2} <img src="layouts/vlayout/modules/OSSMailView/wewnetrzna.png" />{/if}{$row['subject']} {if $row['attachments'] eq 1}<img class="pull-right" src="layouts/vlayout/modules/OSSMailView/zalacznik.png" />{/if}<h5>
		</div>
		<div class="span12">
			<div class="pull-right" >
				<a class="showMailBody" >
					<i class="body-icon icon-chevron-down"></i>&nbsp;&nbsp;&nbsp;&nbsp;
				</a>
			</div>
			<span class="pull-left" style="font-size:x-small;">{vtranslate('From', 'OSSMailView')}: {$row['from']}</span>
		</div>
		<div class="span12" style="font-size:x-small;">
			{vtranslate('To', 'OSSMailView')}: {$row['to']}
		</div>
		<div class="span12 defaultMarginP mailBody" style="display: none;">
			{Vtiger_Functions::removeHtmlTags(array('link', 'style', 'a', 'img', 'script'), $row['body'])} 
		</div>
	</div><hr/>
	{/foreach}
</div>
{literal}
<script>
	jQuery( function() {
		var container = jQuery('.mailRows');
		container.on('click', '.showMailBody', function(e) {
			var widgetContainer = jQuery(e.currentTarget).closest('.mailRow');
			var mailBody = widgetContainer.find('.mailBody');
			var bodyIcon = jQuery(e.currentTarget).find('.body-icon');
			if( mailBody.css( "display" ) == 'none'){
				mailBody.show();
				bodyIcon.removeClass( "icon-chevron-down" ).addClass( "icon-chevron-up" );
			}else{
				mailBody.hide();
				bodyIcon.removeClass( "icon-chevron-up" ).addClass( "icon-chevron-down" );
			}
		});
		jQuery('[name="mail-type"]').change( function(e) {
			var params = {};
			var currentElement = jQuery(e.currentTarget);
			var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
			var widgetDataContainer = summaryWidgetContainer.find('.widget_contents');
			var recordId = jQuery('#recordId').val();
			var progress = widgetDataContainer.progressIndicator();
			params['module'] = 'OSSMailView';
			params['view'] = 'widget';
			params['smodule'] = jQuery('#module').val();
			params['srecord'] = recordId;
			params['mode'] = 'showEmailsList';
			params['type'] = jQuery('[name="mail-type"]').val();
			AppConnector.request(params).then(
				function(data) {
					widgetDataContainer.html(data);
					progress.progressIndicator({'mode':'hide'});
				}
			);
		});
		jQuery('#send_button').click(function(e) {
			var send_button = jQuery(e.currentTarget);
			var main_url = 'index.php?module=OSSMail&view=compose';
			var url = send_button.attr( "data-url" );
			var mod = send_button.attr( "data-mod" );
			var record = send_button.attr( "data-record" );
			if(mod){
				main_url += '&mod='+mod;
			}
			if(record){
				main_url += '&record='+record;
			}
			if(url){
				main_url += url;
			}
			if(mod == 'Contacts' || mod == 'Leads' || mod == 'Accounts' ){
				var params = {};
				var resp = {};
				params.data = {module: 'OSSMail', action: 'getContactMail',mod: mod,ids: record}
				params.async = false;
				params.dataType = 'json';
				AppConnector.request(params).then(
					function(response) {
						resp = response['result'];
						if(resp.length > 1){
							var getConfig = jQuery.ajax({
								type: "GET",
								async: false,
								url: 'index.php?module=OSSMail&view=selectEmail',
								data: {resp: resp}
							});
							var callback = function(container){
								$('#sendEmailContainer #selectEmail').click(function(e) {
									main_url += '&to='+$('input[name=selectedFields]:checked').val();
									window.location.href = main_url;
								});	
							}
							getConfig.done(function(cfg) {
								var data = {}
								data.css = {'width':'700px'};
								data.cb = callback;
								data.data = cfg;
								app.showModalWindow(data);
							});
						}
						if(resp.length == 1){
							main_url += '&to='+resp[0].email;
							window.location.href = main_url;
						}
						if(resp.length == 0){
							window.location.href = main_url;
						}
					}
				);
			}else{
				window.location.href = main_url;
			}
		});
	});
</script>
{/literal}