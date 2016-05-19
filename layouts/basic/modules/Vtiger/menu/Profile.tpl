{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{if AppConfig::security('CHANGE_LOGIN_PASSWORD')}
		{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon($MENU, Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE))}
		<li class="menuLabel {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU['id']}" role="menuitem" tabindex="{$TABINDEX}" 
			{if $HASCHILDS == 'true'}aria-haspopup="{$HASCHILDS}"{/if}>
			<a class="{if $MENU['active'] || $PARENT_MODULE == $MENU['id']}active {/if}{if $ICON}hasIcon{/if}" {if $HASCHILDS == 'true'}role="button"{/if} href="#">
				{$ICON}
				<span class="menuName">{Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE)}</span>
			</a>
			{if $DEVICE == 'Desktop'}
				<ul class="nav subMenu {if $MENU['active'] || $PARENT_MODULE == $MENU['id']}in{/if}" role="menu" aria-hidden="true">
			{/if}
					<li class="menuPanel">
						<button name="changePass" data-url="index.php?module=Users&view=ChangePassword" 
							class=" btn btn-block btn-default showModalWindow" type='submit'>
								{Vtiger_Menu_Model::vtranslateMenu('LBL_CHANGE_LOGIN_PASSWORD',$MENU_MODULE)}
						</button>
					</li>
			{if $DEVICE == 'Desktop'}
				</ul>
			{/if}
		</li>
	{/if}
{/strip}
