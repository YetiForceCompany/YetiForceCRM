{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-ConditionBuilder-Base input-group">
		<input class="form-control js-condition-builder-value"
			   data-js="val"
			   title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}"
			   value="{\App\Purifier::encodeHtml($VALUE)}"
			   autocomplete="off"/>
	</div>
{/strip}
