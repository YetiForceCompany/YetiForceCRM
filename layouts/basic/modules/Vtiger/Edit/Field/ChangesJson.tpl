{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Field-ChangesJson -->
	{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
	{assign var=TABINDEX value=$FIELD_MODEL->getTabIndex()}
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD)}
	{assign var=VALUE value=$FIELD_MODEL->getEditViewValue($FIELD_MODEL->get('fieldvalue'), $RECORD)}
	{assign var=IS_EDITABLE_READ_ONLY value=$FIELD_MODEL->isEditableReadOnly()}
	{assign var=PARAMS value=$FIELD_MODEL->getFieldParams()}
	<div class="d-flex align-items-center js-changesjson-container" data-js="container">
		<input name="{$FIELD_MODEL->getName()}" type="hidden" value="{\App\Purifier::encodeHtml($VALUE)}" class="js-changesjson-value" data-fieldtype="{$FIELD_MODEL->getFieldDataType()}" data-fieldinfo='{$FIELD_INFO}' {if $IS_EDITABLE_READ_ONLY}readonly="readonly" {/if} data-related-field="{$PARAMS['field']}" data-module="{$FIELD_MODEL->getModuleName()}" />
		<div class="input-group referenceGroup {$WIDTHTYPE_GROUP}">
			<input id="{$MODULE_NAME}_editView_fieldName_{$FIELD_NAME}_display" name="{$FIELD_NAME}_display" type="text" disabled class="marginLeftZero form-control" value="{$FIELD_VALUE}" />
			<div class="input-group-append u-cursor-pointer">
				<button class="btn btn-light js-changesjson-edit" type="button" tabindex="{$TABINDEX}" {if $IS_EDITABLE_READ_ONLY}disabled{/if}>
					<span id="{$MODULE_NAME}_editView_fieldName_{$FIELD_NAME}_edit" class="yfi yfi-full-editing-view" title="{\App\Language::translate('LBL_EDIT', $MODULE_NAME)}"></span>
				</button>
			</div>
		</div>
	</div>
	<!-- /tpl-Base-Edit-Field-ChangesJson -->
{/strip}
