{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-MailIntegration-Iframe-HeaderListItem -->
	{assign var=DETAIL_VIEW_PERMITTED value=\App\Privilege::isPermitted($RECORD['module'], 'DetailView', $RECORD['id'])}
	<li class="list-group-item list-group-item-action py-0 px-2 {if $DETAIL_VIEW_PERMITTED}js-list-item-click{/if}" data-id="{$RECORD['id']}" data-module="{$RECORD['module']}" data-field="{\App\ModuleHierarchy::getMappingRelatedField($RECORD['module'])}">
		{assign var=MODULE_MODEL value=Vtiger_Module_Model::getInstance($RECORD['module'])}
		<div class="d-flex w-100 align-items-center">
			<a class="modCT_{$RECORD['module']} {if $DETAIL_VIEW_PERMITTED}js-record-link js-popover-tooltip--record{/if} small u-text-inherit text-truncate" {if $DETAIL_VIEW_PERMITTED}href="{$URL}index.php?module={$RECORD['module']}&view=Detail&record={$RECORD['id']}" {/if} target="_blank">
				<span class="relatedModuleIcon yfm-{$RECORD['module']} mr-2" aria-hidden="true"></span>
				<span class="relatedName">{$RECORD['label']}</span>
			</a>
			{if $DETAIL_VIEW_PERMITTED}
				<div class="ml-auto btn-group btn-group-sm" role="group" aria-label="record actions">
					{if \App\Privilege::isPermitted('Calendar','CreateView')}
						<button class="js-add-related-record btn u-text-inherit js-popover-tooltip" data-module="Calendar" data-js="popover" data-content="{\App\Language::translate('LBL_ADD_CALENDAR',$MODULE_NAME)}">
							<span class="yfm-Calendar" aria-hidden="true"></span>
						</button>
					{/if}
					{if \App\Privilege::isPermitted('ModComments','CreateView') && $MODULE_MODEL->isCommentEnabled()}
						<button class="js-add-related-record btn u-text-inherit js-popover-tooltip" data-module="ModComments" data-js="popover" data-content="{\App\Language::translate('LBL_ADD_MODCOMMENTS',$MODULE_NAME)}">
							<span class="yfm-ModComments"></span>
						</button>
					{/if}
					{if $REMOVE_RECORD && \App\Privilege::isPermitted($RECORD['module'], 'RemoveRelation')}
						<button class="js-remove-record btn u-text-inherit js-popover-tooltip" data-js="popover" data-content="{\App\Language::translate('LBL_REMOVE_RELATION',$MODULE_NAME)}">
							<span class="fas fa-times"></span>
						</button>
					{/if}
				</div>
			{/if}
		</div>
	</li>
	<!-- /tpl-MailIntegration-Iframe-HeaderListItem -->
{/strip}
