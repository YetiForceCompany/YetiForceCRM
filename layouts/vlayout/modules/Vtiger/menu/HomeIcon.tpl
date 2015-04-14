{if $PRIVILEGESMODEL->isAdminUser() || $PRIVILEGESMODEL->hasGlobalReadPermission() || $PRIVILEGESMODEL->hasModulePermission($MENU.tabid) }
	<li role="menuitem" tabindex="{$TABINDEX}" aria-haspopup="{$HASPOPUP}">
		<a class="{if $MODULE eq 'Home'} selected {/if}" href="{$HOME_MODULE_MODEL->getDefaultUrl()}"><img src="{vimage_path('home.png')}" alt="{vtranslate('LBL_HOME',$moduleName)}" title="{vtranslate('LBL_HOME',$moduleName)}" /></a>
		{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE}
	</li>
{/if}
