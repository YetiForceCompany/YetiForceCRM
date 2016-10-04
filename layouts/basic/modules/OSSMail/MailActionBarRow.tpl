{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="rowReletedRecord" data-id="{$RELETED['id']}" data-module="{$RELETED['module']}">
		<a href="{$URL}index.php?module={$RELETED['module']}&amp;view=Detail&amp;record={$RELETED['id']}" title="{vtranslate('SINGLE_'|cat:$RELETED['module'],$RELETED['module'])}: {$RELETED['label']}" target="_blank">
			<span class="relatedModuleIcon userIcon-{$RELETED['module']}" aria-hidden="true"></span>
			<span class="relatedName">
				{vtlib\Functions::textLength($RELETED['label'],38)}
			</span>
		</a>
		<div class="pull-right rowActions">
			{if Users_Privileges_Model::isPermitted('Calendar','CreateView')}
				<button class="addReletedRecord" data-module="Calendar" title="{vtranslate('LBL_ADD_CALENDAR',$MODULE_NAME)}">
					<span class="userIcon-Calendar" aria-hidden="true"></span>
				</button>
			{/if}
			{if Users_Privileges_Model::isPermitted('ModComments','CreateView')}
				<button class="addReletedRecord" data-module="ModComments" title="{vtranslate('LBL_ADD_MODCOMMENTS',$MODULE_NAME)}">
					<span class="glyphicon glyphicon-comment" aria-hidden="true"></span>
				</button>
			{/if}
			{if in_array($RELETED['module'], ['HelpDesk','Project']) &&  Users_Privileges_Model::isPermitted('HelpDesk','CreateView')}
				<button class="addReletedRecord" data-module="HelpDesk" title="{vtranslate('LBL_ADD_HELPDESK',$MODULE_NAME)}">
					<span class="userIcon-HelpDesk" aria-hidden="true"></span>
				</button>
			{/if}
			{if in_array($RELETED['module'], ['Accounts','Contacts','Leads']) && Users_Privileges_Model::isPermitted('Products','DetailView')}
				<button class="selectRecord" data-type="1" data-module="Products" title="{vtranslate('LBL_ADD_PRODUCTS',$MODULE_NAME)}">
					<span class="userIcon-Products" aria-hidden="true"></span>
				</button>
			{/if}
			{if in_array($RELETED['module'], ['Accounts','Contacts','Leads']) &&  Users_Privileges_Model::isPermitted('Services','DetailView')}
				<button class="selectRecord" data-type="1" data-module="Services" title="{vtranslate('LBL_ADD_SERVICES',$MODULE_NAME)}">
					<span class="userIcon-Services" aria-hidden="true"></span>
				</button>
			{/if}
			<button class="removeRecord " title="{vtranslate('LBL_REMOVE_RELATION',$MODULE_NAME)} {$RELETED['label']}">
				<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
			</button>
		</div>
	</div>
{/strip}
