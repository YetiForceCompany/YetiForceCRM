<li class="menuLabel {$CLASS}" role="menuitem" tabindex="{$TABINDEX}" aria-haspopup="{$HASPOPUP}">
	<a href="#">{Vtiger_Menu_Model::vtranslateMenu($MENU.name,'Menu')}</a>
	{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE}
</li>
