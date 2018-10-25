{strip}
	<div class="tpl-Base-ConditionBuilder js-condition-builder" data-js="container">
		<div class="js-condition-builder-group-template hide" data-js="template">
			{include file=\App\Layout::getTemplatePath('ConditionBuilderGroup.tpl', $MODULE_NAME)}
		</div>
		{include file=\App\Layout::getTemplatePath('ConditionBuilderGroup.tpl', $MODULE_NAME) CONDITIONS_GROUP=$ADVANCE_CRITERIA ROOT_ITEM=true}
	</div>
{/strip}