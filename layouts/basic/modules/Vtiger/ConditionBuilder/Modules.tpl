{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getModulesListValues()}
	{assign var=FIELD_VALUES value=explode(',', $VALUE)}
	<div class="tpl-Base-ConditionBuilder-Modules">
		<select class="js-modules-field select2 form-control js-condition-builder-value"
			title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}"
			multiple="multiple" data-js="val" data-placeholder="{\App\Language::translate('LBL_SELECT_OPTION')}">
			{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
				<option value="{\App\Purifier::encodeHtml($PICKLIST_VALUE['name'])}"
					title="{\App\Purifier::encodeHtml($PICKLIST_VALUE['name'])}" {if in_array(trim($PICKLIST_VALUE['name']), $FIELD_VALUES)} selected {/if}>{\App\Purifier::encodeHtml($PICKLIST_VALUE['label'])}</option>
			{/foreach}
		</select>
	</div>
{/strip}
