{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{if $VIEWID}
		{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getUITypeModel()->getPicklistValuesForModuleList($MODULE, $VIEWID)}
	{else}
		{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getUITypeModel()->getPicklistValues()}
	{/if}
	{assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var=SEARCH_VALUES value=explode(',',$SEARCH_INFO['searchValue'])}
	{assign var="PARAMS" value=$FIELD_MODEL->getUITypeModel()->get('field')->getFieldParams()}
	<div class="picklistSearchField">
		<select class="select2noactive listSearchContributor" name="{$FIELD_MODEL->get('name')}" title="{vtranslate($FIELD_MODEL->get('label'), $MODULE)}" multiple data-fieldinfo="{$FIELD_INFO|escape}">
			{foreach item=VALUE from=$PICKLIST_VALUES}
				<option value="{$VALUE}" {if in_array($VALUE,$SEARCH_VALUES) && ($VALUE neq "")}selected{/if}>{vtranslate($VALUE, $PARAMS['module'])}</option>
			{/foreach}
		</select>
	</div>
{/strip}
