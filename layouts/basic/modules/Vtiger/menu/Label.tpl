{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon($MENU, Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE))}
	<li class="tpl-menu-Label menuLabel {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU['id']}" role="presentation">
		<a class="{if (isset($MENU['active']) && $MENU['active']) || $PARENT_MODULE == $MENU['id']}active {/if}{if $ICON}hasIcon{/if}" 
			{if $HASCHILDS == 'true'}role="button"{/if} href="#" aria-haspopup="{$HASCHILDS}">
			{$ICON}
			<span class="menuName">{Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE)}</span>
		</a>
		{include file=\App\Layout::getTemplatePath('menu/SubMenu.tpl', $MODULE)}
	</li>
{/strip}
