{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}

<div id="mail_btn" style="overflow: auto;">	
	<span class="pull-right" style="text-align:right;">
		<a data-url="{$SENDURLDDATA}" data-popup="{$POPUP}" class="btn btn-default addButton sendMailBtn"><strong>{vtranslate('LBL_CREATEMAIL', 'OSSMailView')}</strong></a>
	</span>
	<span class="pull-right" title="{vtranslate('LBL_ChangeType', 'OSSMailView')}" style="font-weight:normal; font-size:small;">
		<select name="mail-type" title="{vtranslate('LBL_CHANGE_MAIL_TYPE')}" class="form-control">
			<option value="all" {if $TYPE eq 'all'} selected="selected"{/if}>{vtranslate('LBL_ALL', 'OSSMailView')}</option>
			<option value="0" {if $TYPE eq '0'} selected="selected"{/if}>{vtranslate('LBL_OUTCOMING', 'OSSMailView')}</option>
			<option value="1" {if $TYPE eq '1'} selected="selected"{/if}>{vtranslate('LBL_INCOMING', 'OSSMailView')}</option>
			<option value="2" {if $TYPE eq '2'} selected="selected"{/if}>{vtranslate('LBL_INTERNAL', 'OSSMailView')}</option>
		</select>
	</span>
</div>
<div class="mailRows row pushDown">
	{foreach from=$RECOLDLIST item=row}
		<div class="row-fluid mailRow">
			<div class="col-md-12" style="font-size:x-small;">
				<div class="pull-right muted" style="font-size:x-small;">
					<small title="{$row['date']}">{Vtiger_Util_Helper::formatDateDiffInStrings($row['date'])}</small>   
				</div>
				<h5>{if $row['type'] eq 0}<img src="layouts/vlayout/modules/OSSMailView/wychodzaca.png" />{elseif $row['type'] eq 1}<img src="layouts/vlayout/modules/OSSMailView/przychodzaca.png" />{elseif $row['type'] eq 2} <img src="layouts/vlayout/modules/OSSMailView/wewnetrzna.png" />{/if}{$row['subject']} {if $row['attachments'] eq 1}<img class="pull-right" src="layouts/vlayout/modules/OSSMailView/zalacznik.png" />{/if}<h5>
						</div>
						<div class="col-md-12">
							<div class="pull-right" >
								<a class="showMailBody" >
									<span class="body-icon glyphicon glyphicon-triangle-bottom"></span>&nbsp;&nbsp;&nbsp;&nbsp;
								</a>
							</div>
							<span class="pull-left" style="font-size:x-small;">{vtranslate('From', 'OSSMailView')}: {$row['from']}</span>
						</div>
						<div class="col-md-12" style="font-size:x-small;">
							{vtranslate('To', 'OSSMailView')}: {$row['to']}
							<div class="pull-right" >
								<a onclick="window.open('index.php?module=OSSMail&view=compose&id={$row['id']}&type=forward{if $POPUP}&popup=1{/if}',{if !$POPUP}'_self'{else}'_blank', 'resizable=yes,location=no,scrollbars=yes,toolbar=no,menubar=no,status=no'{/if})" class="btn btn-mini pull-right"><span title="{vtranslate('LBL_FORWARD','OSSMailView')}" class="glyphicon glyphicon-share-alt"></span></a>
								<a onclick="window.open('index.php?module=OSSMail&view=compose&id={$row['id']}&type=replyAll{if $POPUP}&popup=1{/if}',{if !$POPUP}'_self'{else}'_blank', 'resizable=yes,location=no,scrollbars=yes,toolbar=no,menubar=no,status=no'{/if})" class="btn btn-mini pull-right"><img width="14px" src="layouts/vlayout/modules/OSSMailView/previewReplyAll.png" alt="{vtranslate('LBL_REPLYALLL','OSSMailView')}" title="{vtranslate('LBL_REPLYALLL','OSSMailView')}"></a>
								<a onclick="window.open('index.php?module=OSSMail&view=compose&id={$row['id']}&type=reply{if $POPUP}&popup=1{/if}',{if !$POPUP}'_self'{else}'_blank', 'resizable=yes,location=no,scrollbars=yes,toolbar=no,menubar=no,status=no'{/if})" class="btn btn-mini pull-right"><img width="14px" src="layouts/vlayout/modules/OSSMailView/previewReply.png" alt="{vtranslate('LBL_REPLY','OSSMailView')}" title="{vtranslate('LBL_REPLY','OSSMailView')}"></a>
								&nbsp;&nbsp;&nbsp;&nbsp;
							</div>
						</div>
						<div class="col-md-12 defaultMarginP mailBody" style="display: none;">{$row['body']}</div>
						<div class="clearfix"></div>
						<div class="col-md-12">
							<hr/>
						</div>
						</div>
					{/foreach}
					</div>
{literal}
<script>
			jQuery(function() {
			var container = jQuery('.mailRows');
					container.on('click', '.showMailBody', function(e) {
					var widgetContainer = jQuery(e.currentTarget).closest('.mailRow');
							var mailBody = widgetContainer.find('.mailBody');
							var bodyIcon = jQuery(e.currentTarget).find('.body-icon');
							if (mailBody.css("display") == 'none'){
					mailBody.show();
							bodyIcon.removeClass("icon-chevron-down").addClass("icon-chevron-up");
					} else{
					mailBody.hide();
							bodyIcon.removeClass("icon-chevron-up").addClass("icon-chevron-down");
					}
					});
					jQuery('[name="mail-type"]').change(function(e) {
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
					jQuery('.sendMailBtn').click(function(e) {
			var sendButton = jQuery(e.currentTarget);
					var url = sendButton.data("url");
					var mod = sendButton.data("mod");
					var record = sendButton.data("record");
					var popup = sendButton.data("popup");
					if (mod == 'Contacts' || mod == 'Leads' || mod == 'Accounts'){
			var params = {};
					var resp = {};
					params.data = {module: 'OSSMail', action: 'getContactMail', mod: mod, ids: record}
			params.async = false;
					params.dataType = 'json';
					AppConnector.request(params).then(
					function(response) {
					resp = response['result'];
							if (resp.length > 1){
					var getConfig = jQuery.ajax({
					type: "GET",
							async: false,
							url: 'index.php?module=OSSMail&view=selectEmail',
							data: {resp: resp}
					});
							var callback = function(container){
							$('#sendEmailContainer #selectEmail').click(function(e) {
							url += '&to=' + $('input[name=selectedFields]:checked').val();
									sendMailWindow(url, popup);
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
					if (resp.length == 1){
					url += '&to=' + resp[0].email;
							sendMailWindow(url, popup);
					}
					if (resp.length == 0){
					sendMailWindow(url, popup);
					}
					}
			);
			} else{
			sendMailWindow(url, popup);
			}
			});
					function sendMailWindow(url, popup) {
					if (popup){
					window.open(url, '_blank', 'resizable=yes,location=no,scrollbars=yes,toolbar=no,menubar=no,status=no');
					} else{
					window.location.href = url;
					}
					}
			});
</script>
{/literal}
