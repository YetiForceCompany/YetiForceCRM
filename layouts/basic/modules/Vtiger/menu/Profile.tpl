{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{if AppConfig::security('CHANGE_LOGIN_PASSWORD')}
		{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon($MENU, Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE))}
		<li class="menuLabel {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU['id']}" role="menuitem" tabindex="{$TABINDEX}" 
			{if $HASCHILDS == 'true'}aria-haspopup="{$HASCHILDS}"{/if}>
			<a class="{if $MENU['active'] || $PARENT_MODULE == $MENU['id']}active {/if}{if $ICON}hasIcon{/if}" {if $HASCHILDS == 'true'}role="button"{/if} href="#">
				{if $ICON}
					<div class="iconContainer">
						<div class="iconImage">{$ICON}</div>
					</div>
				{/if}
				<div class="labelConstainer">
					<div class="labelValue">
						<span class="menuName">{Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE)}</span>
					</div>
				</div>
			</a>
			
			<ul class="slimScrollSubMenu nav subMenu {if $MENU['active'] || $PARENT_MODULE == $MENU['id']}in{/if}" role="menu" aria-hidden="true">
				<li class="menuPanel">
					<button name="changePass" data-url="index.php?module=Users&view=PasswordModal&mode=change&record={$USER_MODEL->getRealId()}" 
							class=" btn btn-block btn-light showModal" type="button">
						{Vtiger_Menu_Model::vtranslateMenu('LBL_CHANGE_LOGIN_PASSWORD',$MENU_MODULE)}
					</button>
				</li>

			</ul>
		</li>
	{/if}
{/strip}
