{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if !$RECORD}
		<input type="hidden" id="mailActionBarID" value=""/>
		<div class="noRecords">
			{\App\Language::translate('LBL_MAIL_NOT_FOUND_IN_DB',$MODULE_NAME)} <a
					class="importMail">{\App\Language::translate('LBL_IMPORT_MAIL_MANUALLY',$MODULE_NAME)}</a>
		</div>
	{else}
		<input type="hidden" id="mailActionBarID" value="{$RECORD}"/>
		{assign var="MODULES_LEVEL_0" value=\App\ModuleHierarchy::getModulesByLevel()}
		{assign var="MODULES_LEVEL_1" value=\App\ModuleHierarchy::getModulesByLevel(1)}
		{assign var="MODULES_LEVEL_2" value=\App\ModuleHierarchy::getModulesByLevel(2)}
		{assign var="MODULES_LEVEL_3" value=\App\ModuleHierarchy::getModulesByLevel(3)}
		{if !empty($MODULES_LEVEL_0)}
			<input type="hidden" id="modulesLevel0"
				   value="{\App\Purifier::encodeHtml(\App\Json::encode(array_keys($MODULES_LEVEL_0)))}"/>
		{/if}
		{if !empty($MODULES_LEVEL_1)}
			<input type="hidden" id="modulesLevel1"
				   value="{\App\Purifier::encodeHtml(\App\Json::encode(array_keys($MODULES_LEVEL_1)))}"/>
		{/if}
		{if !empty($MODULES_LEVEL_2)}
			<input type="hidden" id="modulesLevel2"
				   value="{\App\Purifier::encodeHtml(\App\Json::encode(array_keys($MODULES_LEVEL_2)))}"/>
		{/if}
		{if !empty($MODULES_LEVEL_3)}
			<input type="hidden" id="modulesLevel3"
				   value="{\App\Purifier::encodeHtml(\App\Json::encode(array_keys($MODULES_LEVEL_3)))}"/>
		{/if}
		<div class="flex-wrap action-bar">
			<div class="action-bar__col">
				<div class="action-bar__head">
					{if !empty($MODULES_LEVEL_0) || !empty($MODULES_LEVEL_3) || !empty($MODULES_LEVEL_1) || !empty($MODULES_LEVEL_2)}
						<div data-type="link" class="action-bar__head__container js-head-container" data-js="container">
							{assign var="ACCESS_LEVEL_0" value=\App\ModuleHierarchy::accessModulesByLevel()}
							{assign var="ACCESS_LEVEL_3" value=\App\ModuleHierarchy::accessModulesByLevel(3)}
							{assign var="ACCESS_LEVEL_1" value=\App\ModuleHierarchy::accessModulesByLevel(1)}
							{assign var="ACCESS_LEVEL_2" value=\App\ModuleHierarchy::accessModulesByLevel(2)}
							<label class="d-none" for="addRelationSelect">{\App\Language::translate('LBL_ADD_RELATION',$MODULE_NAME)}</label>
							<select id="addRelationSelect" required class="module action-bar__select mr-5px">
								<option value="" disabled selected style="color: grey;">{\App\Language::translate('LBL_ADD_RELATION',$MODULE_NAME)}</option>
								{if $ACCESS_LEVEL_0}
									<optgroup label="{\App\Language::translate('LBL_RELATIONS',$MODULE_NAME)}">
										{foreach item="ITEM" key="MODULE" from=$ACCESS_LEVEL_0}
											<option value="{$MODULE}">
												{\App\Language::translate($MODULE, $MODULE)}
											</option>
										{/foreach}
									</optgroup>
								{/if}
								{if $ACCESS_LEVEL_3}
									<optgroup label="{\App\Language::translate('LBL_RELATIONS_EXTEND',$MODULE_NAME)}">
										{foreach item="ITEM" key="MODULE" from=$ACCESS_LEVEL_3}
											<option value="{$MODULE}">
												{\App\Language::translate($MODULE, $MODULE)}
											</option>
										{/foreach}
									</optgroup>
								{/if}
								{if $ACCESS_LEVEL_1}
									<optgroup label="{\App\Language::translate('LBL_PROCESS',$MODULE_NAME)}">
										{foreach item="ITEM" key="MODULE" from=$ACCESS_LEVEL_1}
											<option value="{$MODULE}">
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
													<option value="{$PARENT_MODULE}">
														{\App\Language::translate($PARENT_MODULE, $PARENT_MODULE)}
													</option>
												{/foreach}
											</optgroup>
										{/if}
									{/foreach}
								{/if}
							</select>
							<button class="addRecord action-bar__add-button mr-5px"
									title="{\App\Language::translate('LBL_ADD_RECORD',$MODULE_NAME)}">
								<span class="fas fa-plus"></span>
							</button>
							{if $ACCESS_LEVEL_0 || $ACCESS_LEVEL_3 || $ACCESS_LEVEL_1 || $ACCESS_LEVEL_2}
								<button class="selectRecord action-bar__select-button mr-5px" data-type="0"
										title="{\App\Language::translate('LBL_SELECT_RECORD',$MODULE_NAME)}">
									<span class="fas fa-search"></span>
								</button>
							{/if}
						</div>
					{/if}
				</div>
				<div class="action-bar__data flex-wrap js-data">
					{if !empty($MODULES_LEVEL_0)}
						{foreach key=MODULE item=ITEM from=$MODULES_LEVEL_0}
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
				</div>
			</div>
		</div>
		{if $RELATED_RECORDS}
			<div class="chevronBtnCube">
				<button class="hideBtn" data-type="0"
						title="{\App\Language::translate('LBL_MINIMIZE_BAR',$MODULE_NAME)}">
					<span class="fas fa-chevron-up"></span>
				</button>
			</div>
		{/if}
	{/if}
{/strip}
