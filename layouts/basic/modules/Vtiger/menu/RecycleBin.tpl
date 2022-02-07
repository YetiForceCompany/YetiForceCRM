{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Menu-RecycleBin -->
	{assign var=RECYCLE_BIN_MODEL value=Vtiger_Module_Model::getInstance('RecycleBin')}
	{if $MODULE eq 'RecycleBin'}
		{assign var=ACTIVE value=true}
	{else}
		{assign var=ACTIVE value=false}
	{/if}
	<li class="c-menu__item js-menu__item nav-item menuHomeIcon hasParentMenu"
		data-id="{$MENU['id']}">
		<a class="nav-link {if $ACTIVE} active{else} collapsed{/if} hasIcon"
			href="{$RECYCLE_BIN_MODEL->getDefaultUrl()}">
			<span class="fa-lg fa-fw fas fa-trash-alt c-menu__item__icon"></span>
			<span class="c-menu__item__text js-menu__item__text" title="{$MENU['name']}"
				data-js="class: u-white-space-n">{$MENU['name']}</span>
		</a>
	</li>
	<!-- /tpl-Base-Menu-RecycleBin -->
{/strip}
