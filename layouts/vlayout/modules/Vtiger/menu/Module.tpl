{if vtlib_isModuleActive($MENU['mod']) AND ($PRIVILEGESMODEL->isAdminUser() || $PRIVILEGESMODEL->hasGlobalReadPermission() || $PRIVILEGESMODEL->hasModulePermission($MENU['tabid']) ) }
	<li class="menuModule .moduleColor_{$MENU['mod']} {$CLASS} {if !$HASPOPUP}hasParentMenu{/if}" data-id="{$MENU['id']}" role="menuitem" tabindex="{$TABINDEX}" {if $HASPOPUP}aria-haspopup="{$HASPOPUP}"{/if}>
		<a {if $MENU['hotkey']}class="hotKey" data-hotkeys="{$MENU['hotkey']}"{/if} href="{$MENU['dataurl']}" {if $MENU['newwindow'] eq 1}target="_blank" {/if}>{Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU['mod'])}</a>
		{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE}
	</li>
{/if}
