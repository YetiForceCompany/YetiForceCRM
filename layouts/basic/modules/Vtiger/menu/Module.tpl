{strip}
{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
	{if \App\Module::isModuleActive($MENU['mod']) AND ($PRIVILEGESMODEL->isAdminUser() || $PRIVILEGESMODEL->hasGlobalReadPermission() || $PRIVILEGESMODEL->hasModulePermission($MENU['tabid']) ) }
		{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon($MENU, Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU['mod']))}
		<li class="menuModule moduleColor_{$MENU['mod']} {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU['id']}" role="menuitem" tabindex="{$TABINDEX}" {if $HASCHILDS}aria-haspopup="{$HASCHILDS}"{/if}>
			<a class="{if $MENU['name'] == $MODULE}active{/if} {if $ICON}hasIcon{/if} {if isset($MENU['hotkey'])}hotKey{/if}" {if isset($MENU['hotkey'])}data-hotkeys="{$MENU['hotkey']}"{/if} href="{$MENU['dataurl']}" 
				{if $MENU['newwindow'] eq 1}target="_blank" {/if}>
				{$ICON}
				<span class="menuName">{Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU['mod'])}</span>
			</a>
			{if $DEVICE == 'Desktop'}
				{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE DEVICE=$DEVICE}
			{/if}
		</li>
		{if $DEVICE == 'Mobile'}
			{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE DEVICE=$DEVICE}
		{/if}
	{/if}
{/strip}
