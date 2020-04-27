{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Modals-MeetingModal -->
	<div class="modal-body js-modal-body mb-0 pb-0" data-js="container">
		<div class="row">
			{if $MEETING_URL}
				<div class="col-sm-4">
					<div class="card mb-2">
						<h6 class="card-header p-2">
							{\App\Language::translate('LBL_MEETING_COPY_URL', $MODULE_NAME)}
						</h6>
						<div class="card-body text-center">
							<div class="btn-group">
								<button type="button" class="btn btn-success js-clipboard" data-js="click"
										data-copy-attribute="clipboard-text" data-clipboard-text="{$MEETING_URL}"
										title="{\App\Language::translate('BTN_COPY_TO_CLIPBOARD', $MODULE_NAME)}">
									<span class="mdi mdi-content-copy"></span>
								</button>
								<a class="btn btn-danger" href="{$MEETING_URL}" rel="noreferrer noopener" target="_blank"
										title="{\App\Language::translate('LBL_MEETING_JOIN', $MODULE_NAME)}">
									<span class="mdi mdi-account-plus"></span>
								</a>
							</div>
						</div>
					</div>
				</div>
			{/if}
			<div class="col-sm-4">
				<div class="card mb-2">
					<h6 class="card-header p-2">
						{\App\Language::translate('LBL_MEETING_COPY_GUEST_URL', $MODULE_NAME)}
					</h6>
					<div class="card-body text-center">
						<div class="btn-group">
							<button type="button" class="btn btn-warning js-clipboard" data-js="click"
									data-copy-attribute="clipboard-text" data-clipboard-text="{$MEETING_GUEST_URL}"
									title="{\App\Language::translate('BTN_COPY_TO_CLIPBOARD', $MODULE_NAME)}">
								<span class="mdi mdi-content-copy"></span>
							</button>
							{if $SEND_INVITATION && !$MEETING_URL}
								<a class="btn btn-danger" href="{$MEETING_GUEST_URL}" rel="noreferrer noopener" target="_blank"
										title="{\App\Language::translate('LBL_MEETING_JOIN', $MODULE_NAME)}">
									<span class="mdi mdi-account-plus"></span>
								</a>
							{/if}
						</div>
					</div>
				</div>
			</div>
			{if $SEND_INVITATION && \App\Config::main('isActiveSendingMails') && \App\Privilege::isPermitted('OSSMail')}
				<div class="col-sm-4">
					<div class="card mb-2">
						<h6 class="card-header p-2">
							{\App\Language::translate('LBL_MEETING_SEND_INVITATION', $MODULE_NAME)}
						</h6>
						<div class="card-body text-center">
							{if $USER_MODEL->get('internal_mailer') == 1}
								{assign var=URLDATA value=OSSMail_Module_Model::getComposeUrl($MODULE_NAME, $RECORD_ID, 'Detail', 'new')}
								{assign var=CONFIG value=OSSMail_Module_Model::getComposeParameters()}
								<button type="button" class="btn btn-primary sendMailBtn" data-url="{$URLDATA}"
									data-module="{$MODULE_NAME}" data-record="{$RECORD_ID}" data-popup="{$CONFIG['popup']}"
									title="{\App\Language::translate('LBL_MEETING_SEND_INVITATION', $MODULE_NAME)}">
									<span class="mdi mdi-email-send-outline"></span>
								</button>
							{else}
								{assign var=URLDATA value=OSSMail_Module_Model::getExternalUrl($MODULE_NAME, $RECORD_ID, 'Detail', 'new')}
								{if $URLDATA}
									<a class="btn btn-primary" href="{$URLDATA}"
										title="{\App\Language::translate('LBL_MEETING_SEND_INVITATION', $MODULE_NAME)}">
										<span class="mdi mdi-email-send-outline"></span>
									</a>
								{/if}
							{/if}
						</div>
					</div>
				</div>
			{/if}
		</div>
	</div>
	<!-- /tpl-Base-Modals-MeetingModal -->
{/strip}
