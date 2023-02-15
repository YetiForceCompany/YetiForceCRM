{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Groups-List-Field-MultiPicklist -->
	{if $FIELD_MODEL->getName() eq 'members'}
		{assign var=FIELD_INFO value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
		{assign var=MEMBERS_ALL value=$FIELD_MODEL->getUITypeModel()->getMembersList($RECORD)}
		{if isset($SEARCH_INFO['searchValue'])}
			{assign var=SEARCH_VALUES value=explode('##', \App\Purifier::decodeHtml($SEARCH_INFO['searchValue']))}
		{else}
			{assign var=SEARCH_VALUES value=[]}
		{/if}
		<div class="tpl-Groups-List-Field-MultiPicklist picklistSearchField">
			<select class="select2noactive listSearchContributor" name="{$FIELD_MODEL->getName()}"
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
	{else}
		{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), 'Vtiger')}
	{/if}
	<!-- /tpl-Settings-Groups-List-Field-MultiPicklist -->
{/strip}
