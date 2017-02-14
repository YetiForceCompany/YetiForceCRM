{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
	<select title="{\App\Language::translate($FIELD_MODEL->get('label'), $MODULE)}" class="select2 form-control col-md-12 listSearchContributor" name="{$FIELD_MODEL->get('name')}" data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->isMandatory() eq true} {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if}>
		<option value="">{\App\Language::translate('LBL_SELECT_OPTION','Vtiger')}</option>
		{foreach item=PICKLIST_VALUE key=KEY from=$PICKLIST_VALUES}
			<option value="{$KEY}">{$PICKLIST_VALUE['name']}</option>
		{/foreach}
	</select>
{/strip}


