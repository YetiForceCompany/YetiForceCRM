{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon($MENU, Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE))}
	<li class="hovernav menuLabel {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU['id']}" role="menuitem" tabindex="{$TABINDEX}" 
		{if $HASCHILDS == 'true'}aria-haspopup="{$HASCHILDS}"{/if}>
		<a class="{if $MENU['active'] || $PARENT_MODULE == $MENU['id']}active {/if}{if $ICON}hasIcon{/if}" {if $CHILDS|@count neq 0}role="button"{/if} href="#">
			{if $ICON}
				<div class='' style='height:45px;display: table;'>
					<div style=' display: table-cell;vertical-align: middle;'>{$ICON}</div>
				</div>
			{/if}
			<div class='' >
				<span class="menuName">{Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE)}</span>
			</div>
		</a>
		{if $DEVICE == 'Desktop'}
			{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE DEVICE=$DEVICE}
		{/if}
	</li>
	{if $DEVICE == 'Mobile'}
		{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE DEVICE=$DEVICE}
	{/if}
{/strip}
