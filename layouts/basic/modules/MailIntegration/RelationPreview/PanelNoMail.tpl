{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-MailIntegration-RelationPreview-PanelNoMail -->
	<div>
		{if \App\Privilege::isPermitted('OSSMailView', 'CreateView')}
			{include file=\App\Layout::getTemplatePath('RelationPreview/PanelNoMailAlert.tpl', $MODULE_NAME)}
		{/if}
		{if !empty($RELATIONS)}
			{include file=\App\Layout::getTemplatePath('RelationPreview/PanelList.tpl', $MODULE_NAME) REMOVE_RECORD=false}
		{/if}
	</div>
<!-- /tpl-MailIntegration-RelationPreview-PanelNoMail -->
{/strip}
