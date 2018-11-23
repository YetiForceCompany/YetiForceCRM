{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{assign var='MODULEMODEL' value=Vtiger_Module_Model::getInstance($MENU.tabid)}
	{assign var='QUICKCREATEMODULE' value=$MODULEMODEL->isQuickCreateSupported()}
	{assign var='SINGULAR_LABEL' value=$MODULEMODEL->getSingularLabelKey()}
	{assign var='NAME' value=$MODULEMODEL->getName()}
	{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon($MENU, Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE))}
	{if $QUICKCREATEMODULE == '1' && \App\Module::isModuleActive($NAME) && ($PRIVILEGESMODEL->isAdminUser() || $PRIVILEGESMODEL->hasGlobalWritePermission() || $PRIVILEGESMODEL->hasModuleActionPermission($MENU.tabid, 'CreateView') ) }
		{if $MENU.name != ''}
			{assign var='MENU_ITEM_TEXT' value=\App\Language::translate($MENU.name,'Menu')}
		{else}
			{assign var='MENU_ITEM_TEXT' value="{Vtiger_Menu_Model::vtranslateMenu('LBL_QUICK_CREATE_MODULE',$NAME)}: {Vtiger_Menu_Model::vtranslateMenu($SINGULAR_LABEL, $NAME)}"}
		{/if}
		<li class="tpl-menu-QuickCreate c-menu__item js-menu__item nav-item quickCreateModules quickCreate {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU.id}" data-js="mouseenter mouseleave">
			<a class="nav-link quickCreateModule{if $HASCHILDS=='true'} collapsed{/if}{if $ICON} hasIcon{/if}{if isset($MENU['hotkey'])} hotKey{/if}{if $HASCHILDS == 'true'} js-submenu-toggler is-submenu-toggler{/if}" href="#" {if isset($MENU['hotkey'])} data-hotkeys="{$MENU['hotkey']}"{/if} data-name="{$NAME}" data-url="{$MODULEMODEL->getQuickCreateUrl()}"
					{if $HASCHILDS == 'true'} data-toggle="collapse" data-target="#submenu-{$MENU['id']}" role="button" href="#" aria-haspopup="true" aria-expanded="false" aria-controls="submenu-{$MENU['id']}"{/if}>
				{$ICON}
				<span class="c-menu__item__text js-menu__item__text" title="{$MENU_ITEM_TEXT}" data-js="class: u-white-space-n">
					{$MENU_ITEM_TEXT}
				</span>
				{if $HASCHILDS == 'true'}
					<span class="toggler" aria-hidden="true"><span class="fas fa-plus-circle"></span><span class="fas fa-minus-circle"></span></span>
				{/if}
			</a>
			{include file=\App\Layout::getTemplatePath('menu/SubMenu.tpl', $MODULE)}
		</li>
	{/if}
{/strip}
