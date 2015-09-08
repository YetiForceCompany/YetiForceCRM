{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<li class="menuShortcut {$CLASS} {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU.id}" role="menuitem" tabindex="{$TABINDEX}" {if $HASCHILDS}aria-haspopup="{$HASCHILDS}"{/if}>
		<a class="{if $MENU.hotkey}hotKey{/if} {if $MENU['active']}active{/if}" {if $MENU.hotkey} data-hotkeys="{$MENU.hotkey}"{/if} href="{$MENU['dataurl']}" {if $MENU.newwindow eq 1}target="_blank" {/if}>
			<span class="menuName">
				{Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE)}
			</span>
		</a>
		{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE}
	</li>
{/strip}

