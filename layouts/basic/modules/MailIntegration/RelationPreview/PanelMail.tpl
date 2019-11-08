{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-MailIntegration-RelationPreview-PanelMail -->
	<div>
		{if $MODULES}
			{include file=\App\Layout::getTemplatePath('RelationPreview/PanelMailRelationAdder.tpl', $MODULE_NAME)}
		{/if}
		{if !empty($RELATIONS)}
			{include file=\App\Layout::getTemplatePath('RelationPreview/PanelList.tpl', $MODULE_NAME) REMOVE_RECORD=true}
		{/if}
	</div>
<!-- /tpl-MailIntegration-RelationPreview-PanelMail -->
{/strip}
