{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Groups-List-Field-MultiPicklist -->
	{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
	{if $FIELD_NAME eq 'members'}
		{assign var=FIELD_INFO value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
		{assign var=MEMBERS_ALL value=$FIELD_MODEL->getUITypeModel()->getMembersList($RECORD)}
		{if isset($SEARCH_INFO['searchValue'])}
			{assign var=SEARCH_VALUES value=explode('##', \App\Purifier::decodeHtml($SEARCH_INFO['searchValue']))}
		{else}
			{assign var=SEARCH_VALUES value=[]}
		{/if}
		<div class="tpl-Groups-List-Field-MultiPicklist picklistSearchField">
			<select class="select2noactive listSearchContributor" name="{$FIELD_NAME}"
				title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" multiple="multiple"
				{if !$FIELD_MODEL->isActiveSearchView()} disabled="disabled" data-placeholder=" " {/if}
				data-fieldinfo='{$FIELD_INFO|escape}'
				{if !empty($FIELD_MODEL->get('source_field_name'))}
					data-source-field-name="{$FIELD_MODEL->get('source_field_name')}" data-module-name="{$FIELD_MODEL->getModuleName()}"
				{/if}>
				{foreach from=$MEMBERS_ALL key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
					<optgroup label="{\App\Language::translate($GROUP_LABEL)}">
						{foreach from=$ALL_GROUP_MEMBERS key=MEMBER_ID item=MEMBER}
							<option class="{$MEMBER['type']}" value="{$MEMBER_ID}" {if in_array($MEMBER_ID, $SEARCH_VALUES)} selected {/if}>{\App\Language::translate($MEMBER['name'])}</option>
						{/foreach}
					</optgroup>
				{/foreach}
			</select>
		</div>
	{else if $FIELD_NAME eq 'parentid'}
		{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
		{assign var=OWNERS_ALL value=$FIELD_MODEL->getUITypeModel()->getOwnerList($RECORD)}
		{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
		{function OPTGRUOP BLOCK_NAME='' OWNERS=[] ACTIVE='inactive'}
			{if $OWNERS}
				<optgroup label="{\App\Language::translate($BLOCK_NAME)}">
					{foreach key=OWNER_ID item=OWNER_NAME from=$OWNERS}
						<option value="{$OWNER_ID}"
							data-picklistvalue="{$OWNER_NAME}" {if in_array($OWNER_ID, $FIELD_VALUE)} selected="selected" {/if}
							data-userId="{$CURRENT_USER_ID}">
							{$OWNER_NAME}
						</option>
					{/foreach}
				</optgroup>
			{/if}
		{/function}
		<div class="w-100">
			<select class="select2noactive listSearchContributor" multiple title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}" name="{$FIELD_NAME}[]" data-fieldinfo='{$FIELD_INFO}'
				{foreach from=$OWNERS_ALL item=OWNERS key=BLOCK_NAME}
					{OPTGRUOP BLOCK_NAME=$BLOCK_NAME OWNERS=$OWNERS}
				{/foreach}
				</select>
		</div>
	{else}
		{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), 'Vtiger')}
	{/if}
	<!-- /tpl-Settings-Groups-List-Field-MultiPicklist -->
{/strip}
