 {*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
 <!-- tpl-Settings-Workflows-Tasks-VTRecordCollector -->
 {strip}
	 <input type="hidden" id="taskFields"
	 	value="{\App\Purifier::encodeHtml(\App\Json::encode($TASK_OBJECT->getFieldNames()))}" />
	 <div class="createRecordCollector">
	 	<div class="row">
	 		<label class="col-md-4 col-form-label">
	 			<strong>{\App\Language::translate('LBL_RECORD_COLLECTOR','Settings.RecordCollector')}
	 				<span class="redColor">*</span>
	 			</strong>
	 		</label>
	 		<div class="col-md-6">
	 			<select class="select2 " id="recordCollector" name="recordCollector"
	 				data-validation-engine='validate[required]'
	 				data-select="allowClear"
	 				data-placeholder="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}">
	 				<option value="">{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
	 				{foreach from=\App\RecordCollector::getByType('FillFields', $SOURCE_MODULE) key=COLLECTOR_NAME item=COLLECTOR_VALUE}
		 				<option value="{$COLLECTOR_NAME}" {if isset($TASK_OBJECT->recordCollector) && $TASK_OBJECT->recordCollector eq $COLLECTOR_NAME} selected="" {/if} data-fields='{\App\Json::encode(array_values($COLLECTOR_VALUE->formFieldsToRecordMap[$SOURCE_MODULE]))}'>
		 					{\App\Language::translate($COLLECTOR_VALUE->label, 'Other.RecordCollector')}
		 				</option>
	 				{/foreach}
	 			</select>
	 		</div>
	 	</div>
	 	<br />
	 </div>
	 <!-- /tpl-Settings-Workflows-Tasks-VTRecordCollector -->
{/strip}
