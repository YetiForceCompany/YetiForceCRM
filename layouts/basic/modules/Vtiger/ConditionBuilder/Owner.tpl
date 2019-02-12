{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-ConditionBuilder-Owner">
		{assign var=VALUES value=explode('##', $VALUE)}
		{assign var=ASSIGNED_USER_ID value=$FIELD_MODEL->getName()}
		{if !AppConfig::performance('SEARCH_OWNERS_BY_AJAX')}
			{assign var=ALL_ACTIVEUSER_LIST value=\App\Fields\Owner::getInstance($FIELD_MODEL->getModuleName())->getAccessibleUsers()}
			{if $ASSIGNED_USER_ID neq 'modifiedby'}
				{assign var=ALL_ACTIVEGROUP_LIST value=\App\Fields\Owner::getInstance($FIELD_MODEL->getModuleName())->getAccessibleGroups()}
			{else}
				{assign var=ALL_ACTIVEGROUP_LIST value=[]}
			{/if}
		{/if}
		<select class="select2 form-control js-condition-builder-value"
				title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}"
				multiple="multiple"
				data-js="val"
				data-placeholder="{\App\Language::translate('LBL_SELECT_OPTION')}"
				{if AppConfig::performance('SEARCH_OWNERS_BY_AJAX')}
			data-ajax-search="1" data-ajax-url="index.php?module={$MODULE_NAME}&action=Fields&mode=getOwners&fieldName={$ASSIGNED_USER_ID}" data-minimum-input="{AppConfig::performance('OWNER_MINIMUM_INPUT_LENGTH')}"{' '}
				{/if}>
			{if AppConfig::performance('SEARCH_OWNERS_BY_AJAX')}
				{foreach from=$VALUES item=OWNER_ID}
					<option value="{$OWNER_ID}" selected>{\App\Fields\Owner::getLabel($OWNER_ID)}</option>
				{/foreach}
			{else}
				{if count($ALL_ACTIVEUSER_LIST) gt 0}
					<optgroup label="{\App\Language::translate('LBL_USERS')}">
						{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
							<option value="{$OWNER_ID}" {if in_array($OWNER_ID, $VALUES)} selected {/if}>
								{$OWNER_NAME}
							</option>
						{/foreach}
					</optgroup>
				{/if}
				{if count($ALL_ACTIVEGROUP_LIST) gt 0}
					<optgroup label="{\App\Language::translate('LBL_GROUPS')}">
						{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
							<option value="{$OWNER_ID}" {if in_array($OWNER_ID, $VALUES)} selected {/if} >
								{$OWNER_NAME}
							</option>
						{/foreach}
					</optgroup>
				{/if}
			{/if}
		</select>
	</div>
{/strip}
