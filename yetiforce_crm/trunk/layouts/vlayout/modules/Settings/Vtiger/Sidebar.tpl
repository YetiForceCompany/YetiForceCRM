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
<div class="sidebarTitleBlock">
	<h3 class="titlePadding themeTextColor unSelectedQuickLink cursorPointer"><a href="index.php?module=Vtiger&parent=Settings&view=Index">{vtranslate('LBL_SETTINGS', $QUALIFIED_MODULE)}</a></h3>
</div>
<!--div>
	<input class='input-medium' type='text' name='settingsSearch' placeholder={vtranslate("LBL_SEARCH_SETTINGS_PLACEHOLDER", $QUALIFIED_MODULE)} >
</div-->
<div class="quickWidgetContainer accordion" id="settingsQuickWidgetContainer">
	{foreach item=MENU from=$SETTINGS_MENUS}
		<div class="quickWidget">
			<div class="accordion-heading accordion-toggle quickWidgetHeader" data-parent="#settingsQuickWidgetContainer" data-target="#Settings_sideBar_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($MENU->getLabel())}"
				data-toggle="collapse" data-parent="#quickWidgets">
				<span class="pull-left"><img class="imageElement" data-rightimage="{vimage_path('rightArrowWhite.png')}" data-downimage="{vimage_path('downArrowWhite.png')}" src="{if $SELECTED_MENU->get('blockid') eq $MENU->get('blockid') && !empty($SELECTED_FIELDID) }{vimage_path('downArrowWhite.png')}{else}{vimage_path('rightArrowWhite.png')}{/if}" /></span>
				<h5 class="title paddingLeft10px widgetTextOverflowEllipsis" title="{vtranslate($MENU->getLabel(), $QUALIFIED_MODULE)}">{vtranslate($MENU->getLabel(), $QUALIFIED_MODULE)}</h5>
				<div class="clearfix"></div>
			</div>
			<div  style="border-bottom: 1px solid black;" class="widgetContainer accordion-body {if $SELECTED_MENU->get('blockid') eq $MENU->get('blockid')  && !empty($SELECTED_FIELDID)} in {/if} collapse" id="Settings_sideBar_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($MENU->getLabel())}">
			{foreach item=MENUITEM from=$MENU->getMenuItems()}
				<div class="{if $MENUITEM->getId() eq $SELECTED_FIELDID} selectedMenuItem selectedListItem{/if}" style="padding:7px;border-top:0px;">
					<div class="row-fluid menuItem"  data-actionurl="{$MENUITEM->getPinUnpinActionUrl()}">
						<a href="{$MENUITEM->getUrl()}" data-id="{$MENUITEM->getId()}" class="span9 menuItemLabel" data-menu-item="true" >{vtranslate($MENUITEM->get('name'), $QUALIFIED_MODULE)}</a>
						<span class="span1">&nbsp;</span>
						<img style="margin-right: 6%" id="{$MENUITEM->getId()}_menuItem" data-pintitle="{vtranslate('LBL_PIN',$QUALIFIED_MODULE)}" data-unpintitle="{vtranslate('LBL_UNPIN',$QUALIFIED_MODULE)}" data-id="{$MENUITEM->getId()}" class="pinUnpinShortCut cursorPointer hide pull-right" data-unpinimageurl="{{vimage_path('unpin.png')}}"  data-pinimageurl="{{vimage_path('pin.png')}}" {if $MENUITEM->isPinned()} title="{vtranslate('LBL_UNPIN',$QUALIFIED_MODULE)}" src="{vimage_path('unpin.png')}" data-action="unpin" {else} title="{vtranslate('LBL_PIN',$QUALIFIED_MODULE)}" src="{vimage_path('pin.png')}" data-action="pin" {/if} />
						<div class="clearfix"></div>
					</div>
				</div>
			{/foreach}
			</div>
		</div>
	{/foreach}
</div>
{/strip}
