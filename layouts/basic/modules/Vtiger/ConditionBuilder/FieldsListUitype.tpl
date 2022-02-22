{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-ConditionBuilder-FieldsListUitype -->
	<div>
		{assign var=FIELD_NAME value=$FIELD_MODEL->getFieldName()}
		{assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldDataType()}
		{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getModule()->getFieldsByType($FIELD_TYPE, true)}
		<select class="js-picklist-field select2 form-control js-condition-builder-value"
			title="{\App\Language::translate('LBL_FIELDS_LIST', $FIELD_MODEL->getModuleName())}"
			data-validation-engine="validate[required]]"
			data-placeholder="{\App\Language::translate('LBL_SELECT_OPTION')}" data-js="value|container">
			{foreach item=PICKLIST_MODEL key=PICKLIST_NAME from=$PICKLIST_VALUES}
				{if $FIELD_NAME !== $PICKLIST_MODEL->getFieldName()}
					<option value="{\App\Purifier::encodeHtml($PICKLIST_MODEL->getCustomViewSelectColumnName($SELECTED_RELATED_FIELD_NAME))}" {if $VALUE === $PICKLIST_MODEL->getCustomViewSelectColumnName($SELECTED_RELATED_FIELD_NAME)}selected{/if}>
						{\App\Language::translate($PICKLIST_MODEL->getFieldLabel(), $PICKLIST_MODEL->getModuleName())}
					</option>
				{/if}
			{/foreach}
		</select>
	</div>
	<!-- /tpl-Base-ConditionBuilder-FieldsListUitype -->
{/strip}
