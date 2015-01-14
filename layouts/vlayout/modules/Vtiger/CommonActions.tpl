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
            <div class="span2">
                <span class="companyLogo"><img src="{$COMPANY_LOGO->get('imagepath')}" title="{$COMPANY_LOGO->get('title')}" alt="{$COMPANY_LOGO->get('alt')}"/>&nbsp;</span>
            </div>
            <div class="span10">
                <div class="row-fluid">
                    <div class="searchElement span8">
                        <div class="select-search">
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
                        <div class="input-append searchBar">
                            <input type="text" class="" id="globalSearchValue" placeholder="{vtranslate('LBL_GLOBAL_SEARCH')}" results="10" />
                            <span id="searchIcon" class="add-on search-icon"><i class="icon-white icon-search "></i></span>
                            <span class="adv-search  pull-left">
                                <a class="alignMiddle" id="globalSearch">{vtranslate('LBL_ADVANCE_SEARCH')}</a>
                            </span>
                        </div>

                    </div>
                    <div class="notificationMessageHolder span2">

                    </div>
                    <div class="nav quickActions btn-toolbar span2 pull-right marginLeftZero">
                        <div class="pull-right commonActionsButtonContainer">
                            {if !empty($announcement)}
                                <div class="btn-group cursorPointer">
                                    <img class='alignMiddle' src="{vimage_path('btnAnnounceOff.png')}" alt="{vtranslate('LBL_ANNOUNCEMENT',$MODULE)}" title="{vtranslate('LBL_ANNOUNCEMENT',$MODULE)}" id="announcementBtn" />
                                </div>&nbsp;
                            {/if}

                            <div class="btn-group cursorPointer" id="guiderHandler">
                                {if !$MAIN_PRODUCT_WHITELABEL}
                                {/if}
                            </div>&nbsp;

                            <div class="btn-group cursorPointer">
                                <img id="menubar_quickCreate" src="{vimage_path('btnAdd.png')}" class="alignMiddle" alt="{vtranslate('LBL_QUICK_CREATE',$MODULE)}" title="{vtranslate('LBL_QUICK_CREATE',$MODULE)}" data-toggle="dropdown" />
                                <ul class="dropdown-menu dropdownStyles commonActionsButtonDropDown">
                                    <li class="title"><strong>{vtranslate('LBL_QUICK_CREATE',$MODULE)}</strong></li><hr/>
                                    <li id="quickCreateModules">
                                        <div class="row-fluid">
                                            <div class="span12">
                                                {foreach key=moduleName item=moduleModel from=$MENUS}
                                                    {if $moduleModel->isPermitted('EditView')}
                                                        {assign var='quickCreateModule' value=$moduleModel->isQuickCreateSupported()}
                                                        {assign var='singularLabel' value=$moduleModel->getSingularLabelKey()}
														{if $singularLabel == 'SINGLE_Calendar'}
															{assign var='singularLabel' value='LBL_EVENT_OR_TASK'}
														{/if}	
                                                        {if $quickCreateModule == '1'}
                                                            {if $count % 3 == 0}
                                                                <div class="row-fluid">
                                                                {/if}
                                                                <div class="span4">
                                                                    <a id="menubar_quickCreate_{$moduleModel->getName()}" class="quickCreateModule" data-name="{$moduleModel->getName()}"
                                                                       data-url="{$moduleModel->getQuickCreateUrl()}" href="javascript:void(0)" title="{vtranslate($singularLabel,$moduleName)}">{vtranslate($singularLabel,$moduleName)}</a>
                                                                </div>
                                                                {if $count % 3 == 2}
                                                                </div>
                                                            {/if}
                                                            {assign var='count' value=$count+1}
                                                        {/if}
                                                    {/if}
                                                {/foreach}
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>&nbsp;
                        </div>
                    </div>
                </div>
            </div>
			{assign var="BREADCRUMBS" value=$MENU_STRUCTURE['breadcrumbs']}
			{assign var="MENUSCOLOR" value=$MENU_STRUCTURE['menusColor']}
			{if $BREADCRUMBS}
				<div class="breadcrumbsContainer span12" style="margin-left: 20px;">
					<style>
					.mainContainer{
						margin-top: {if !empty($announcement)} 135px {else} 110px {/if}!important;
					}
					.commonActionsContainer .actionsContainer{
						height: 70px !important;
					}
					</style>
					<span><a href="index.php"><i class="icon-home"></i></a></span>
					{foreach item=item from=$BREADCRUMBS}
						<span>&nbsp;{vglobal('breadcrumbs_separator')}&nbsp;</span>
						{if $item.url}
							<span><a {if $item.class}class="{$item.class}" {/if}href="{$item.url}">{$item.lable}</a></span>
						{else}
							<span {if $item.class}class="{$item.class}"{/if}>{$item.lable}</span>
						{/if}
					{/foreach}
				</div>
			{/if}
			{if $MENUSCOLOR}
				<div class="menusColorContainer" style="display: none;">
					<style>
					{foreach item=item from=$MENUSCOLOR}
						.{$item.class}{
							color: {$item.color} !important;
						}
					{/foreach}
					</style>
				</div>
			{/if}
        </div>
    </div>
{/strip}
