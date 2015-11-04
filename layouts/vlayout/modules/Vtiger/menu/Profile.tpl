{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{if SysSecurity::get('CHANGE_LOGIN_PASSWORD')}
		{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon($MENU, Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE))}
		<li class="menuLabel {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU['id']}" role="menuitem" tabindex="{$TABINDEX}" 
			{if $HASCHILDS == 'true'}aria-haspopup="{$HASCHILDS}"{/if}>
			<a class="{if $MENU['active'] || $PARENT_MODULE == $MENU['id']}active {/if}{if $ICON}hasIcon{/if}" {if $CHILDS|@count neq 0}role="button"{/if} href="#">
				{$ICON}
				<span class="menuName">{Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE)}</span>
			</a>
			<ul class="nav subMenu {if $MENU['active'] || $PARENT_MODULE == $MENU['id']}in{/if}" role="menu" aria-hidden="true">
				<li class="menuPanel">
					<div class="panel panel-primary">
						<div class="panel-heading">{Vtiger_Menu_Model::vtranslateMenu('LBL_CHANGE_LOGIN_PASSWORD',$MENU_MODULE)}</div>
						<div class="panel-body">
							<div class="">
								<button class="btn btn-default btn-block btn-success showModalWindow"  name="changePass" data-url="index.php?module=Users&view=ChangePassword" type="submit">{Vtiger_Menu_Model::vtranslateMenu('LBL_CHANGE',$MENU_MODULE)}</button>
							</div>
						</div>
					</div>
				</li>	
			</ul>
		</li>
	{/if}
{/strip}
