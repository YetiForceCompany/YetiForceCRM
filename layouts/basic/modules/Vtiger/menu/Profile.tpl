{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{if App\Config::security('CHANGE_LOGIN_PASSWORD')}
		{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon($MENU, $MENU['name'])}
		<li class="tpl-menu-Profile c-menu__item js-menu__item nav-item menuLabel {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU['id']}" data-js="mouseenter mouseleave">
			<a class="nav-link collapsed{if $ICON} hasIcon{/if} js-submenu-toggler is-submenu-toggler"
				data-toggle="collapse" data-target="#submenu-{$MENU['id']}" role="button" href="#" aria-haspopup="true" aria-expanded="false" aria-controls="submenu-{$MENU['id']}">
				{$ICON}
				<span class="c-menu__item__text js-menu__item__text" title="{$MENU['name']}" data-js="class: u-white-space-n">{$MENU['name']}</span>
				<span class="toggler" aria-hidden="true"><span class="fas fa-plus-circle"></span><span class="fas fa-minus-circle"></span></span>
			</a>
			<div class="tpl-menu-Profile_submenu js-submenu collapse" id="submenu-{$MENU['id']}" data-js="bootstrap:collapse" data-parent="#submenu-{$MENU['id']}">
				<ul class="nav flex-column">
					<li class="menuPanel nav-item">
						<button name="changePass" data-url="index.php?module=Users&view=PasswordModal&mode=change&record={$USER_MODEL->getRealId()}"
							class=" btn btn-block btn-light showModal" type="button" title="{Vtiger_Menu_Model::vtranslateMenu('LBL_CHANGE_LOGIN_PASSWORD',$MENU_MODULE)}">
							{Vtiger_Menu_Model::vtranslateMenu('LBL_CHANGE_LOGIN_PASSWORD',$MENU_MODULE)}
						</button>
					</li>
				</ul>
			</div>
		</li>
	{/if}
{/strip}
