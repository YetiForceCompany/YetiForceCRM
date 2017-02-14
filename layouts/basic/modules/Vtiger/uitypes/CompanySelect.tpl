{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
	<input type="hidden" name="{$FIELD_MODEL->getFieldName()}" value="" />
	<select id="{$MODULE}_{$VIEW}_fieldName_{$FIELD_MODEL->get('name')}" title="{\App\Language::translate($FIELD_MODEL->get('label'), $MODULE)}" class="chzn-select form-control col-md-12" name="{$FIELD_MODEL->get('name')}" data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->isMandatory() eq true} {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if}>
		<option value="">{\App\Language::translate('LBL_SELECT_OPTION','Vtiger')}</option>
		{foreach item=PICKLIST_VALUE key=KEY from=$PICKLIST_VALUES}
			<option value="{$KEY}" {if $KEY eq $FIELD_VALUE} selected {/if}>
				{if $PICKLIST_VALUE['default']}
					{\App\Language::translate('PLL_DEFAULT', $MODULE)}
				{else}
					{$PICKLIST_VALUE['name']}
				{/if}
			</option>
		{/foreach}
	</select>
{/strip}


