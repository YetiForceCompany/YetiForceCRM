{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
{assign var="MODULE_NAME" value=$MODULE_MODEL->get('name')}
<input id="recordId" type="hidden" value="{$RECORD->getId()}"/>
<div class="tpl-Users-UserViewHeader detailViewContainer">
	<div class="detailViewTitle" id="userPageHeader">
		<div class="widget_header row">
			<div class="col-md-8">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
			<div class="col-md-4 mt-2">
				<div class="float-right detailViewButtoncontainer mb-2">
					<div class="btn-toolbar btn-group float-right">
						{if isset($DETAILVIEW_LINKS['DETAIL_VIEW_ADDITIONAL'])}
							{foreach item=LINK from=$DETAILVIEW_LINKS['DETAIL_VIEW_ADDITIONAL']}
								{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='detailViewBasic'}
							{/foreach}
						{/if}
						{if $DETAILVIEW_LINKS['DETAIL_VIEW_BASIC']|@count gt 0}
							<div class="btn-group dropdown">
								<button class="btn btn-outline-dark dropdown-toggle" id="more-actions-button"
										data-toggle="dropdown" href="javascript:void(0);"
										aria-haspopup="true" aria-expanded="false">
									<strong>{\App\Language::translate('LBL_MORE', $MODULE_NAME)}</strong>
								</button>
								<div class="dropdown-menu" aria-labelledby="more-actions-button">
									{foreach item=DETAIL_VIEW_LINK from=$DETAILVIEW_LINKS['DETAIL_VIEW_BASIC']}
										{if $DETAIL_VIEW_LINK->getLabel() eq 'Delete'}
											{if $USER_MODEL->isAdminUser() && $USER_MODEL->getId() neq $RECORD->getId()}
												<a class="dropdown-item"
												   id="{$MODULE_NAME}_detailView_moreAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_LINK->getLabel())}"
												   href={$DETAIL_VIEW_LINK->getUrl()}>{\App\Language::translate($DETAIL_VIEW_LINK->getLabel(), $MODULE_NAME)}</a>
											{/if}
										{else}
											<a class="dropdown-item {$DETAIL_VIEW_LINK->getClassName()}"
											   id="{$MODULE_NAME}_detailView_moreAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_LINK->getLabel())}"
											   href="#"
											   data-url="{if !empty($DETAIL_VIEW_LINK->linkdata['url'])}{$DETAIL_VIEW_LINK->linkdata['url']}{/if}"
											   onclick="{$DETAIL_VIEW_LINK->linkurl}"
											>
												<span class="{$DETAIL_VIEW_LINK->linkicon} mr-1"></span>
												{\App\Language::translate($DETAIL_VIEW_LINK->getLabel(), $MODULE_NAME)}
											</a>
										{/if}
									{/foreach}
								</div>
							</div>
						{/if}

					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="detailViewInfo userPreferences row">
		<div class="details col-md-12">
			<form id="detailView"
				  data-name-fields="{\App\Purifier::encodeHtml(\App\Json::encode($MODULE_MODEL->getNameFields()))}">
				<div class="contents">
					{/strip}
