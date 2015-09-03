{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{if vtlib_isModuleActive($MENU['name']) AND ($PRIVILEGESMODEL->isAdminUser() || $PRIVILEGESMODEL->hasGlobalReadPermission() || $PRIVILEGESMODEL->hasModulePermission($MENU.tabid) ) }
		{assign var=IMAGE value=$MENU['name']|cat:'.png'}
		<li class="menuModule .moduleColor_{$MENU['mod']} {$CLASS} {if !$HASPOPUP}hasParentMenu{/if}" data-id="{$MENU.id}" 
			role="menuitem" tabindex="{$TABINDEX}" {if $HASPOPUP}aria-haspopup="{$HASPOPUP}"{/if}>
			<a class="{if $MENU['name'] == $MODULE}active{/if} {if file_exists( vimage_path($IMAGE) )}isImage{/if}{if $MENU.hotkey}hotKey{/if}" {if $MENU.hotkey}data-hotkeys="{$MENU.hotkey}"{/if} href="{$MENU.dataurl}" 
			   {if $MENU.newwindow eq 1}target="_blank" {/if}>
				{if file_exists( vimage_path($IMAGE) )}
					<img src="{vimage_path($IMAGE)}" alt="{Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU['name'])}" class="moduleIcon" />
				{/if}
				{Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU['name'])}
			</a>
			{include file='menu/SubMenu.tpl'|@vtemplate_path:$MODULE}
		</li>
	{/if}
{/strip}
