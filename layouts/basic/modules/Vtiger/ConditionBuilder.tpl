{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-ConditionBuilder c-condition-builder js-condition-builder" data-js="container">
		<div class="js-condition-builder-group-template hide" data-js="template">
			{include file=\App\Layout::getTemplatePath('ConditionBuilderGroup.tpl', $MODULE_NAME)}
		</div>
		{include file=\App\Layout::getTemplatePath('ConditionBuilderGroup.tpl', $MODULE_NAME) CONDITIONS_GROUP=$ADVANCE_CRITERIA ROOT_ITEM=true}
	</div>
{/strip}