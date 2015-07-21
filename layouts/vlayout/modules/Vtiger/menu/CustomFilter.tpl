{if vtlib_isModuleActive($MENU.mod) AND ($PRIVILEGESMODEL->isAdminUser() || $PRIVILEGESMODEL->hasGlobalReadPermission() || $PRIVILEGESMODEL->hasModulePermission($MENU.tabid) ) }
	<li class="menuCustomFilter .moduleColor_{$MENU.mod} {$CLASS}" data-id="{$MENU.id}" role="menuitem" tabindex="{$TABINDEX}" aria-haspopup="{$HASPOPUP}">
		<a {if $MENU.hotkey}class="hotKey" data-hotkeys="{$MENU.hotkey}"{/if} href="{$MENU.dataurl}" {if $MENU.newwindow eq 1}target="_blank" {/if}>{$MENU.name}</a>
		{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE}
	</li>
{/if}
