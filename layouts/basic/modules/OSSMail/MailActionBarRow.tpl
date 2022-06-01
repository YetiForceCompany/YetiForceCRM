{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-OSSMail-MailActionBarRow rowRelatedRecord mb-1 pr-2 d-flex align-items-center" data-id="{$RELATED['id']}" data-module="{$RELATED['module']}">
		{if \App\Privilege::isPermitted($RELATED['module'], 'DetailView', $RELATED['id'])}
			<a class="modCT_{$RELATED['module']} js-popover-tooltip--record" href="{$URL}index.php?module={$RELATED['module']}&amp;view=Detail&amp;record={$RELATED['id']}" target="_blank">
				<span class="relatedModuleIcon yfm-{$RELATED['module']}" aria-hidden="true"></span>
				<span class="relatedName">
					{App\TextUtils::textTruncate($RELATED['label'],38)}
				</span>
			</a>
			<div class="rowActions">
				{if \App\Privilege::isPermitted('Calendar','CreateView')}
					<button class="addRelatedRecord" data-module="Calendar" title="{\App\Language::translate('LBL_ADD_CALENDAR',$MODULE_NAME)}">
						<span class="yfm-Calendar" aria-hidden="true"></span>
					</button>
				{/if}
				{if \App\Privilege::isPermitted('ModComments','CreateView')}
					<button class="addRelatedRecord" data-module="ModComments" title="{\App\Language::translate('LBL_ADD_MODCOMMENTS',$MODULE_NAME)}">
						<span class="fas fa-comments"></span>
					</button>
				{/if}
				{if in_array($RELATED['module'], ['HelpDesk','Project']) &&  \App\Privilege::isPermitted('HelpDesk','CreateView')}
					<button class="addRelatedRecord" data-module="HelpDesk" title="{\App\Language::translate('LBL_ADD_HELPDESK',$MODULE_NAME)}">
						<span class="yfm-HelpDesk" aria-hidden="true"></span>
					</button>
				{/if}
				{if in_array($RELATED['module'], ['Accounts','Contacts','Leads']) && \App\Privilege::isPermitted('Products','DetailView')}
					<button class="selectRecord" data-type="1" data-module="Products" title="{\App\Language::translate('LBL_ADD_PRODUCTS',$MODULE_NAME)}">
						<span class="yfm-Products" aria-hidden="true"></span>
					</button>
				{/if}
				{if in_array($RELATED['module'], ['Accounts','Contacts','Leads']) &&  \App\Privilege::isPermitted('Services','DetailView')}
					<button class="selectRecord" data-type="1" data-module="Services" title="{\App\Language::translate('LBL_ADD_SERVICES',$MODULE_NAME)}">
						<span class="yfm-Services" aria-hidden="true"></span>
					</button>
				{/if}
				{if $RELATED['is_related_to_documents'] && \App\Privilege::isPermitted('Documents', 'DetailView')}
					<button class="addAttachments" data-type="1" title="{\App\Language::translate('LBL_ADD_DOCUMENTS', $MODULE_NAME)}">
						<span class="yfm-Documents" aria-hidden="true"></span>
					</button>
				{/if}
				<button class="removeRecord " title="{\App\Language::translate('LBL_REMOVE_RELATION',$MODULE_NAME)} {$RELATED['label']}">
					<span class="fas fa-times"></span>
				</button>
			</div>
		{else}
			<a class="modCT_{$RELATED['module']}">
				<span class="relatedModuleIcon yfm-{$RELATED['module']}" aria-hidden="true"></span>
				<span class="relatedName">
					{App\TextUtils::textTruncate($RELATED['label'],38)}
				</span>
			</a>
		{/if}
	</div>
{/strip}
