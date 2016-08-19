{strip}
{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
	{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon($MENU, Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE))}
	<li class="menuShortcut {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU.id}" role="menuitem" tabindex="{$TABINDEX}" {if $HASCHILDS}aria-haspopup="{$HASCHILDS}"{/if}>
		<a class="{if isset($MENU['hotkey'])}hotKey{/if} {if $MENU['active']}active{/if}{if $ICON} hasIcon{/if}" {if isset($MENU['hotkey'])} data-hotkeys="{$MENU['hotkey']}"{/if} href="{$MENU['dataurl']}" {if $MENU.newwindow eq 1 OR $MENU['name'] eq 'LBL_YETIFORCE_SHOP'}target="_blank" {/if}>
			{$ICON}
			<span class="menuName">
				{Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE)}
			</span>
		</a>
		{if $DEVICE == 'Desktop'}
			{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE DEVICE=$DEVICE}
		{/if}
	</li>
	{if $DEVICE == 'Desktop'}
		{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE DEVICE=$DEVICE}
	{/if}
{/strip}

