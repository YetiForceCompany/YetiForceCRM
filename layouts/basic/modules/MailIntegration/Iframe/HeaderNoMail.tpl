{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-MailIntegration-Iframe-HeaderNoMail -->
	<div>
		{if \App\Privilege::isPermitted('OSSMailView', 'CreateView')}
			{include file=\App\Layout::getTemplatePath('Iframe/HeaderNoMailAlert.tpl', $MODULE_NAME)}
		{/if}
		{if !empty($RELATIONS)}
			{include file=\App\Layout::getTemplatePath('Iframe/HeaderList.tpl', $MODULE_NAME) REMOVE_RECORD=false}
		{/if}
	</div>
<!-- /tpl-MailIntegration-Iframe-HeaderNoMail -->
{/strip}
