<li class="menuShortcut {$CLASS}" role="menuitem" tabindex="{$TABINDEX}" aria-haspopup="{$HASPOPUP}">
	<a {if $MENU.hotkey}class="hotKey" data-hotkeys="{$MENU.hotkey}"{/if} href="{$MENU.dataurl}" {if $MENU.newwindow eq 1}target="_blank" {/if}>{vtranslate($MENU.name,'Menu')}</a>
	{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE}
</li>