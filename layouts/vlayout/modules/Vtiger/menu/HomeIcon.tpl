{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon('home.png', vtranslate('LBL_HOME',$moduleName))}
	{if $MOREMENU neq true && ($PRIVILEGESMODEL->isAdminUser() || $PRIVILEGESMODEL->hasGlobalReadPermission() || $PRIVILEGESMODEL->hasModulePermission($MENU.tabid)) }
		<li class="menuHomeIcon {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU['id']}" role="menuitem" tabindex="{$TABINDEX}" {if $HASCHILDS}aria-haspopup="{$HASCHILDS}"{/if}>
			<a class="{if $MODULE eq 'Home'} selected {/if} {if $ICON}hasIcon{/if}" href="{$HOME_MODULE_MODEL->getDefaultUrl()}">
				{$ICON}
				{vtranslate('LBL_HOME',$moduleName)}
			</a>
			{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE}
		</li>
	{/if}
{/strip}
