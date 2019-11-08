{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-MailIntegration-RelationPreview-List -->
<div class="mb-1">
	<ul class="list-group">
		{foreach item="RELATION" from=$RELATIONS}
			{include file=\App\Layout::getTemplatePath('RelationPreview/PanelListItem.tpl', $MODULE_NAME) ROW=$RELATION REMOVE_RECORD=$REMOVE_RECORD}
		{/foreach}
	</ul>
</div>
<!-- /tpl-MailIntegration-RelationPreview-List -->
{/strip}
