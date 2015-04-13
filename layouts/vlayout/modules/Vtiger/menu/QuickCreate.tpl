{assign var='MODULEMODEL' value=Vtiger_Module_Model::getInstance($MENU.tabid)}
{assign var='quickCreateModule' value=$MODULEMODEL->isQuickCreateSupported()}
{assign var='singularLabel' value=$MODULEMODEL->getSingularLabelKey()}
{if $quickCreateModule == '1' && ($PRIVILEGESMODEL->isAdminUser() || $PRIVILEGESMODEL->getGlobalWritePermission() || $PRIVILEGESMODEL->hasModuleActionPermission($MENU.tabid, 'EditView') ) }
	<li class="quickCreate {$CLASS}" role="menuitem" tabindex="{$TABINDEX}" aria-haspopup="{$HASPOPUP}">
		<a id="menubar_quickCreate_{$MODULEMODEL->getName()}" class="quickCreateModule {if $MENU.hotkey}hotKey{/if}" {if $MENU.hotkey}data-hotkeys="{$MENU.hotkey}"{/if} data-name="{$MODULEMODEL->getName()}" data-url="{$MODULEMODEL->getQuickCreateUrl()}" href="javascript:void(0)">
			{if $MENU.name != ''}{vtranslate($MENU.name,'Menu')}{else}{vtranslate('LBL_QUICK_CREATE_MODULE','Menu')}: {vtranslate($singularLabel,$MODULEMODEL->getName())}{/if}
		</a>
		{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE}
	</li>
{/if}