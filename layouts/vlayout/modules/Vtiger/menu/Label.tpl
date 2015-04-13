<li class="menuLabel {$CLASS}" role="menuitem" tabindex="{$TABINDEX}" aria-haspopup="{$HASPOPUP}">
	<a href="#">{vtranslate($MENU.name,'Menu')}</a>
	{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE}
</li>