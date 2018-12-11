{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-BreadCrumbs -->
	{if AppConfig::main('breadcrumbs') eq  'true'}
		{if isset($BREADCRUMB_TITLE)}
			{assign var="BREADCRUMBS" value=Vtiger_Menu_Model::getBreadcrumbs($BREADCRUMB_TITLE)}
		{else}
			{assign var="BREADCRUMBS" value=Vtiger_Menu_Model::getBreadcrumbs()}
		{/if}
		{assign var=HOMEICON value='userIcon-Home'}
		{if $BREADCRUMBS}
			<ol class="breadcrumb breadcrumbsContainer my-0 py-auto pl-2 pr-0 js-popover-tooltip--ellipsis-icon"
				data-content="{App\Purifier::encodeHtml($BREADCRUMBS[$BREADCRUMBS|@count - 1]['name'])}"
				data-toggle="popover"
				data-js="popover | mouseenter">
				<li class="breadcrumb-item">
					<a href="{AppConfig::main('site_URL')}">
						<span class="{$HOMEICON}" aria-hidden="true"></span>
						<span class="sr-only">{\App\Language::translate('LBL_HOME')}</span>
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
						<li class="breadcrumb-item active js-popover-text js-text-content" data-js="text"
							aria-current="page">{$item['name']}
						</li>
						<li class="js-popover-icon d-none mr-1" data-js="class: d-none">
							<span class="fas fa-info-circle fa-sm"></span>
						</li>
					{else}
						<li class="breadcrumb-item"><a href="#">{$item['name']}</a></li>
					{/if}
					{assign var="ITEM_PREV" value=$item['name']}
				{/foreach}
			</ol>
			{if isset($SELECTED_PAGE)}
				{assign var="TRANSLATED_DESCRIPTION" value=\App\Language::translate($SELECTED_PAGE->get('description'),$QUALIFIED_MODULE)}
				{if !empty(trim($TRANSLATED_DESCRIPTION)) && $SELECTED_PAGE->get('description') !== $TRANSLATED_DESCRIPTION}
					<div class="js-popover-tooltip ml-2 d-inline mt-2" data-js="popover"
						 data-content="{$TRANSLATED_DESCRIPTION}">
						<span class="fas fa-info-circle"></span>
					</div>
				{/if}
			{/if}
		{/if}
	{/if}
	<!-- /tpl-Base-BreadCrumbs -->
{/strip}
