{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-BreadCrumbs -->
	{if \App\Config::layout('breadcrumbs')}
		{if isset($BREADCRUMB_TITLE)}
			{assign var="BREADCRUMBS" value=Vtiger_Menu_Model::getBreadcrumbs($BREADCRUMB_TITLE)}
			{assign var="BREADCRUMBS_POPOVER" value=Vtiger_Menu_Model::getBreadcrumbs()}
		{else}
			{assign var="BREADCRUMBS" value=Vtiger_Menu_Model::getBreadcrumbs()}
			{assign var="BREADCRUMBS_POPOVER" value=$BREADCRUMBS}
		{/if}
		{assign var=HOME_ICON value='fas fa-home'}
		{if $BREADCRUMBS}
			{assign var="BREADCRUMBS_TEXT" value="<a href='`App\Config::main('site_URL')`'><span class='$HOME_ICON' aria-hidden='true'></span></a>"}
			{foreach key=key item=item from=$BREADCRUMBS_POPOVER}
				{assign var="BREADCRUMBS_ITEM" value=$item['name']}
				{if isset($item['url'])}
					{assign var="BREADCRUMBS_ITEM" value="<a href='`$item['url']`'>`$item['name']`</a>"}
				{/if}
				{assign var="BREADCRUMBS_TEXT" value="`$BREADCRUMBS_TEXT` / `$BREADCRUMBS_ITEM`"}
			{/foreach}
			<ol class="breadcrumb breadcrumbsContainer my-0 py-auto pl-2 pr-0 js-popover-tooltip--ellipsis-icon"
				data-content="{\App\Purifier::encodeHTML($BREADCRUMBS_TEXT)}" data-toggle="popover" data-js="popover | mouseenter">
				<li class="breadcrumb-item">
					{if \App\Config::layout('breadcrumbsHref')}
						<a href="{App\Config::main('site_URL')}">
							<span class="{$HOME_ICON}" aria-hidden="true"></span>
							<span class="sr-only">{\App\Language::translate('LBL_HOME')}</span>
						</a>
					{else}
						<span class="{$HOME_ICON}" aria-hidden="true"></span>
						<span class="sr-only">{\App\Language::translate('LBL_HOME')}</span>
					{/if}
				</li>
				{foreach key=key item=item from=$BREADCRUMBS name=breadcrumbs}
					{if isset($item['url'])}
						<li class="breadcrumb-item u-text-ellipsis">
							{if \App\Config::layout('breadcrumbsHref')}
								<a href="{$item['url']}">
									{if isset($item['icon'])}<span class="{$item['icon']} mr-1"></span>{/if}
									{$item['name']}
								</a>
							{else}
								{if isset($item['icon'])}<span class="{$item['icon']} mr-1"></span>{/if}
								{$item['name']}
							{/if}
						</li>
					{elseif $item@last}
						<li class="breadcrumb-item active js-text-content u-text-ellipsis d-flex" aria-current="page">{$item['name']}</li>
						<li class="js-popover-icon d-none mr-1" data-js="class: d-none"><span class="fas fa-info-circle fa-sm"></span></li>
					{else}
						<li class="breadcrumb-item u-text-ellipsis">
							{if isset($item['icon'])}<span class="{$item['icon']} mr-1"></span>{/if}
							{$item['name']}
						</li>
					{/if}
					{assign var="ITEM_PREV" value=$item['name']}
				{/foreach}
			</ol>
			{if isset($SELECTED_PAGE) && $SELECTED_PAGE->get('description')}
				{assign var="TRANSLATED_DESCRIPTION" value=\App\Language::translate($SELECTED_PAGE->get('description'),$QUALIFIED_MODULE)}
				{if !empty(trim($TRANSLATED_DESCRIPTION)) && $SELECTED_PAGE->get('description') !== $TRANSLATED_DESCRIPTION}
					<div class="js-popover-tooltip ml-2 d-inline my-auto u-h-fit u-cursor-pointer" data-js="popover" data-content="{$TRANSLATED_DESCRIPTION}">
						<span class="fas fa-info-circle"></span>
					</div>
				{/if}
			{/if}
		{/if}
	{/if}
	<!-- /tpl-Base-BreadCrumbs -->
{/strip}
