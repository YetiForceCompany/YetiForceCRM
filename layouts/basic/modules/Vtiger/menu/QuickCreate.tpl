{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
	{assign var='MODULEMODEL' value=Vtiger_Module_Model::getInstance($MENU.tabid)}
	{assign var='QUICKCREATEMODULE' value=$MODULEMODEL->isQuickCreateSupported()}
	{assign var='SINGULAR_LABEL' value=$MODULEMODEL->getSingularLabelKey()}
	{assign var='NAME' value=$MODULEMODEL->getName()}
	{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon($MENU, Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE))}
	{if $QUICKCREATEMODULE == '1' && \App\Module::isModuleActive($NAME) && ($PRIVILEGESMODEL->isAdminUser() || $PRIVILEGESMODEL->hasGlobalWritePermission() || $PRIVILEGESMODEL->hasModuleActionPermission($MENU.tabid, 'CreateView') ) }
		<li class="quickCreateModules quickCreate {if !$HASCHILDS}hasParentMenu{/if} " data-id="{$MENU.id}" role="menuitem" tabindex="{$TABINDEX}" {if $HASCHILDS}aria-haspopup="{$HASCHILDS}"{/if}>
			<a class="quickCreateModule {if $ICON}hasIcon{/if} {if isset($MENU['hotkey'])}hotKey{/if}" {if isset($MENU['hotkey'])}data-hotkeys="{$MENU['hotkey']}"{/if} data-name="{$NAME}" data-url="{$MODULEMODEL->getQuickCreateUrl()}" href="javascript:void(0)">
				{if $ICON}
					<div  {if $DEVICE == 'Desktop'}class="iconContainer"{/if}>
						<div {if $DEVICE == 'Desktop'}class="iconImage" {/if}>{$ICON}</div>
					</div>
				{/if}
				<div {if $DEVICE == 'Desktop'}class="labelConstainer"{/if}>
					<div {if $DEVICE == 'Desktop'}class="labelValue" {/if}>
						<span class="menuName">
							{if $MENU.name != ''}
								{vtranslate($MENU.name,'Menu')}
							{else}
								{Vtiger_Menu_Model::vtranslateMenu('LBL_QUICK_CREATE_MODULE',$NAME)}: {Vtiger_Menu_Model::vtranslateMenu($SINGULAR_LABEL, $NAME)}
							{/if}
						</span>
					</div>
				</div>
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
