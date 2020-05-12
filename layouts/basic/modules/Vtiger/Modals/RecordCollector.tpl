{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Modals-RecordCollector -->
	<div class="modal-body js-modal-body" data-js="container">
		<form class="form-horizontal js-record-collector__form" data-js="submit">
			<input type="hidden" name="module" value="{$MODULE_NAME}"/>
			<input type="hidden" name="view" value="RecordCollector"/>
			<input type="hidden" name="mode" value="search"/>
			<input type="hidden" name="record" value="{$RECORD_ID}"/>
			<input type="hidden" name="collectorType" value="{$COLLECTOR_NAME}"/>
			{foreach item=FIELD_MODEL from=$RECORD_COLLECTOR->getFields()}
				<div class="form-group form-row">
					<label class="col-4 col-form-label">
						{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->get('labelModule'))}
						{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}:
					</label>
					<div class="col-8">
						{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME) FIELD_MODEL=$FIELD_MODEL MODULE=$MODULE_NAME RECORD=null}
					</div>
				</div>
			{/foreach}
			<div class="float-right">
				<button class="btn btn-success" type="submit" name="saveButton" data-js="click">
					<span class="fas fa-check mr-1"></span>{\App\Language::translate('LBL_SEARCH', $MODULE_NAME)}
				</button>
				<button class="btn btn-danger ml-1" type="reset" data-dismiss="modal">
					<span class="fas fa-times mr-1"></span>{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}
				</button>
			</div>
		</form>
		<div class="mt-5 pt-2 js-record-collector__summary" data-js="html"></div>
	</div>
	<!-- /tpl-Base-Modals-RecordCollector -->
{/strip}
