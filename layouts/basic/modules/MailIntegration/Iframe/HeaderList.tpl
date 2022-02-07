{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-MailIntegration-Iframe-HeaderList -->
	<div class="mb-1">
		<ul class="list-group">
			{foreach item="RELATION" from=$RELATIONS}
				{include file=\App\Layout::getTemplatePath('Iframe/HeaderListItem.tpl', $MODULE_NAME) RECORD=$RELATION REMOVE_RECORD=$REMOVE_RECORD}
			{/foreach}
		</ul>
	</div>
	<!-- /tpl-MailIntegration-Iframe-HeaderList -->
{/strip}
