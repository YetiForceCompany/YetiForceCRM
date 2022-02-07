{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-MailIntegration-Iframe-HeaderMail -->
	<div>
		{if $MODULES}
			{include file=\App\Layout::getTemplatePath('Iframe/HeaderMailRelationAdder.tpl', $MODULE_NAME)}
		{/if}
		{if !empty($RELATIONS)}
			{include file=\App\Layout::getTemplatePath('Iframe/HeaderList.tpl', $MODULE_NAME) REMOVE_RECORD=true}
		{/if}
	</div>
	<!-- /tpl-MailIntegration-Iframe-HeaderMail -->
{/strip}
