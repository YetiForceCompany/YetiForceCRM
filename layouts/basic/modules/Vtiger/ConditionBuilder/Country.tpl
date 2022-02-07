{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
	{assign var=FIELD_VALUES value=explode('##', $VALUE)}
	<div class="tpl-Base-ConditionBuilder-Country">
		<select class="js-country-field select2 form-control js-condition-builder-value"
			title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}"
			multiple="multiple" data-js="val" data-placeholder="{\App\Language::translate('LBL_SELECT_OPTION')}">
			{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
				<option value="{\App\Purifier::encodeHtml($PICKLIST_NAME)}" title="{\App\Purifier::encodeHtml($PICKLIST_NAME)}" {if in_array($PICKLIST_NAME, $FIELD_VALUES)}selected{/if}>
					{\App\Language::translateSingleMod($PICKLIST_NAME,'Other.Country')}
				</option>
			{/foreach}
		</select>
	</div>
{/strip}
