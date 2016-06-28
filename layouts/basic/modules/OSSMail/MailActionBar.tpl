{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{if !$RECORD}
		<input type="hidden" id="mailActionBarID" value="" />
		<div class="noRecords">
			{vtranslate('LBL_MAIL_NOT_FOUND_IN_DB',$MODULE_NAME)} <a class="importMail">{vtranslate('LBL_IMPORT_MAIL_MANUALLY',$MODULE_NAME)}</a>
		</div>
	{else}
		<input type="hidden" id="mailActionBarID" value="{$RECORD}" />
		{assign var="MODULES_LEVEL_0" value=Vtiger_ModulesHierarchy_Model::getModulesByLevel()}
		{assign var="MODULES_LEVEL_1" value=Vtiger_ModulesHierarchy_Model::getModulesByLevel(1)}
		{assign var="MODULES_LEVEL_2" value=Vtiger_ModulesHierarchy_Model::getModulesByLevel(2)}
		<input type="hidden" id="modulesLevel0" value="{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode(array_keys($MODULES_LEVEL_0)))}" />
		<input type="hidden" id="modulesLevel1" value="{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode(array_keys($MODULES_LEVEL_1)))}" />
		<input type="hidden" id="modulesLevel2" value="{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode(array_keys($MODULES_LEVEL_2)))}" />
		<div class="head row">
			{if !empty($MODULES_LEVEL_0)}
				<div class="col-4" data-type="link">
					<div class="col">
						{vtranslate('LBL_RELATIONS',$MODULE_NAME)}
						<div class="pull-right">
							{assign var="ACCESS_LEVEL_0" value=Vtiger_ModulesHierarchy_Model::accessModulesByLevel()}
							{if $ACCESS_LEVEL_0}
								<select class="module">
									{foreach item="ITEM" key="MODULE" from=$ACCESS_LEVEL_0}
										<option value="{$MODULE}">
											{vtranslate($MODULE, $MODULE)}
										</option>
									{/foreach}
								</select>
								<button class="addRecord" title="{vtranslate('LBL_ADD_RECORD',$MODULE_NAME)}">
									<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
								</button>
							{/if}
							{if Vtiger_ModulesHierarchy_Model::accessModulesByLevel(0,'DetailView')}
								<button class="selectRecord" data-type="0" title="{vtranslate('LBL_SELECT_RECORD',$MODULE_NAME)}">
									<span class="glyphicon glyphicon-resize-small" aria-hidden="true"></span>
								</button>
							{/if}
						</div>
					</div>
				</div>
			{/if}
			{if !empty($MODULES_LEVEL_1)}
				<div class="col-4" data-type="process">
					<div class="col">
						{vtranslate('LBL_PROCESS',$MODULE_NAME)}
						<div class="pull-right">
							{assign var="ACCESS_LEVEL_1" value=Vtiger_ModulesHierarchy_Model::accessModulesByLevel(1)}
							{if $ACCESS_LEVEL_1}
								<select class="module">
									{foreach item="ITEM" key="MODULE" from=$ACCESS_LEVEL_1}
										<option value="{$MODULE}">
											{vtranslate($MODULE, $MODULE)}
										</option>
									{/foreach}
								</select>
								<button class="addRecord" title="{vtranslate('LBL_ADD_RECORD',$MODULE_NAME)}">
									<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
								</button>
							{/if}
							{if Vtiger_ModulesHierarchy_Model::accessModulesByLevel(1,'DetailView')}
								<button class="selectRecord" data-type="0" title="{vtranslate('LBL_SELECT_RECORD',$MODULE_NAME)}">
									<span class="glyphicon glyphicon-resize-small" aria-hidden="true"></span>
								</button>
							{/if}
						</div>
					</div>
				</div>
			{/if}
			{if !empty($MODULES_LEVEL_2)}
				<div class="col-4" data-type="subprocess">
					<div class="col">
						{vtranslate('LBL_SUB_PROCESS',$MODULE_NAME)}
						<div class="pull-right">
							{assign var="ACCESS_LEVEL_2" value=Vtiger_ModulesHierarchy_Model::accessModulesByLevel(2)}
							{if $ACCESS_LEVEL_2}
								<select class="module">
									{foreach item="ITEM" key="MODULE" from=Vtiger_ModulesHierarchy_Model::accessModulesByLevel(1)}
										{assign var="ACCESS_PARENT" value=Vtiger_ModulesHierarchy_Model::accessModulesByParent($MODULE)}
										{if $ACCESS_PARENT}
											<optgroup label="{vtranslate($MODULE,$MODULE)}">
												{foreach item="PARENT_ITEM" key="PARENT_MODULE" from=$ACCESS_PARENT}
													<option value="{$PARENT_MODULE}">
														{vtranslate($PARENT_MODULE, $PARENT_MODULE)}
													</option>
												{/foreach}
											</optgroup>
										{/if}
									{/foreach}
								</select>
								<button class="addRecord" title="{vtranslate('LBL_ADD_RECORD',$MODULE_NAME)}">
									<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
								</button>
							{/if}
							{if Vtiger_ModulesHierarchy_Model::accessModulesByLevel(2, 'DetailView')}
								<button class="selectRecord" data-type="0" title="{vtranslate('LBL_SELECT_RECORD',$MODULE_NAME)}">
									<span class="glyphicon glyphicon-resize-small" aria-hidden="true"></span>
								</button>
							{/if}
							<button class="hideBtn" data-type="0" title="{vtranslate('LBL_MINIMIZE_BAR',$MODULE_NAME)}">
								<span class="glyphicon glyphicon-chevron-up" aria-hidden="true"></span>
							</button>
						</div>
					</div>
				</div>
			{/if}
		</div>
		<div class="data row">
			{if !empty($MODULES_LEVEL_0)}
				<div class="col-4" data-type="link">
					<div class="col">
						{foreach key=MODULE item=ITEM from=$MODULES_LEVEL_0}
							{foreach item=RELETED from=$RELETED_RECORDS[$MODULE]}
								{include file='MailActionBarRow.tpl'|@vtemplate_path:$MODULE_NAME}
							{/foreach}
						{/foreach}
					</div>
				</div>
			{/if}
			{if !empty($MODULES_LEVEL_1)}
				<div class="col-4" data-type="link">
					<div class="col">
						{foreach key=MODULE item=ITEM from=$MODULES_LEVEL_1}
							{foreach item=RELETED from=$RELETED_RECORDS[$MODULE]}
								{include file='MailActionBarRow.tpl'|@vtemplate_path:$MODULE_NAME}
							{/foreach}
						{/foreach}
					</div>
				</div>
			{/if}
			{if !empty($MODULES_LEVEL_2)}
				<div class="col-4" data-type="link">
					<div class="col">
						{foreach key=MODULE item=ITEM from=$MODULES_LEVEL_2}
							{foreach item=RELETED from=$RELETED_RECORDS[$MODULE]}
								{include file='MailActionBarRow.tpl'|@vtemplate_path:$MODULE_NAME}
							{/foreach}
						{/foreach}
					</div>
				</div>
			{/if}
		</div>
	{/if}
{/strip}
