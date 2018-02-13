{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{if $MOREMENU neq true && ($PRIVILEGESMODEL->isAdminUser() || $PRIVILEGESMODEL->hasGlobalReadPermission() || $PRIVILEGESMODEL->hasModulePermission($MENU.tabid)) }
		<li class="{if $DEVICE == 'Desktop'}menuHomeIcon{else} menuLabel {/if} {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU['id']}" role="menuitem" tabindex="{$TABINDEX}" {if $HASCHILDS}aria-haspopup="{$HASCHILDS}"{/if}>
			<a class="{if $MODULE eq 'Home'} selected {/if} hasIcon" href="{$HOME_MODULE_MODEL->getDefaultUrl()}">
				<div  {if $DEVICE == 'Desktop'}class='iconContainer'{/if}>
					<div {if $DEVICE == 'Desktop'}class="iconImage" {/if}>
						<span class="menuIcon userIcon-Home" aria-hidden="true"></span>
					</div>
				</div>
				<div class='{if $DEVICE == 'Desktop'}iconContainer{/if}'>
					<span {if $DEVICE == 'Desktop'}class="iconImage" {/if}>
						{\App\Language::translate('LBL_HOME',$MENU_MODULE)}
					</span>
				</div>


			</a>
			{include file=\App\Layout::getTemplatePath('menu/SubMenu.tpl', $MODULE)}
		</li>
	{/if}
{/strip}
