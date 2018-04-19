{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon($MENU, Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE))}
	<li class="menuShortcut {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU.id}" role="presentation">
		<a class="{if isset($MENU['hotkey'])}hotKey{/if} {if $MENU['active']}active{/if}{if $ICON} hasIcon{/if}" {if isset($MENU['hotkey'])} data-hotkeys="{$MENU['hotkey']}"{/if} 
			role="menuitem" href="{$MENU['dataurl']}" aria-haspopup="{$HASCHILDS}" {if $MENU.newwindow eq 1}target="_blank" {/if} rel="noreferrer">
			{$ICON}
			<span class="menuName">
				{Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE)}
			</span>
		</a>
		{include file=\App\Layout::getTemplatePath('menu/SubMenu.tpl', $MODULE)}
	</li>
{/strip}

