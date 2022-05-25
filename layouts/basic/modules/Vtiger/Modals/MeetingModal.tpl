{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Modals-MeetingModal -->
	<div class="modal-body js-modal-body mb-0" data-js="container">
		<div class="row text-center">
			<div class="col-sm-4 mx-auto">
				<div class="card mb-2 border-0">
					<h6 class="card-header p-2 border-0 bg-white">
						{\App\Language::translate('LBL_MEETING_COPY_GUEST_URL', $MODULE_NAME)}
					</h6>
					<div class="card-body text-center p-0">
						<div class="row mb-3 mt-2">
							<div class="col-xs-6 mx-auto">
								<span class="m-1 u-fs-4x yfi-guest-link text-success js-clipboard u-cursor-pointer" data-js="click"
									data-copy-attribute="clipboard-text" data-clipboard-text="{\App\Purifier::encodeHtml($MEETING_GUEST_URL)}"
									title="{\App\Language::translate('BTN_COPY_TO_CLIPBOARD', $MODULE_NAME)}">
								</span>
								<div class="text-center text-success">
									{\App\Language::translate('LBL_COPY', $MODULE_NAME)}
								</div>
							</div>
							{if $SIMPLE_URL && !$MEETING_URL}
								<div class="col-xs-6 mx-auto">
									<a class="m-1 u-fs-4x yfi-enter-guest text-success" href="{\App\Purifier::encodeHtml($MEETING_GUEST_URL)}" rel="noreferrer noopener" target="_blank"
										title="{\App\Language::translate('LBL_MEETING_JOIN', $MODULE_NAME)}">
									</a>
									<div class="text-success">
										{\App\Language::translate('LBL_MEETING_JOIN', $MODULE_NAME)}
									</div>
								</div>
							{/if}
						</div>
						<div class="u-fs-xs">
							{\App\Language::translate('LBL_MEETING_GUEST_DESCRIPTION', $MODULE_NAME)}
						</div>
					</div>
				</div>
			</div>
			{if $SEND_INVITATION}
				<div class="col-sm-4 mx-auto">
					<div class="card mb-2 border-0">
						<h6 class="card-header p-2 border-0 bg-white">
							{\App\Language::translate('LBL_MEETING_SEND_INVITATION', $MODULE_NAME)}
						</h6>
						<div class="card-body text-center p-0">
							<div class="row mb-3 mt-2">
								<div class="col-xs-6 mx-auto">
									<span class="m-1 yfi-copy-invitation text-info u-fs-4x u-cursor-pointer js-template-copy" data-js="click" data-clipboard-target="#iframeTemlate"
										title="{\App\Language::translate('BTN_COPY_TO_CLIPBOARD', $MODULE_NAME)}">
									</span>
									<div class="text-center text-info">
										{\App\Language::translate('LBL_COPY', $MODULE_NAME)}<br>
									</div>
								</div>
								<iframe id="iframeTemlate" width="0" height="0" frameborder="0" data-js="iframe" srcdoc="{\App\Purifier::encodeHtml($EMAIL_TEMPLATE_DATA)}"></iframe>
								<div class="col-xs-6 mx-auto">
									{if \App\Mail::checkInternalMailClient()}
										{assign var=URLDATA value=OSSMail_Module_Model::getComposeUrl($MODULE_NAME, $RECORD_ID, 'Detail', 'new')}
										{assign var=URLDATA value="{$URLDATA}&template={$EMAIL_TEMPLATE}&templateParams={$TEMPLATE_PARAMS}"}
										{assign var=CONFIG value=OSSMail_Module_Model::getComposeParameters()}
										<span class="m-1 yfi-send-invitation text-info u-fs-4x sendMailBtn u-cursor-pointer" data-url="{\App\Purifier::encodeHtml($URLDATA)}"
											data-module="{$MODULE_NAME}" data-record="{$RECORD_ID}" data-popup="{$CONFIG['popup']}"
											title="{\App\Language::translate('LBL_MEETING_SEND_INVITATION', $MODULE_NAME)}">
										</span>
										<div class="text-center text-info">
											{\App\Language::translate('LBL_SEND_MAIL', $MODULE_NAME)}
										</div>
									{else}
										{assign var=URLDATA value=OSSMail_Module_Model::getExternalUrl($MODULE_NAME, $RECORD_ID, 'Detail', 'new')}
										{if $URLDATA}
											<a class="m-1 yfi-send-invitation text-info u-fs-4x" href="{\App\Purifier::encodeHtml($URLDATA)}"
												title="{\App\Language::translate('LBL_MEETING_SEND_INVITATION', $MODULE_NAME)}">
											</a>
											<div class="text-center text-info">
												{\App\Language::translate('LBL_SEND_MAIL', $MODULE_NAME)}
											</div>
										{/if}
									{/if}
								</div>
							</div>
							<div class="u-fs-xs">
								{\App\Language::translate('LBL_MEETING_SEND_INVITATION_DESCRIPTION', $MODULE_NAME)}
							</div>
						</div>
					</div>
				</div>
			{/if}
			{if $MEETING_URL}
				<div class="col-sm-4 mx-auto">
					<div class="card mb-2 border-0">
						<h6 class="card-header p-2 border-0 bg-white">
							{\App\Language::translate('LBL_MEETING_COPY_URL', $MODULE_NAME)}
						</h6>
						<div class="card-body text-center p-0">
							<div class="mb-3 mt-2 row">
								<div class="col-xs-6 mx-auto">
									<span class="m-1 u-fs-4x yfi-moderator-link text-danger js-clipboard u-cursor-pointer" data-js="click"
										data-copy-attribute="clipboard-text" data-clipboard-text="{\App\Purifier::encodeHtml($MEETING_URL)}"
										title="{\App\Language::translate('BTN_COPY_TO_CLIPBOARD', $MODULE_NAME)}">
									</span>
									<div class="text-center text-danger">
										{\App\Language::translate('LBL_COPY', $MODULE_NAME)}
									</div>
								</div>
								<div class="col-xs-6 mx-auto">
									<a class="m-1 u-fs-4x yfi-enter-moderator text-danger" href="{\App\Purifier::encodeHtml($MEETING_URL)}" rel="noreferrer noopener" target="_blank"
										title="{\App\Language::translate('LBL_MEETING_JOIN', $MODULE_NAME)}">
									</a>
									<div class="text-danger">
										{\App\Language::translate('LBL_MEETING_JOIN', $MODULE_NAME)}
									</div>
								</div>
							</div>
							<div class="u-fs-xs">
								{\App\Language::translate('LBL_MEETING_URL_DESCRIPTION', $MODULE_NAME)}
							</div>
						</div>
					</div>
				</div>
			{/if}
		</div>
	</div>
	<!-- /tpl-Base-Modals-MeetingModal -->
{/strip}
