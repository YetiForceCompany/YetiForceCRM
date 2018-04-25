{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon($MENU, Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE))}	
	<li class="tpl-menu-Script nav-item menuScript {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU['id']}">
		<a class="nav-link {if $HASCHILDS=='true'}collapsed{/if}{if $ICON} hasIcon{/if}{if isset($MENU['hotkey'])} hotKey{/if}{if $HASCHILDS == 'true'} js-submenu-toggler{/if}"{if isset($MENU['hotkey'])} data-hotkeys="{$MENU['hotkey']}"{/if}
			{if $HASCHILDS == 'true'} data-toggle="collapse" data-target="#submenu-{$MENU['id']}" role="button"{/if}
			href="{$MENU['dataurl']}" 
			{if $HASCHILDS == 'true'} aria-haspopup="true" aria-expanded="false" aria-controls="submenu-{$MENU['id']}"{/if}>
			{$ICON}
			<span class="menuName">
				{\App\Language::translate($MENU['name'],'Menu')}
			</span>
			{if $HASCHILDS == 'true'}<span class="toggler" aria-hidden="true"><span class="fas fa-plus-circle"></span><span class="fas fa-minus-circle"></span></span>{/if}
		</a>
		{include file=\App\Layout::getTemplatePath('menu/SubMenu.tpl', $MODULE)}
	</li>
{/strip}
