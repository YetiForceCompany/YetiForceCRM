{strip}
	{assign var=CONFIG value=OSSMail_Module_Model::getComposeParameters()}
	<div class="summaryWidgetContainer">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}">
			<div class="widget_header">
				<input type="hidden" name="relatedModule" value="{$WIDGET['data']['relatedmodule']}" />
				<div class="widgetTitle row">
					<div class="col-xs-7">
						<h4 class="modCT_{$WIDGET['label']}">{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h4>
					</div>
					<div class="col-xs-5">
						<div class="pull-right">
							<button type="button" class="btn btn-sm btn-light showMailsModal" data-url="index.php?module=OSSMailView&view=MailsPreview&smodule={$MODULE_NAME}&srecord={$RECORD->getId()}&mode=showEmailsList">
								<span class="body-icon fas fa-search" title="{\App\Language::translate('LBL_SHOW_PREVIEW_EMAILS','OSSMailView')}"></span>
							</button>
							&nbsp;
							{if AppConfig::main('isActiveSendingMails') && \App\Privilege::isPermitted('OSSMail')}
								{if $USER_MODEL->get('internal_mailer') == 1}
									{assign var=URLDATA value=OSSMail_Module_Model::getComposeUrl($MODULE_NAME, $RECORD->getId(), 'Detail', 'new')}
									<button type="button" class="btn btn-sm btn-light sendMailBtn" data-url="{$URLDATA}" data-module="{$MODULE_NAME}" data-record="{$RECORD->getId()}" data-popup="{$CONFIG['popup']}" title="{\App\Language::translate('LBL_CREATEMAIL', 'OSSMailView')}">
										<span class="fas fa-envelope" title="{\App\Language::translate('LBL_CREATEMAIL', 'OSSMailView')}"></span>
									</button>&nbsp;
								{else}
									{assign var=URLDATA value=OSSMail_Module_Model::getExternalUrl($MODULE_NAME, $RECORD->getId(), 'Detail', 'new')}
									{if $URLDATA}
										<a class="btn btn-sm btn-light" href="{$URLDATA}" title="{\App\Language::translate('LBL_CREATEMAIL', 'OSSMailView')}">
											<span class="fas fa-envelope" title="{\App\Language::translate('LBL_CREATEMAIL', 'OSSMailView')}"></span>
										</a>&nbsp;
									{/if}
								{/if}
							{/if}
							{if \App\Privilege::isPermitted('OSSMailView', 'ReloadRelationRecord')}
								<button type="button" class="btn btn-sm btn-light resetRelationsEmail">
									<span class="body-icon fas fa-retweet" title="{\App\Language::translate('BTN_RESET_RELATED_MAILS', 'OSSMailView')}"></span>
								</button>
							{/if}
						</div>
					</div>
				</div>
				<hr class="rowHr" />
				<div class="row">
					<div class="col-xs-6 paddingRightZero">
						<select name="mail-type" title="{\App\Language::translate('LBL_CHANGE_MAIL_TYPE')}" class="form-control input-sm">
							<option value="All" {if $TYPE eq 'all'} selected="selected"{/if}>
								{\App\Language::translate('LBL_ALL', 'OSSMailView')}
							</option>
							<option value="0" {if $TYPE eq '0'} selected="selected"{/if}>
								{\App\Language::translate('LBL_OUTCOMING', 'OSSMailView')}
							</option>
							<option value="1" {if $TYPE eq '1'} selected="selected"{/if}>
								{\App\Language::translate('LBL_INCOMING', 'OSSMailView')}
							</option>
							<option value="2" {if $TYPE eq '2'} selected="selected"{/if}>
								{\App\Language::translate('LBL_INTERNAL', 'OSSMailView')}
							</option>
						</select>
					</div>
					<div class="col-xs-6">
						{if $MODULE_NAME == 'Accounts'}
							<select name="mailFilter" title="{\App\Language::translate('LBL_CHANGE_FILTER', 'OSSMailView')}" class="form-control input-sm">
								<option value="All">{\App\Language::translate('LBL_FILTER_ALL', 'OSSMailView')}</option>
								<option value="Accounts">{\App\Language::translate('LBL_FILTER_ACCOUNTS', 'OSSMailView')}</option>
								<option value="Contacts">{\App\Language::translate('LBL_FILTER_CONTACTS', 'OSSMailView')}</option>
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
								<h4 class="modal-title">{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h4>
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
