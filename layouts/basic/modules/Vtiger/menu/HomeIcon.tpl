{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{if $MOREMENU neq true && ($PRIVILEGESMODEL->isAdminUser() || $PRIVILEGESMODEL->hasGlobalReadPermission() || $PRIVILEGESMODEL->hasModulePermission($MENU.tabid)) }
		<li class="tpl-menu-HomeIcon menuHomeIcon {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU['id']}" role="presentation">
			<a class="{if $MODULE eq 'Home'} selected {/if} hasIcon" href="{$HOME_MODULE_MODEL->getDefaultUrl()}"
				role="menuitem" aria-haspopup="{$HASCHILDS}">
				<span class="menuIcon userIcon-Home" aria-hidden="true"></span>
				<span class="menuName">{\App\Language::translate('LBL_HOME',$MENU_MODULE)}</span>
			</a>
			{include file=\App\Layout::getTemplatePath('menu/SubMenu.tpl', $MODULE)}
		</li>
	{/if}
{/strip}
