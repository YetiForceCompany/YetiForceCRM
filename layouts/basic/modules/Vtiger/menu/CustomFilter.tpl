{strip}
{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
	{if \App\Module::isModuleActive($MENU.mod) AND ($PRIVILEGESMODEL->isAdminUser() || $PRIVILEGESMODEL->hasGlobalReadPermission() || $PRIVILEGESMODEL->hasModulePermission($MENU['tabid']) ) }
		{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon($MENU, Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE))}
		<li class="menuCustomFilter .moduleColor_{$MENU.mod}" data-id="{$MENU['id']}" role="menuitem" tabindex="{$TABINDEX}" aria-haspopup="{$HASCHILDS}">
			<a class="{if isset($MENU['hotkey'])}hotKey{/if} {if $ICON}hasIcon{/if}" {if isset($MENU['hotkey'])}data-hotkeys="{$MENU['hotkey']}"{/if} href="{$MENU['dataurl']}" {if $MENU['newwindow'] eq 1}target="_blank" {/if}>
				{if $ICON}
					<div  {if $DEVICE == 'Desktop'}class='iconContainer'{/if}>
						<div {if $DEVICE == 'Desktop'}class="iconImage" {/if}>{$ICON}</div>
					</div>
				{/if}
				<span class="menuName">{Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE)}</span>
			</a>
			{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE}
		</li>
	{/if}
{/strip}
