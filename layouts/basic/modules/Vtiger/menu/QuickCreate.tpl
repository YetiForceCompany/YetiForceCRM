{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{assign var='MODULEMODEL' value=Vtiger_Module_Model::getInstance($MENU.tabid)}
	{assign var='QUICKCREATEMODULE' value=$MODULEMODEL->isQuickCreateSupported()}
	{assign var='SINGULAR_LABEL' value=$MODULEMODEL->getSingularLabelKey()}
	{assign var='NAME' value=$MODULEMODEL->getName()}
	{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon($MENU, Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE))}
	{if $QUICKCREATEMODULE == '1' && \App\Module::isModuleActive($NAME) && ($PRIVILEGESMODEL->isAdminUser() || $PRIVILEGESMODEL->hasGlobalWritePermission() || $PRIVILEGESMODEL->hasModuleActionPermission($MENU.tabid, 'CreateView') ) }
		<li class="tpl-menu-QuickCreate quickCreateModules quickCreate {if !$HASCHILDS}hasParentMenu{/if} " data-id="{$MENU.id}" role="presentation">
			<a class="quickCreateModule {if $ICON}hasIcon{/if} {if isset($MENU['hotkey'])}hotKey{/if}" {if isset($MENU['hotkey'])}data-hotkeys="{$MENU['hotkey']}"{/if} data-name="{$NAME}" data-url="{$MODULEMODEL->getQuickCreateUrl()}" 
				{if $HASCHILDS == 'true'}role="button"{/if} href="#" aria-haspopup="{$HASCHILDS}">
				{$ICON}
				<span class="menuName">
					{if $MENU.name != ''}
						{\App\Language::translate($MENU.name,'Menu')}
					{else}
						{Vtiger_Menu_Model::vtranslateMenu('LBL_QUICK_CREATE_MODULE',$NAME)}: {Vtiger_Menu_Model::vtranslateMenu($SINGULAR_LABEL, $NAME)}
					{/if}
				</span>
			</a>
			{include file=\App\Layout::getTemplatePath('menu/SubMenu.tpl', $MODULE)}
		</li>
	{/if}
{/strip}
