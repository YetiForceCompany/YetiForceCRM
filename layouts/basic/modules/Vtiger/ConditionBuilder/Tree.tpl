{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-ConditionBuilder-Tree">
		{assign var=ALL_VALUES value=\App\Fields\Tree::getPicklistValue($FIELD_MODEL->getFieldParams(), $FIELD_MODEL->getModuleName())}
		{assign var=FIELD_VALUES value=explode('##', $VALUE)}
		<select class="select2 form-control js-condition-builder-value"
				data-js="val"
				title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}"
				multiple="multiple" data-placeholder="{\App\Language::translate('LBL_SELECT_OPTION')}">
			{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$ALL_VALUES}
				<option value="{\App\Purifier::encodeHtml($PICKLIST_NAME)}"
						title="{\App\Purifier::encodeHtml($PICKLIST_NAME)}" {if in_array($PICKLIST_NAME, $FIELD_VALUES)} selected {/if}>{\App\Purifier::encodeHtml($PICKLIST_VALUE)}</option>
			{/foreach}
		</select>
	</div>
{/strip}
