{strip}
<div class="searchMenu">
	<div class="input-group">
		<span class="input-group-btn">
			<select class="chzn-select form-control col-md-5" title="{vtranslate('LBL_SEARCH_MODULE', $MODULE_NAME)}" id="basicSearchModulesList" >
				<option value="" class="globalSearch_module_All">{vtranslate('LBL_ALL_RECORDS', $MODULE_NAME)}</option>
				{foreach key=MODULE_NAME item=fieldObject from=$SEARCHABLE_MODULES}
					{if isset($SEARCHED_MODULE) && $SEARCHED_MODULE eq $MODULE_NAME && $SEARCHED_MODULE !== 'All'}
						<option value="{$MODULE_NAME}" class="globalSearch_module_{$MODULE_NAME}" selected>{vtranslate($MODULE_NAME,$MODULE_NAME)}</option>
					{else}
						<option value="{$MODULE_NAME}" class="globalSearch_module_{$MODULE_NAME}">{vtranslate($MODULE_NAME,$MODULE_NAME)}</option>
					{/if}
				{/foreach}
			</select>
		</span>
	</div>
	<div class="input-group">
		<input type="text" class="form-control" title="{vtranslate('LBL_GLOBAL_SEARCH')}" id="globalMobileSearchValue" 
			placeholder="{vtranslate('LBL_GLOBAL_SEARCH')}" results="10" />
		<span class="input-group-btn">
			<div class="pull-right">
				<button class="btn btn-default" id="searchMobileIcon" type="button">
					<span class="glyphicon glyphicon-search"></span>
				</button>
			</div>
		</span>
	</div>
	<div class="pull-left">
		<button class="btn btn-default" id="globalSearch" title="{vtranslate('LBL_ADVANCE_SEARCH')}" type="button">
			<span class="glyphicon glyphicon-th-large"></span>
		</button>
	</div>
</div>
{/strip}
