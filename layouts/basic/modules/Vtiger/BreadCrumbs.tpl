{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if AppConfig::main('breadcrumbs') eq  'true'}
		{if isset($BREADCRUMB_TITLE)}
			{assign var="BREADCRUMBS" value=Vtiger_Menu_Model::getBreadcrumbs($BREADCRUMB_TITLE)}
		{else}
			{assign var="BREADCRUMBS" value=Vtiger_Menu_Model::getBreadcrumbs()}
		{/if}
		{assign var=HOMEICON value='userIcon-Home'}
		{if $BREADCRUMBS}
			<ol  class="breadcrumb breadcrumbsContainer my-0 py-auto px-0" aria-label="breadcrumb">
				<li class="breadcrumb-item" aria-current="page">
					<a href="{AppConfig::main('site_URL')}">
						<span class="{$HOMEICON}"></span>
					</a>
				</li>
				{foreach key=key item=item from=$BREADCRUMBS name=breadcrumbs}
					{if isset($item['url'])}
						<li class="breadcrumb-item">
							<a href="{$item['url']}">
								{$item['name']}
							</a>
						</li>
					{elseif $item@last}
						<li class="breadcrumb-item active js-text-content" data-js="text" aria-current="page">{$item['name']}</li>
						{else}
						<li class="breadcrumb-item"><a href="#">{$item['name']}</a></li>
						{/if}
						{assign var="ITEM_PREV" value=$item['name']}
					{/foreach}
			</ol>
		{/if}
	{/if}
{/strip}
