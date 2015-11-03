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
	<h4 class="titlePadding themeTextColor unSelectedQuickLink cursorPointer"><a href="index.php?module=Vtiger&parent=Settings&view=Index">{vtranslate('LBL_SETTINGS', $QUALIFIED_MODULE)}</a></h4>
</div>
<!--div>
	<input class='input-medium' type='text' name='settingsSearch' placeholder={vtranslate("LBL_SEARCH_SETTINGS_PLACEHOLDER", $QUALIFIED_MODULE)} >
</div-->		 
<div class="quickWidgetContainer panel-group" id="settingsQuickWidgetContainer">
	{foreach item=MENU from=$SETTINGS_MENUS}
		<div class="panel panel-default quickWidget">
			<div class="panel-heading quickWidgetHeader">
				<h5 class="panel-title" title="{vtranslate($MENU->getLabel(), $QUALIFIED_MODULE)}">
					<a data-toggle="collapse" data-parent="#settingsQuickWidgetContainer" href="#Settings_sideBar_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($MENU->getLabel())}">
						<span class="pull-left">
							<img class="imageElement" title="" alt="" data-rightimage="{vimage_path('rightArrowWhite.png')}" data-downimage="{vimage_path('downArrowWhite.png')}" src="{if $SELECTED_MENU->get('blockid') eq $MENU->get('blockid') && !empty($SELECTED_FIELDID) }{vimage_path('downArrowWhite.png')}{else}{vimage_path('rightArrowWhite.png')}{/if}" />&nbsp;
					   </span>
							{vtranslate($MENU->getLabel(), $QUALIFIED_MODULE)}
					</a>
				</h5>
			</div>
			<div id="Settings_sideBar_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($MENU->getLabel())}" class="panel-collapse collapse {if $SELECTED_MENU->get('blockid') eq $MENU->get('blockid')  && !empty($SELECTED_FIELDID)} in {/if} widgetContainer">
				<div class="list-group panel-body">
					{foreach item=MENUITEM from=$MENU->getMenuItems()}
						<div class="row">
							<div class="menuItem"  data-actionurl="{$MENUITEM->getPinUnpinActionUrl()}">
								<a href="{$MENUITEM->getUrl()}" data-id="{$MENUITEM->getId()}" class="menuItemLabel list-group-item {if $MENUITEM->getId() eq $SELECTED_FIELDID} active selectedListItem{/if} " data-menu-item="true" >{vtranslate($MENUITEM->get('name'), $MENUITEM->getModuleNameFromUrl($MENUITEM->getUrl()))}
									
								</a>
								<span>
									<img alt="" id="{$MENUITEM->getId()}_menuItem" data-pintitle="{vtranslate('LBL_PIN',$QUALIFIED_MODULE)}" data-unpintitle="{vtranslate('LBL_UNPIN',$QUALIFIED_MODULE)}" data-id="{$MENUITEM->getId()}" class="pinUnpinShortCut cursorPointer hide pull-right" data-unpinimageurl="{{vimage_path('unpin.png')}}"  data-pinimageurl="{{vimage_path('pin.png')}}" {if $MENUITEM->isPinned()} title="{vtranslate('LBL_UNPIN',$QUALIFIED_MODULE)}" src="{vimage_path('unpin.png')}" data-action="unpin" {else} title="{vtranslate('LBL_PIN',$QUALIFIED_MODULE)}" src="{vimage_path('pin.png')}" data-action="pin" {/if} />
								</span>
							</div>
						</div>
					{/foreach}
				</div>
			</div>
        </div>	
	{/foreach}
</div>	
{/strip}
