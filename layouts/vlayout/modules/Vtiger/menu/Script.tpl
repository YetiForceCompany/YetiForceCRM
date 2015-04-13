<li class="menuScript {$CLASS}" role="menuitem" tabindex="{$TABINDEX}" aria-haspopup="{$HASPOPUP}">
	<a {if $MENU.hotkey}class="hotKey" data-hotkeys="{$MENU.hotkey}"{/if} href="{$MENU.dataurl}">{vtranslate($MENU.name,'Menu')}</a>
	{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE}
</li>