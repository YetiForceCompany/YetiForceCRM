{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-MailIntegration-Iframe-HeaderNoMail -->
	<div>
		<div class="alert alert-warning mb-1 p-1 d-flex flex-wrap align-items-center" role="alert">
			{\App\Language::translate('LBL_MAIL_NOT_FOUND_IN_DB',$MODULE_NAME)}
			{if \App\Privilege::isPermitted('OSSMailView', 'CreateView') && $CURRENT_USER->getDetail('mail_scanner_actions')}
				<button class="btn btn-success btn-sm ml-auto js-import-mail js-popover-tooltip" data-title="{\App\Language::translate('LBL_IMPORT_MAIL_MANUALLY',$MODULE_NAME)}" data-content="{\App\Language::translate('LBL_IMPORT_MAIL_MANUALLY_DESC', $MODULE_NAME)}" data-placement="top" data-js="popover">
					<span class="fas fa-download"></span>
				</button>
			{/if}
		</div>
		{if !empty($RELATIONS)}
			{include file=\App\Layout::getTemplatePath('Iframe/HeaderList.tpl', $MODULE_NAME) REMOVE_RECORD=false}
		{/if}
	</div>
	<!-- /tpl-MailIntegration-Iframe-HeaderNoMail -->
{/strip}
