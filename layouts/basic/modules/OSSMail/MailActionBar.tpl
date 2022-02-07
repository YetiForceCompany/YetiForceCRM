{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-OSSMail-MailActionBar -->
	{if !$RECORD}
		{if \App\Privilege::isPermitted('OSSMailView', 'CreateView')}
			<input type="hidden" id="mailActionBarID" value="" />
			<div class="noRecords d-flex align-items-center justify-content-center border p-1 rounded-lg">
				{\App\Language::translate('LBL_MAIL_NOT_FOUND_IN_DB',$MODULE_NAME)}
				<a class="importMail btn btn-sm btn-outline-dark ml-2" type="button">
					<span class="fas fa-download mr-1"></span>
					{\App\Language::translate('LBL_IMPORT_MAIL_MANUALLY',$MODULE_NAME)}
				</a>
			</div>
		{/if}
	{elseif \App\Privilege::isPermitted('OSSMailView', 'DetailView', $RECORD)}
		<input type="hidden" id="mailActionBarID" value="{$RECORD}" />
		{assign var="MODULES_LEVEL_0" value=\App\ModuleHierarchy::getModulesByLevel(0)}
		{assign var="MODULES_LEVEL_1" value=\App\ModuleHierarchy::getModulesByLevel(1)}
		{assign var="MODULES_LEVEL_2" value=\App\ModuleHierarchy::getModulesByLevel(2)}
		{assign var="MODULES_LEVEL_3" value=\App\ModuleHierarchy::getModulesByLevel(3)}
		{assign var="MODULES_LEVEL_4" value=\App\ModuleHierarchy::getModulesByLevel(4)}
		{if !empty($MODULES_LEVEL_0)}
			<input type="hidden" id="modulesLevel0" value="{\App\Purifier::encodeHtml(\App\Json::encode(array_keys($MODULES_LEVEL_0)))}" />
		{/if}
		{if !empty($MODULES_LEVEL_1)}
			<input type="hidden" id="modulesLevel1" value="{\App\Purifier::encodeHtml(\App\Json::encode(array_keys($MODULES_LEVEL_1)))}" />
		{/if}
		{if !empty($MODULES_LEVEL_2)}
			<input type="hidden" id="modulesLevel2" value="{\App\Purifier::encodeHtml(\App\Json::encode(array_keys($MODULES_LEVEL_2)))}" />
		{/if}
		{if !empty($MODULES_LEVEL_3)}
			<input type="hidden" id="modulesLevel3" value="{\App\Purifier::encodeHtml(\App\Json::encode(array_keys($MODULES_LEVEL_3)))}" />
		{/if}
		{if !empty($MODULES_LEVEL_4)}
			<input type="hidden" id="modulesLevel4" value="{\App\Purifier::encodeHtml(\App\Json::encode(array_keys($MODULES_LEVEL_4)))}" />
		{/if}
		<div class="d-flex">
			<div class="flex-wrap action-bar col-11 px-0">
				<div class="action-bar__col">
					<div class="action-bar__head js-data">
						{if !empty($MODULES_LEVEL_0) || !empty($MODULES_LEVEL_3) || !empty($MODULES_LEVEL_1) || !empty($MODULES_LEVEL_2) || !empty($MODULES_LEVEL_4)}
							<div data-type="link" class="action-bar__head__container js-head-container p-1 pr-2 mb-1 rounded-lg" data-js="container">
								<input type="hidden" id="autoCompleteFields" class="js-mailAutoCompleteFields" value="{\App\Purifier::encodeHtml(\App\Json::encode(\App\Config::component('Mail','autoCompleteFields', [])))}" />
								{assign var="DEFAULT_RELATION_MODULE" value=\App\Config::component('Mail','defaultRelationModule')}
								{assign var="ACCESS_LEVEL_0" value=\App\ModuleHierarchy::accessModulesByLevel()}
								{assign var="ACCESS_LEVEL_1" value=\App\ModuleHierarchy::accessModulesByLevel(1)}
								{assign var="ACCESS_LEVEL_2" value=\App\ModuleHierarchy::accessModulesByLevel(2)}
								{assign var="ACCESS_LEVEL_3" value=\App\ModuleHierarchy::accessModulesByLevel(3)}
								{assign var="ACCESS_LEVEL_4" value=\App\ModuleHierarchy::accessModulesByLevel(4)}
								<label class="d-none" for="addRelationSelect">{\App\Language::translate('LBL_ADD_RELATION',$MODULE_NAME)}</label>
								<select id="addRelationSelect" required class="module action-bar__select mr-3px">
									<option value="" disabled="disabled" selected="selected">{\App\Language::translate('LBL_ADD_RELATION',$MODULE_NAME)}</option>
									{if $ACCESS_LEVEL_0}
										<optgroup label="{\App\Language::translate('LBL_RELATIONS',$MODULE_NAME)}">
											{foreach item="ITEM" key="MODULE" from=$ACCESS_LEVEL_0}
												<option value="{$MODULE}" {if $DEFAULT_RELATION_MODULE eq $MODULE} selected="selected" {/if}>
													{\App\Language::translate($MODULE, $MODULE)}
												</option>
											{/foreach}
										</optgroup>
									{/if}
									{if $ACCESS_LEVEL_4}
										<optgroup label="{\App\Language::translate('LBL_RELATIONS_EXTEND',$MODULE_NAME)}">
											{foreach item="ITEM" key="MODULE" from=$ACCESS_LEVEL_4}
												<option value="{$MODULE}" {if $DEFAULT_RELATION_MODULE eq $MODULE} selected="selected" {/if}>
													{\App\Language::translate($MODULE, $MODULE)}
												</option>
											{/foreach}
										</optgroup>
									{/if}
									{if $ACCESS_LEVEL_1}
										<optgroup label="{\App\Language::translate('LBL_PROCESS',$MODULE_NAME)}">
											{foreach item="ITEM" key="MODULE" from=$ACCESS_LEVEL_1}
												<option value="{$MODULE}" {if $DEFAULT_RELATION_MODULE eq $MODULE} selected="selected" {/if}>
													{\App\Language::translate($MODULE, $MODULE)}
												</option>
											{/foreach}
										</optgroup>
									{/if}
									{if $ACCESS_LEVEL_2}
										{foreach item="ITEM" key="MODULE" from=\App\ModuleHierarchy::accessModulesByLevel(1)}
											{assign var="ACCESS_PARENT" value=\App\ModuleHierarchy::accessModulesByParent($MODULE)}
											{if $ACCESS_PARENT}
												<optgroup label="{\App\Language::translate($MODULE,$MODULE)}">
													{foreach item="PARENT_ITEM" key="PARENT_MODULE" from=$ACCESS_PARENT}
														<option value="{$PARENT_MODULE}" {if $DEFAULT_RELATION_MODULE eq $PARENT_MODULE} selected="selected" {/if}>
															{\App\Language::translate($PARENT_MODULE, $PARENT_MODULE)}
														</option>
													{/foreach}
												</optgroup>
											{/if}
										{/foreach}
									{/if}
									{if $ACCESS_LEVEL_3}
										<optgroup label="{\App\Language::translate('FL_SUBPROCESS_SECOND_LEVEL',$MODULE_NAME)}">
											{foreach item="ITEM" key="MODULE" from=$ACCESS_LEVEL_3}
												<option value="{$MODULE}" {if $DEFAULT_RELATION_MODULE eq $MODULE} selected="selected" {/if}>
													{\App\Language::translate($MODULE, $MODULE)}
												</option>
											{/foreach}
										</optgroup>
									{/if}
								</select>
								<button class="addRecord action-bar__add-button mr-3px" title="{\App\Language::translate('LBL_ADD_RECORD',$MODULE_NAME)}">
									<span class="fas fa-plus"></span>
								</button>
								{if $ACCESS_LEVEL_0 || $ACCESS_LEVEL_3 || $ACCESS_LEVEL_1 || $ACCESS_LEVEL_2}
									<button class="selectRecord action-bar__select-button" data-type="0" title="{\App\Language::translate('LBL_SELECT_RECORD',$MODULE_NAME)}">
										<span class="fas fa-search"></span>
									</button>
								{/if}
							</div>
						{/if}
						{if !empty($MODULES_LEVEL_0)}
							{foreach key=MODULE item=ITEM from=$MODULES_LEVEL_0}
								{if !empty($RELATED_RECORDS[$MODULE])}
									{foreach item=RELATED from=$RELATED_RECORDS[$MODULE]}
										{include file=\App\Layout::getTemplatePath('MailActionBarRow.tpl', $MODULE_NAME)}
									{/foreach}
								{/if}
							{/foreach}
						{/if}
						{if !empty($MODULES_LEVEL_4)}
							{foreach key=MODULE item=ITEM from=$MODULES_LEVEL_4}
								{if !empty($RELATED_RECORDS[$MODULE])}
									{foreach item=RELATED from=$RELATED_RECORDS[$MODULE]}
										{include file=\App\Layout::getTemplatePath('MailActionBarRow.tpl', $MODULE_NAME)}
									{/foreach}
								{/if}
							{/foreach}
						{/if}
						{if !empty($MODULES_LEVEL_1)}
							{foreach key=MODULE item=ITEM from=$MODULES_LEVEL_1}
								{if !empty($RELATED_RECORDS[$MODULE])}
									{foreach item=RELATED from=$RELATED_RECORDS[$MODULE]}
										{include file=\App\Layout::getTemplatePath('MailActionBarRow.tpl', $MODULE_NAME)}
									{/foreach}
								{/if}
							{/foreach}
						{/if}
						{if !empty($MODULES_LEVEL_2)}
							{foreach key=MODULE item=ITEM from=$MODULES_LEVEL_2}
								{if !empty($RELATED_RECORDS[$MODULE])}
									{foreach item=RELATED from=$RELATED_RECORDS[$MODULE]}
										{include file=\App\Layout::getTemplatePath('MailActionBarRow.tpl', $MODULE_NAME)}
									{/foreach}
								{/if}
							{/foreach}
						{/if}
						{if !empty($MODULES_LEVEL_3)}
							{foreach key=MODULE item=ITEM from=$MODULES_LEVEL_3}
								{if !empty($RELATED_RECORDS[$MODULE])}
									{foreach item=RELATED from=$RELATED_RECORDS[$MODULE]}
										{include file=\App\Layout::getTemplatePath('MailActionBarRow.tpl', $MODULE_NAME)}
									{/foreach}
								{/if}
							{/foreach}
						{/if}
					</div>
				</div>
			</div>
			{if $RELATED_RECORDS}
				<div class="chevronBtnCube ml-auto">
					<button class="hideBtn" data-type="0" title="{\App\Language::translate('LBL_MINIMIZE_BAR',$MODULE_NAME)}">
						<span class="fas fa-chevron-circle-up"></span>
					</button>
				</div>
			{/if}
		</div>
	{else}
		<div class="action-bar__head">
			<div class="action-bar__head__message w-100">
				{\App\Language::translate('LBL_BAR_ACTIONS_NOT_AVAILABLE', $MODULE_NAME)}
			</div>
		</div>
	{/if}
	<!-- /tpl-OSSMail-MailActionBar -->
{/strip}
