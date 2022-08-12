 {*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
 <!-- tpl-Settings-Workflows-Tasks-RecordCollector -->
 {strip}
	 <input type="hidden" id="taskFields" value="{\App\Purifier::encodeHtml(\App\Json::encode($TASK_OBJECT->getFieldNames()))}" />
	 <div class="createRecordCollector">
	 	<div class="row">
	 		<label class="col-md-4 col-form-label">
	 			<strong>{\App\Language::translate('LBL_RECORD_COLLECTOR','Settings.RecordCollector')}
	 				<span class="redColor">*</span>
	 			</strong>
	 		</label>
	 		<div class="col-md-6">
	 			<select class="select2" id="recordCollector" name="recordCollector" data-validation-engine='validate[required]' data-select="allowClear"
	 				data-placeholder="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}">
	 				<option value="">{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
	 				{foreach from=\App\RecordCollector::getAllByType('FillFields', $SOURCE_MODULE) key=COLLECTOR_NAME item=COLLECTOR}
		 				{assign var=COLLECTOR_CLASS value='App\\RecordCollectors\\'|cat:{$COLLECTOR_NAME}}
		 				<option value="{$COLLECTOR_CLASS}" class="js-fields" {if isset($TASK_OBJECT->recordCollector) && $TASK_OBJECT->recordCollector eq $COLLECTOR_CLASS} selected="" {/if} data-fields='{\App\Purifier::encodeHtml(\App\Json::encode($COLLECTOR->getFieldsLabelsByModule($SOURCE_MODULE)))}' data-js="data">
		 					{\App\Language::translate($COLLECTOR->label, 'Other.RecordCollector')}
		 				</option>
	 				{/foreach}
	 			</select>
	 		</div>
	 	</div>
	 	<div class="row mt-2">
	 		<label class="col-md-4 col-form-label">
	 			<strong>{\App\Language::translate('LBL_SELECT_FIELDS_SAVE', $QUALIFIED_MODULE)}</strong>
	 		</label>
	 		<div class="col-md-6">
	 			<select class="form-control select2 js-fields-map" multiple="multiple" data-value="value" name="fieldsMap[]" data-js="html">
	 				{if isset($TASK_OBJECT->recordCollector) && isset($TASK_OBJECT->fieldsMap)}
		 				{assign var=COLLECTOR value=\App\RecordCollector::getInstance($TASK_OBJECT->recordCollector, $MODULE_NAME)}
		 				{foreach from=$COLLECTOR->getFieldsLabelsByModule($SOURCE_MODULE) key=FIELD_NAME item=FIELD_LABEL }
			 				<option value="{$FIELD_NAME}" {if in_array($FIELD_NAME, $TASK_OBJECT->fieldsMap)} selected {/if}>{$FIELD_LABEL}</option>
		 				{/foreach}
	 				{/if}
	 			</select>
	 		</div>
	 	</div>
	 </div>
	 <!-- /tpl-Settings-Workflows-Tasks-RecordCollector -->
{/strip}
