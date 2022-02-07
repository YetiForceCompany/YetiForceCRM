{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-MailIntegration-Iframe-Container -->
	<div class="js-iframe-container" data-mail-id="{$MAIL_ID}" data-js="data">
		{if isset($URL)}
			<div class="mx-1">
				<input type="hidden" id="autoCompleteFields" class="js-mailAutoCompleteFields" value="{\App\Purifier::encodeHtml(\App\Json::encode(\App\Config::component('Mail','autoCompleteFields', [])))}" />
				{if $MAIL_ID}
					<div class="js-relations-container" data-js="html">
						{include file=\App\Layout::getTemplatePath('Iframe/HeaderMail.tpl', $MODULE_NAME)}
					</div>
				{else}
					{include file=\App\Layout::getTemplatePath('Iframe/HeaderNoMail.tpl', $MODULE_NAME)}
				{/if}
			</div>
			{include file=\App\Layout::getTemplatePath('Iframe/Content.tpl', $MODULE_NAME)}
		{else}
			<div class="alert alert-danger m-2" role="alert">{\App\Language::translate('LBL_PERMISSION_DENIED')}</div>
		{/if}
	</div>
	<!-- /tpl-MailIntegration-Iframe-Container -->
{/strip}
