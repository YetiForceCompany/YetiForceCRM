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
    <input id="recordId" type="hidden" value="{$RECORD->getId()}" />
    <div class="tpl-Users-UserViewHeader detailViewContainer">
        <div class="detailViewTitle" id="userPageHeader">
            <div class="widget_header row">
                <div class="col-md-8">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
                </div>
                <div class="col-md-4 mt-2">
                    <div class="float-right detailViewButtoncontainer mb-2">
						<div class="btn-toolbar float-right">		
							{foreach item=LINK from=$DETAILVIEW_LINKS['DETAIL_VIEW_ADDITIONAL']}
								<div class="mr-1">
								{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='detailViewBasic'}
								</div>
							{/foreach}
							{if $DETAILVIEW_LINKS['DETAIL_VIEW_BASIC']|@count gt 0}
								<span class="btn-group">
									<button class="btn btn-light dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
										<strong>{\App\Language::translate('LBL_MORE', $MODULE_NAME)}</strong>&nbsp;&nbsp;<i class="caret"></i>
									</button>
									<ul class="dropdown-menu float-right">
										{foreach item=DETAIL_VIEW_LINK from=$DETAILVIEW_LINKS['DETAIL_VIEW_BASIC']}
											{if $DETAIL_VIEW_LINK->getLabel() eq 'Delete'}
												{if $USER_MODEL->isAdminUser() && $USER_MODEL->getId() neq $RECORD->getId()}
													<li id="{$MODULE_NAME}_detailView_moreAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_LINK->getLabel())}">
														<a class="dropdown-item" href={$DETAIL_VIEW_LINK->getUrl()} >{\App\Language::translate($DETAIL_VIEW_LINK->getLabel(), $MODULE_NAME)}</a>
													</li>
												{/if}
											{else}	
												<li id="{$MODULE_NAME}_detailView_moreAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_LINK->getLabel())}">
													<a class="dropdown-item" href={$DETAIL_VIEW_LINK->getUrl()} >{\App\Language::translate($DETAIL_VIEW_LINK->getLabel(), $MODULE_NAME)}</a>
												</li>
											{/if}
										{/foreach}
									</ul>
								</span>
							{/if}

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="detailViewInfo userPreferences row">
            <div class="details col-md-12">
                <form id="detailView" data-name-fields='{\App\Json::encode($MODULE_MODEL->getNameFields())}'>
                    <div class="contents">
                    {/strip}
