{strip}
{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon($MENU, Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE))}	
<li class="menuScript {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU['id']}" role="menuitem" tabindex="{$TABINDEX}" {if $HASCHILDS}aria-haspopup="{$HASCHILDS}"{/if}>
		<a class="{if $ICON}hasIcon{/if} {if isset($MENU['hotkey'])}hotKey{/if}" {if isset($MENU['hotkey'])}data-hotkeys="{$MENU['hotkey']}"{/if} href="{$MENU['dataurl']}">
			{$ICON}
			<span class="menuName">
				{\App\Language::translate($MENU['name'],'Menu')}
			</span>
		</a>
		{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE}
	</li>
{/strip}
