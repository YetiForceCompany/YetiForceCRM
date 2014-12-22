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
    <div class="detailViewContainer">
        <div class="detailViewTitle" id="userPageHeader">
            <div class="row-fluid">
                <div class="span8">
                    <span class="row-fluid marginLeftZero">
						<span class="logo span2">
							{foreach key=ITER item=IMAGE_INFO from=$RECORD->getImageDetails()}
								{if !empty($IMAGE_INFO.path) && !empty($IMAGE_INFO.orgname)}
									<img src="{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}" alt="{$IMAGE_INFO.orgname}" title="{$IMAGE_INFO.orgname}" data-image-id="{$IMAGE_INFO.id}">
								{/if}
							{/foreach}
						</span>
						<span class="span9">
							<span id="userHeading" class="row-fluid">
								<h3>{$RECORD->getName()}</h3>
							</span>
						</span>
					</span>
                </div>
                <div class="span4">
                    <div class="row-fluid pull-right detailViewButtoncontainer">
						<div class="btn-toolbar pull-right">
                           							
							{foreach item=DETAIL_VIEW_BASIC_LINK from=$DETAILVIEW_LINKS['DETAILVIEWBASIC']}
								<div class="btn-group">
								<button class="btn"
										{if $DETAIL_VIEW_BASIC_LINK->isPageLoadLink()}
											onclick="window.location.href='{$DETAIL_VIEW_BASIC_LINK->getUrl()}'"
										{else}
											onclick={$DETAIL_VIEW_BASIC_LINK->getUrl()}
										{/if}>
									<strong>{vtranslate($DETAIL_VIEW_BASIC_LINK->getLabel(), $MODULE_NAME)}</strong>
								</button>
								</div>
							{/foreach}
							
							{if $DETAILVIEW_LINKS['DETAILVIEW']|@count gt 0}
								<span class="btn-group">
									<button class="btn dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
										<strong>{vtranslate('LBL_MORE', $MODULE_NAME)}</strong>&nbsp;&nbsp;<i class="caret"></i>
									</button>
									<ul class="dropdown-menu pull-right">
										{foreach item=DETAIL_VIEW_LINK from=$DETAILVIEW_LINKS['DETAILVIEW']}
											{if $DETAIL_VIEW_LINK->getLabel() eq 'Delete'}
												{if $CURRENT_USER_MODEL->isAdminUser() && $CURRENT_USER_MODEL->getId() neq $RECORD->getId()}
													<li id="{$MODULE_NAME}_detailView_moreAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_LINK->getLabel())}">
													<a href={$DETAIL_VIEW_LINK->getUrl()} >{vtranslate($DETAIL_VIEW_LINK->getLabel(), $MODULE_NAME)}</a>
												</li>
												{/if}
											{else}	
												<li id="{$MODULE_NAME}_detailView_moreAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_LINK->getLabel())}">
													<a href={$DETAIL_VIEW_LINK->getUrl()} >{vtranslate($DETAIL_VIEW_LINK->getLabel(), $MODULE_NAME)}</a>
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
        <div class="detailViewInfo userPreferences row-fluid">
            <div class="details span12">
                <form id="detailView" data-name-fields='{ZEND_JSON::encode($MODULE_MODEL->getNameFields())}'>
                    <div class="contents">
                    {/strip}