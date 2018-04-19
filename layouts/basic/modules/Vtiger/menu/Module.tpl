{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{if \App\Module::isModuleActive($MENU['mod']) AND ($PRIVILEGESMODEL->isAdminUser() || $PRIVILEGESMODEL->hasGlobalReadPermission() || $PRIVILEGESMODEL->hasModulePermission($MENU['tabid']) ) }
		{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon($MENU, Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU['mod']))}
		<li class="tpl-menu-Module nav-item modCT_{$MENU['mod']} {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU['id']}" role="presentation">
			<a class="{if $MENU['name'] == $MODULE}active{/if} {if $ICON}hasIcon{/if} {if isset($MENU['hotkey'])}hotKey{/if}" {if isset($MENU['hotkey'])}data-hotkeys="{$MENU['hotkey']}"{/if}
				role="menuitem" href="{$MENU['dataurl']}" aria-haspopup="{$HASCHILDS}" {if $MENU['newwindow'] eq 1}target="_blank" {/if}>
				{$ICON}
				<span class="menuName">{Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU['mod'])}</span>
			</a>
			{include file=\App\Layout::getTemplatePath('menu/SubMenu.tpl', $MODULE)}
		</li>
	{/if}
{/strip}
