{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Workflows-Tasks-SumFieldFromDependent -->
	<input type="hidden" class="js-source-module" value="{$SOURCE_MODULE}" data-js="val">
	<div class="tpl-Settings-Workflows-Tasks-SumFieldFromDependent">
		<div class="row w-100 mb-2">
			<div class="col-md-3 align-self-md-center">
				<strong>{\App\Language::translate('LBL_SUMFIELDFROMDEPENDENT_SOURCE',$QUALIFIED_MODULE)}</strong>
			</div>
			<div class="col-md-4">
				<select name="sourceField" class="select2 form-control" data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
					{foreach from=$MODULE_MODEL->getFieldsByType(\App\QueryGenerator::NUMERIC_TYPE) item=FIELD_MODEL}
						<option value="{$FIELD_MODEL->getName()}" {if isset($TASK_OBJECT->sourceField) && $TASK_OBJECT->sourceField === $FIELD_MODEL->getName()}selected{/if}>
							{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SOURCE_MODULE)}
						</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="row w-100">
			<div class="col-md-3 align-self-md-center">
				<strong>{\App\Language::translate('LBL_SUMFIELDFROMDEPENDENT_TARGET',$QUALIFIED_MODULE)}</strong>
			</div>
			<div class="col-md-4">
				<select name="targetField"
					data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}"
					class="select2 form-control">
					{foreach item=REFERENCE_FIELD from=$MODULE_MODEL->getFieldsByReference()}
						{foreach from=$REFERENCE_FIELD->getReferenceList() item=RELATION_MODULE_NAME}
							<optgroup
								label="{\App\Language::translate($REFERENCE_FIELD->getFieldLabel(), $SOURCE_MODULE)} ({\App\Language::translate($RELATION_MODULE_NAME, $RELATION_MODULE_NAME)})">
								{assign var=RELATION_MODULE_MODEL value=Vtiger_Module_Model::getInstance($RELATION_MODULE_NAME)}
								{foreach from=$RELATION_MODULE_MODEL->getFieldsByType(\App\QueryGenerator::NUMERIC_TYPE) item=FIELD_MODEL}
									{assign var=VALUE value=$REFERENCE_FIELD->getName()|cat:'::'|cat:$RELATION_MODULE_NAME|cat:'::'|cat:$FIELD_MODEL->getName()}
									<option value="{$VALUE}" {if isset($TASK_OBJECT->targetField) && $TASK_OBJECT->targetField === $VALUE}selected{/if}>
										{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $RELATION_MODULE_NAME)}
									</option>
								{/foreach}
							</optgroup>
						{/foreach}
					{/foreach}
				</select>
			</div>
		</div>
		<div class="col-12">
			<input type="hidden" name="conditions" value="">
			{include file=\App\Layout::getTemplatePath('ConditionBuilder.tpl') MODULE_NAME=$SOURCE_MODULE}
		</div>
	</div>
	<!-- /tpl-Settings-Workflows-Tasks-SumFieldFromDependent -->
{/strip}
