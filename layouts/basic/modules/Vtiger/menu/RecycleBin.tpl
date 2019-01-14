{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{if $PRIVILEGESMODEL->isAdminUser() || $PRIVILEGESMODEL->hasGlobalReadPermission() || $PRIVILEGESMODEL->hasModulePermission($MENU.tabid)}
		{if $MODULE eq 'RecycleBin'}
			{assign var=ACTIVE value='true'}
		{else}
			{assign var=ACTIVE value='false'}
		{/if}
		<li class="tpl-menu-HomeIcon c-menu__item js-menu__item nav-item menuHomeIcon hasParentMenu"
			data-id="{$MENU['id']}">
			<a class="nav-link {if $ACTIVE=='true'} active{else} collapsed{/if} hasIcon"
			   href="{$RECYCLE_BIN_MODULE_MODEL->getDefaultUrl()}">
				<span class="fa-lg fa-fw fas fa-trash-alt c-menu__item__icon"></span>
				<span class="c-menu__item__text js-menu__item__text"
					  title="{Vtiger_Menu_Model::vtranslateMenu('MEN_RECYCLE_BIN', $MENU_MODULE)}"
					  data-js="class: u-white-space-n">{Vtiger_Menu_Model::vtranslateMenu('MEN_RECYCLE_BIN', $MENU_MODULE)}</span>
			</a>
		</li>
	{/if}
{/strip}
