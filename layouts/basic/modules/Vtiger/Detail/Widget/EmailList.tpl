{strip}
<!-- tpl-Base-Detail-Widget-EmailList -->
	{assign var=WIDGET_UID value=\App\Layout::getUniqueId(\App\Language::translate($WIDGET['label'],$MODULE_NAME))}
	{assign var=CONFIG value=OSSMail_Module_Model::getComposeParameters()}
	<div class="c-detail-widget js-detail-widget" data-js="container">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}"
			 data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}">
			<div class="c-detail-widget__header js-detail-widget-header collapsed d-flex align-items-center py-1 flex-wrap" data-js="container|value">
				<div class="d-flex w-100 align-items-center">
					<input type="hidden" name="relatedModule" value="{$WIDGET['data']['relatedmodule']}"/>
					<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse" data-target="#{$WIDGET_UID}-collapse" aria-expanded="false" aria-controls="{$WIDGET_UID}-collapse">
						<span class="mdi mdi-chevron-up" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
					<span class="mdi mdi-chevron-down" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}"></span>
					</div>
					<div class="widgetTitle align-items-center py-1">
							<h5 class="mb-0 modCT_{$WIDGET['label']}">{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h5>
					</div>
					<div class="ml-auto">
						<button type="button" class="btn btn-sm btn-light showMailsModal mr-2"
								data-url="index.php?module=OSSMailView&view=MailsPreview&smodule={$MODULE_NAME}&srecord={$RECORD->getId()}&mode=showEmailsList">
							<span class="body-icon fas fa-search"
									title="{\App\Language::translate('LBL_SHOW_PREVIEW_EMAILS','OSSMailView')}"></span>
						</button>
						{if App\Config::main('isActiveSendingMails') && \App\Privilege::isPermitted('OSSMail')}
							{if $USER_MODEL->get('internal_mailer') == 1}
								{assign var=URLDATA value=OSSMail_Module_Model::getComposeUrl($MODULE_NAME, $RECORD->getId(), 'Detail', 'new')}
								<button type="button" class="btn btn-sm btn-light sendMailBtn" data-url="{$URLDATA}"
										data-module="{$MODULE_NAME}" data-record="{$RECORD->getId()}"
										data-popup="{$CONFIG['popup']}"
										title="{\App\Language::translate('LBL_CREATEMAIL', 'OSSMailView')}">
									<span class="fas fa-envelope"
											title="{\App\Language::translate('LBL_CREATEMAIL', 'OSSMailView')}"></span>
								</button>
								&nbsp;
							{else}
								{assign var=URLDATA value=OSSMail_Module_Model::getExternalUrl($MODULE_NAME, $RECORD->getId(), 'Detail', 'new')}
								{if $URLDATA}
									<a class="btn btn-sm btn-light" href="{$URLDATA}"
											title="{\App\Language::translate('LBL_CREATEMAIL', 'OSSMailView')}">
										<span class="fas fa-envelope"
												title="{\App\Language::translate('LBL_CREATEMAIL', 'OSSMailView')}"></span>
									</a>
									&nbsp;
								{/if}
							{/if}
						{/if}
						{if \App\Privilege::isPermitted('OSSMailView', 'ReloadRelationRecord')}
							<button type="button" class="btn btn-sm btn-light resetRelationsEmail">
								<span class="body-icon fas fa-retweet"
										title="{\App\Language::translate('BTN_RESET_RELATED_MAILS', 'OSSMailView')}"></span>
							</button>
						{/if}
					</div>
				</div>
				<div class="row w-100 no-gutters">
					<div class="col-6 pr-2">
						<div class="input-group input-group-sm">
							<select name="mail-type" title="{\App\Language::translate('LBL_CHANGE_MAIL_TYPE')}"
									class="form-control select2">
								<option value="All">
									{\App\Language::translate('LBL_ALL', 'OSSMailView')}
								</option>
								<option value="0">
									{\App\Language::translate('LBL_OUTCOMING', 'OSSMailView')}
								</option>
								<option value="1">
									{\App\Language::translate('LBL_INCOMING', 'OSSMailView')}
								</option>
								<option value="2">
									{\App\Language::translate('LBL_INTERNAL', 'OSSMailView')}
								</option>
							</select>
						</div>
					</div>
					<div class="col-6">
						{if $MODULE_NAME == 'Accounts'}
							<div class="input-group input-group-sm">
								<select name="mailFilter"
										title="{\App\Language::translate('LBL_CHANGE_FILTER', 'OSSMailView')}"
										class="form-control select2">
									<option value="All">{\App\Language::translate('LBL_FILTER_ALL', 'OSSMailView')}</option>
									<option value="Accounts">{\App\Language::translate('LBL_FILTER_ACCOUNTS', 'OSSMailView')}</option>
									<option value="Contacts">{\App\Language::translate('LBL_FILTER_CONTACTS', 'OSSMailView')}</option>
								</select>
							</div>
						{/if}
					</div>
				</div>
			</div>
			<div class="d-none modalView">
				<div class="modelContainer modal fade" tabindex="-1">
					<div class="modal-dialog modal-blg">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h5>
								<button type="button" class="close" data-dismiss="modal"
										aria-label="{\App\Language::translate('LBL_CLOSE')}">
									<span aria-hidden="true"
										  title="{\App\Language::translate('LBL_CLOSE')}">&times;</span>
								</button>
							</div>
							<div class="modal-body modalViewBody">
								_modalContent_
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="c-detail-widget__content widgetContent mailsList js-detail-widget-content collapse multi-collapse" id="{$WIDGET_UID}-collapse" aria-labelledby="{$WIDGET_UID}" data-js="container|value"></div>
		</div>
	</div>
<!-- /tpl-Base-Detail-Widget-EmailList -->
{/strip}
