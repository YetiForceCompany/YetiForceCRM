{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if AppConfig::main('breadcrumbs') eq  'true'}
		<div class="breadCrumbs" >
			{if isset($BREADCRUMB_TITLE)}
				{assign var="BREADCRUMBS" value=Vtiger_Menu_Model::getBreadcrumbs($BREADCRUMB_TITLE)}
			{else}
				{assign var="BREADCRUMBS" value=Vtiger_Menu_Model::getBreadcrumbs()}
			{/if}
			{assign var=HOMEICON value='userIcon-Home'}
			{if $BREADCRUMBS}
				<div class="breadcrumbsContainer">
					<h2 class="breadcrumbsLinks textOverflowEllipsis">
						<a href="{AppConfig::main('site_URL')}">
							<span class="{$HOMEICON}"></span>
						</a>
						&nbsp;|&nbsp;
						{foreach key=key item=item from=$BREADCRUMBS name=breadcrumbs}
							{if $key != 0 && $ITEM_PREV}
								<span class="separator">&nbsp;{\AppConfig::main('breadcrumbs_separator')}&nbsp;</span>
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
					</h2>
				</div>
			{/if}
		</div>
	{/if}
{/strip}
