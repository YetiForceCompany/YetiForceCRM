{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div id="recordsListPreview">
		<input type="hidden" id="defaultDetailViewName" value="{AppConfig::module($MODULE, 'defaultDetailViewName')}" />
		<div class="wrappedPanel">
			<div class="rotatedText">
							{if isset($BREADCRUMB_TITLE)}
				{assign var="BREADCRUMBS" value=Vtiger_Menu_Model::getBreadcrumbs($BREADCRUMB_TITLE)}
			{else}
				{assign var="BREADCRUMBS" value=Vtiger_Menu_Model::getBreadcrumbs()}
			{/if}
				{foreach key=key item=item from=$BREADCRUMBS name=breadcrumbs}
					{if $key != 0 && $ITEM_PREV}
						<span class="separator">&nbsp;{vglobal('breadcrumbs_separator')}&nbsp;</span>
					{/if}
					{if isset($item['url'])}
						<a href="{$item['url']}">
							<span>{$item['name']}</span>
						</a>
					{else}
						<span>{$item['name']}</span>
					{/if}
					{assign var="ITEM_PREV" value=$item['name']}
				{/foreach}
			</div>
		</div>
		<div class="fixedListInitial col-md-3">
			<div class="fixedListContent">
				<div id="recordsList">
				{/strip}
