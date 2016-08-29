{strip}
{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
	{assign var='MODULEMODEL' value=Vtiger_Module_Model::getInstance($MENU.tabid)}
	{assign var='quickCreateModule' value=$MODULEMODEL->isQuickCreateSupported()}
	{assign var='singularLabel' value=$MODULEMODEL->getSingularLabelKey()}
	{assign var='NAME' value=$MODULEMODEL->getName()}
	{if $quickCreateModule == '1' && \includes\Modules::isModuleActive($NAME) && ($PRIVILEGESMODEL->isAdminUser() || $PRIVILEGESMODEL->hasGlobalWritePermission() || $PRIVILEGESMODEL->hasModuleActionPermission($MENU.tabid, 'EditView') ) }
		<li class="quickCreate {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU.id}" role="menuitem" tabindex="{$TABINDEX}" {if $HASCHILDS}aria-haspopup="{$HASCHILDS}"{/if}>
			<a class="quickCreateModule {if isset($MENU['hotkey'])}hotKey{/if}" {if isset($MENU['hotkey'])}data-hotkeys="{$MENU['hotkey']}"{/if} data-name="{$NAME}" data-url="{$MODULEMODEL->getQuickCreateUrl()}" href="javascript:void(0)">
				<span class="menuName">
					{if $MENU.name != ''}
						{vtranslate($MENU.name,'Menu')}
					{else}
						{Vtiger_Menu_Model::vtranslateMenu('LBL_QUICK_CREATE_MODULE',$NAME)}: {Vtiger_Menu_Model::vtranslateMenu($singularLabel,$NAME)}
					{/if}
				</span>
			</a>
			{if $DEVICE == 'Desktop'}
				{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE DEVICE=$DEVICE}
			{/if}
		</li>
		{if $DEVICE == 'Mobile'}
			{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE DEVICE=$DEVICE}
		{/if}
	{/if}
{/strip}
