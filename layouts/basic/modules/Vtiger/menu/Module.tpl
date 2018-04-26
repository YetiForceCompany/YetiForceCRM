{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{if \App\Module::isModuleActive($MENU['mod']) AND ($PRIVILEGESMODEL->isAdminUser() || $PRIVILEGESMODEL->hasGlobalReadPermission() || $PRIVILEGESMODEL->hasModulePermission($MENU['tabid']) ) }
		{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon($MENU, Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU['mod']))}
		{if $MENU['name'] == $MODULE}
			{assign var=ACTIVE value='true'}
		{else}
			{assign var=ACTIVE value='false'}
		{/if}
		<li class="tpl-menu-Module nav-item modCT_{$MENU['mod']} {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU['id']}">
			<a class="nav-link {if $ACTIVE=='true'} active{else} collapsed{/if}{if $ICON} hasIcon{/if}{if isset($MENU['hotkey'])} hotKey{/if}{if $HASCHILDS == 'true'} js-submenu-toggler{/if}"{if isset($MENU['hotkey'])} data-hotkeys="{$MENU['hotkey']}"{/if}
				{if $HASCHILDS == 'true'} data-toggle="collapse" data-target="#submenu-{$MENU['id']}" role="button"{/if}
				href="{$MENU['dataurl']}" 
				{if $HASCHILDS == 'true'} aria-haspopup="true" aria-expanded="{$ACTIVE}" aria-controls="submenu-{$MENU['id']}"{/if}
				{if $MENU['newwindow'] eq 1}target="_blank" {/if}>
				{$ICON}
				<span class="menuName">{Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU['mod'])}</span>
				{if $HASCHILDS == 'true'}<span class="toggler" aria-hidden="true"><span class="fas fa-plus-circle"></span><span class="fas fa-minus-circle"></span></span>{/if}
			</a>
			{include file=\App\Layout::getTemplatePath('menu/SubMenu.tpl', $MODULE)}
		</li>
	{/if}
{/strip}
