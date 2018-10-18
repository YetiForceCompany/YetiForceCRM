{strip}
	<div class="tpl-Base-ConditionBuilder js-condition-builder">
		<div class="js-condition-builder-group-template hide">
			{include file=\App\Layout::getTemplatePath('ConditionBuilderGroup.tpl', $MODULE_NAME)}
		</div>
		{include file=\App\Layout::getTemplatePath('ConditionBuilderGroup.tpl', $MODULE_NAME) CONDITIONS_GROUP=$ADVANCE_CRITERIA}
	</div>
{/strip}