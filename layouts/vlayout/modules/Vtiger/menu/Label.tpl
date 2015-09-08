{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<li class="hovernav menuLabel {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU['id']}" role="menuitem" tabindex="{$TABINDEX}" 
		{if $HASCHILDS == 'true'}aria-haspopup="{$HASCHILDS}"{/if}>
		<a class="{if $MENU['active'] || $PARENT_MODULE == $MENU['id']}active {/if}{if array_key_exists("glyphicon",$MENU)}hasIcon{/if}" {if $CHILDS|@count neq 0}role="button"{/if} href="#">
			{if array_key_exists("glyphicon",$MENU)}
				<span class="glyphicon {$MENU['glyphicon']}" aria-hidden="true"></span>
			{/if}
			<span class="menuName">{Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE)}</span>
		</a>
		{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE}
	</li>
{/strip}
