{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon($MENU, Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE))}	
	<li class="menuScript {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU['id']}" role="presentation">
		<a class="{if $ICON}hasIcon{/if} {if isset($MENU['hotkey'])}hotKey{/if}" {if isset($MENU['hotkey'])}data-hotkeys="{$MENU['hotkey']}"{/if} 
			role="menuitem" href="{$MENU['dataurl']}" aria-haspopup="{$HASCHILDS}">
			{$ICON}
			<span class="menuName">
				{\App\Language::translate($MENU['name'],'Menu')}
			</span>
		</a>
		{include file=\App\Layout::getTemplatePath('menu/SubMenu.tpl', $MODULE)}
	</li>
{/strip}
