{strip}
{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
	{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon($MENU, Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE))}
	<li class="hovernav menuLabel {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU['id']}" role="menuitem" tabindex="{$TABINDEX}" {if $HASCHILDS == 'true'}aria-haspopup="{$HASCHILDS}"{/if}>
		<a class="{if (isset($MENU['active']) && $MENU['active']) || $PARENT_MODULE == $MENU['id']}active {/if}{if $ICON}hasIcon{/if}" {if $HASCHILDS == 'true'}role="button"{/if} href="#">
			{if $ICON}
				<div  {if $DEVICE == 'Desktop'}class='iconContainer'{/if}>
					<div {if $DEVICE == 'Desktop'}class="iconImage" {/if}>{$ICON}</div>
				</div>
			{/if}
			<div {if $DEVICE == 'Desktop'}class='labelConstainer'{/if}>
				<div {if $DEVICE == 'Desktop'}class="labelValue" {/if}>
				    	<span class="menuName">{Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE)}</span>
				</div>
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
