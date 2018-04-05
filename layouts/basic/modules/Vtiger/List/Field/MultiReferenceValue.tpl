{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if $VIEWID}
		{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getUITypeModel()->getPicklistValuesForModuleList($MODULE, $VIEWID)}
	{else}
		{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getUITypeModel()->getPicklistValues()}
	{/if}
	{assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var=SEARCH_VALUES value=explode('##',$SEARCH_INFO['searchValue'])}
	{assign var="PARAMS" value=$FIELD_MODEL->getFieldParams()}
	{assign var="RELATED_FIELD_MODEL" value=Vtiger_Field_Model::getInstanceFromFieldId($PARAMS['field'])}
	<div class="tpl-List-Field-MultiReferenceValue picklistSearchField">
		<select class="select2noactive listSearchContributor" name="{$FIELD_MODEL->getName()}" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" multiple data-fieldinfo="{$FIELD_INFO|escape}">
			{foreach item=VALUE from=$PICKLIST_VALUES}
				<option value="{$VALUE}" {if in_array($VALUE,$SEARCH_VALUES) && ($VALUE neq "")}selected{/if}>
					{$RELATED_FIELD_MODEL->getUITypeModel()->getDisplayValue($VALUE)}
				</option>
			{/foreach}
		</select>
	</div>
{/strip}
