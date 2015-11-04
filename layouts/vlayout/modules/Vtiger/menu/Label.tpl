{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<li class="hovernav menuLabel {$CLASS} {if !$HASPOPUP}hasParentMenu{/if}" data-id="{$MENU.id}" role="menuitem" tabindex="{$TABINDEX}" 
		{if $HASPOPUP == 'true'}aria-haspopup="{$HASPOPUP}"{/if}>
		<a class="{if $PARENT_MODULE == $MENU.id}active{/if}" {if $CHILDS|@count neq 0}data-toggle="collapse" aria-expanded="false" role="button"{/if} href="#menu{$MENU.id}" 
		   aria-controls="menu{$MENU.id}">
			{Vtiger_Menu_Model::vtranslateMenu($MENU.name,'Menu')}
		</a>
		{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE}
	</li>
{/strip}
