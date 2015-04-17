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
    {assign var="announcement" value=$ANNOUNCEMENT->get('announcement')}
    {assign var='count' value=0}
    {assign var="dateFormat" value=$USER_MODEL->get('date_format')}
    <div class="navbar commonActionsContainer noprint">
        <div class="actionsContainer row-fluid">
            <div id="companyLogo-container" class="span2">
                <span class="companyLogo"><img src="{$COMPANY_LOGO->get('imagepath')}" title="{$COMPANY_LOGO->get('title')}" alt="{$COMPANY_LOGO->get('alt')}"/>&nbsp;</span>
            </div>
            <div class="span10">
                <div class="row-fluid">
                    <div class="searchElement span11">
                        <div class="select-search span2">
                            <select class="chzn-select" id="basicSearchModulesList" style="width:150px;">
                                <option value="" class="globalSearch_module_All">{vtranslate('LBL_ALL_RECORDS', $MODULE_NAME)}</option>
                                {foreach key=MODULE_NAME item=fieldObject from=$SEARCHABLE_MODULES}
                                    {if isset($SEARCHED_MODULE) && $SEARCHED_MODULE eq $MODULE_NAME && $SEARCHED_MODULE !== 'All'}
                                        <option value="{$MODULE_NAME}" class="globalSearch_module_{$MODULE_NAME}" selected>{vtranslate($MODULE_NAME,$MODULE_NAME)}</option>
                                    {else}
                                        <option value="{$MODULE_NAME}" class="globalSearch_module_{$MODULE_NAME}">{vtranslate($MODULE_NAME,$MODULE_NAME)}</option>
                                    {/if}
                                {/foreach}
                            </select>
                        </div>
                        <div class="input-append searchBar span5">
                            <input type="text" class="" id="globalSearchValue" placeholder="{vtranslate('LBL_GLOBAL_SEARCH')}" results="10" />
                            <span id="searchIcon" class="add-on search-icon"><i class="icon-white icon-search "></i></span>
                            <span class="adv-search  pull-left">
                                <a class="alignMiddle" id="globalSearch">{vtranslate('LBL_ADVANCE_SEARCH')}</a>
                            </span>
                        </div>
                    </div>
                    <div class="nav quickActions btn-toolbar span marginLeftZero">
                        <div class="pull-right commonActionsButtonContainer">
                            {if !empty($announcement)}
                                <div class="btn-group cursorPointer">
                                    <img class='alignMiddle' src="{vimage_path('btnAnnounceOff.png')}" alt="{vtranslate('LBL_ANNOUNCEMENT',$MODULE)}" title="{vtranslate('LBL_ANNOUNCEMENT',$MODULE)}" id="announcementBtn" />
                                </div>&nbsp;
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
			{assign var="BREADCRUMBS" value=$MENU_STRUCTURE['breadcrumbs']}
			{if $BREADCRUMBS}
				<div class="breadcrumbsContainer span12" style="margin-left: 10px;">
					<style>
					.mainContainer{
						margin-top: {if !empty($announcement)} 135px {else} 110px {/if}!important;
					}
					.commonActionsContainer .actionsContainer{
						height: 70px !important;
					}
					.breadcrumbsIcon{
						display: inline-block;
						line-height: 10px;
						background-image: url("layouts/vlayout/skins/images/breadcrumbs.png");
						width: 20px;
						height: 18px;
						background-position: 0px 0px;
						margin-right: 5px;
					}
					.breadcrumbsIcon.goBack{
						background-position: -22px 0px;
					}
					.breadcrumbsIcon.mainPage{
						background-position: -42px 0px;
						width: 13px;
					}
					.breadcrumbsLinks{
						display: inline;
						position: relative;
						top: -5px;
						font-size: 14px;
					}
					@media(max-width: 960px){
						.mainContainer{
							margin-top: {if !empty($announcement)} 150px {else} 125px {/if}!important;
						}
						.commonActionsContainer .actionsContainer{
							height: 94px !important;
						}
					}
					@media(max-width: 668px){
						.mainContainer{
							margin-top: {if !empty($announcement)} 150px {else} 136px {/if}!important;
						}
						.commonActionsContainer .actionsContainer{
							height: 107px !important;
						}
					}
					</style>
					<div class="btn-group" style="margin: 0;padding-left: 7px;display: inline-block;">
						<a class="breadcrumbsIcon showHistory popoverTooltip" class="dropdown-toggle" data-content="{vtranslate('LBL_PAGES_HISTORY')}" data-placement="right" data-toggle="dropdown" aria-expanded="false" ></a>
					</div>
					<a class="breadcrumbsIcon goBack popoverTooltip" data-content="{vtranslate('LBL_GO_BACK')}" data-placement="right" href="#"></a>
					<a class="breadcrumbsIcon mainPage popoverTooltip" href="index.php" data-content="{vtranslate('LBL_MAIN_PAGE')}" data-placement="right"></a>
					<div class="breadcrumbsLinks">
					{foreach key=key item=item from=$BREADCRUMBS}
						{if $key != 0}
							<span class="separator">&nbsp;{vglobal('breadcrumbs_separator')}&nbsp;</span>
						{/if}
						{if $item.url}
							<span><a {if $item.class}class="{$item.class}" {/if}href="{$item.url}">{$item.lable}</a></span>
						{else}
							<span {if $item.class}class="{$item.class}"{/if}>{$item.lable}</span>
						{/if}
					{/foreach}
					</div>
				</div>
			{/if}
			{assign var="MENUSCOLOR" value=Users_Colors_Model::getModulesColors(true)}
			{if $MENUSCOLOR}
				<div class="menusColorContainer" style="display: none;">
					<style>
					{foreach item=item from=$MENUSCOLOR}
						.moduleColor_{$item.module}{
							color: {$item.color} !important;
						}
						.moduleIcon{$item.module}{
							background: {$item.color} !important;
						}
					{/foreach}
					</style>
				</div>
			{/if}
        </div>
    </div>
{/strip}
