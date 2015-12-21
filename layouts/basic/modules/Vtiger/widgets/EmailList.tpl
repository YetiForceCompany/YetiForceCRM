{strip}
	{assign var=EMAIL_MODULE_MODEL value=Vtiger_Module_Model::getInstance('OSSMail')}
	{assign var=CONFIG value=$EMAIL_MODULE_MODEL->getComposeParameters()}
	{assign var=URLDATA value=$EMAIL_MODULE_MODEL->getComposeUrl($MODULE_NAME, $RECORD->getId(), 'Detail', $CONFIG['popup'])}
	<div class="summaryWidgetContainer">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}">
			<div class="widget_header">
				<input type="hidden" name="relatedModule" value="{$WIDGET['data']['relatedmodule']}" />
					<div class="widgetTitle row">
						<div class="col-xs-9">
							<h4 class="moduleColor_{$WIDGET['label']}">{vtranslate($WIDGET['label'],$MODULE_NAME)}</h4>
						</div>
						<div class="col-xs-3">
							<div class="pull-right">
								<button type="button" class="btn btn-sm btn-default showMailsModal" data-url="index.php?module=OSSMailView&view=MailsPreview&smodule={$MODULE_NAME}&srecord={$RECORD->getId()}&mode=showEmailsList">
									<span class="body-icon glyphicon glyphicon-search" title="{vtranslate('LBL_SHOW_PREVIEW_EMAILS','OSSMailView')}"></span>
								</button>
								&nbsp;
								{if AppConfig::main('isActiveSendingMails')}
									<a title="{vtranslate('LBL_CREATEMAIL', 'OSSMailView')}" data-url="{$URLDATA}" data-popup="{$CONFIG['popup']}" class="btn btn-default btn-sm addButton sendMailBtn">
										<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>
									</a>
								{/if}
							</div>
						</div>
					</div>
					<hr class="rowHr"/>
					<div class="row">
						<div class="col-xs-6 paddingRightZero">
							<select name="mail-type" title="{vtranslate('LBL_CHANGE_MAIL_TYPE')}" class="form-control input-sm">
								<option value="All" {if $TYPE eq 'all'} selected="selected"{/if}>
									{vtranslate('LBL_ALL', 'OSSMailView')}
								</option>
								<option value="0" {if $TYPE eq '0'} selected="selected"{/if}>
									{vtranslate('LBL_OUTCOMING', 'OSSMailView')}
								</option>
								<option value="1" {if $TYPE eq '1'} selected="selected"{/if}>
									{vtranslate('LBL_INCOMING', 'OSSMailView')}
								</option>
								<option value="2" {if $TYPE eq '2'} selected="selected"{/if}>
									{vtranslate('LBL_INTERNAL', 'OSSMailView')}
								</option>
							</select>
						</div>
						<div class="col-xs-6">
							{if $MODULE_NAME == 'Accounts'}
								<select name="mailFilter" title="{vtranslate('LBL_CHANGE_FILTER', 'OSSMailView')}" class="form-control input-sm">
									<option value="All">{vtranslate('LBL_FILTER_ALL', 'OSSMailView')}</option>
									<option value="Accounts">{vtranslate('LBL_FILTER_ACCOUNTS', 'OSSMailView')}</option>
									<option value="Contacts">{vtranslate('LBL_FILTER_CONTACTS', 'OSSMailView')}</option>
								</select>
							{/if}
						</div>
					</div>
			</div>
			<div class="hide modalView">
				<div class="modelContainer modal fade" tabindex="-1">
					<div class="modal-dialog modal-blg">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title">{vtranslate($WIDGET['label'],$MODULE_NAME)}</h4>
							</div>
							<div class="modal-body modalViewBody">
								_modalContent_
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="widget_contents widgetContent mailsList"></div>
		</div>
	</div>
{/strip}
