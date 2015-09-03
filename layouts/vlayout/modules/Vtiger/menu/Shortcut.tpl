{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<li class="menuShortcut {$CLASS} {if !$HASPOPUP}hasParentMenu{/if}" data-id="{$MENU.id}" role="menuitem" tabindex="{$TABINDEX}" {if $HASPOPUP}aria-haspopup="{$HASPOPUP}"{/if}>
		<a {if $MENU.hotkey}class="hotKey" data-hotkeys="{$MENU.hotkey}"{/if} href="{$MENU.dataurl}" {if $MENU.newwindow eq 1}target="_blank" {/if}>{vtranslate($MENU.name,'Menu')}</a>
		{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE}
	</li>
{/strip}
