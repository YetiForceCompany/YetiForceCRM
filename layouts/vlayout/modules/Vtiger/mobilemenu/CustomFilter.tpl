{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{if vtlib_isModuleActive($MENU.mod) AND ($PRIVILEGESMODEL->isAdminUser() || $PRIVILEGESMODEL->hasGlobalReadPermission() || $PRIVILEGESMODEL->hasModulePermission($MENU['tabid']) ) }
		<li class="menuCustomFilter .moduleColor_{$MENU.mod}" data-id="{$MENU['id']}" role="menuitem" tabindex="{$TABINDEX}" aria-haspopup="{$HASCHILDS}">
			<a {if $MENU['hotkey']}class="hotKey" data-hotkeys="{$MENU['hotkey']}"{/if} href="{$MENU['dataurl']}" {if $MENU['newwindow'] eq 1}target="_blank" {/if}>
				<span class="menuName">{Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE)}</span>
			</a>
			{include file='mobilemenu/SubMenu.tpl'|@vtemplate_path:$MODULE}
		</li>
	{/if}
{/strip}
