{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=FIELD_INFO_ARRAY value=$FIELD_MODEL->getFieldInfo()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getUITypeModel()->getDisplayValueEncoded($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(),$FIELD_INFO_ARRAY['limit'])}
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO_ARRAY))}
	<div class="tpl-Detail-Field-MultiImage c-multi-image js-multi-image">
		<div name="{$FIELD_MODEL->getFieldName()}" id="{$MODULE_NAME}_detailView_fieldName_{$FIELD_MODEL->getFieldName()}" data-value="{$FIELD_VALUE}" data-fieldinfo='{$FIELD_INFO}' class="js-multi-image__values" data-js="value"></div>
		<div class="d-inline js-multi-image__result" data-js="container" data-name="{$FIELD_MODEL->getFieldName()}"></div>
	</div>
{/strip}
