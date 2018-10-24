{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=PICKLIST_VALUES value=Vtiger_Theme::getAllSkins()}
	{assign var=FIELD_VALUES value=explode('##', $VALUE)}
	<div class="tpl-ConditionBuilder-Theme">
		<select class="js-theme-field select2 form-control js-condition-builder-value"
				title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}"
				multiple="multiple" data-placeholder="{\App\Language::translate('LBL_SELECT_OPTION')}">
			{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
				<option class="u-bg-{$PICKLIST_NAME} text-light u-hover-bold"
						value="{\App\Purifier::encodeHtml($PICKLIST_NAME)}"
						{if in_array($PICKLIST_NAME, $FIELD_VALUES)} selected {/if}>{\App\Purifier::encodeHtml(ucfirst($PICKLIST_NAME))}</option>
			{/foreach}
		</select>
	</div>
{/strip}
