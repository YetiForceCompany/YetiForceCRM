{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	<div class="tpl-Base-QuickDetail-TabContent">
		{foreach item=COMPONENT from=$COMPONENTS}
			{include file=\App\Layout::getTemplatePath("QuickDetail/Components/{$COMPONENT['type']}.tpl", $MODULE_NAME)}
		{/foreach}
	</div>
{/strip}
