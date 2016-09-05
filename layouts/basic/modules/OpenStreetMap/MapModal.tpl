{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="massEditHeader" class="modal-title">{vtranslate('LBL_MAP', $MODULE_NAME)}</h3>
	</div>
	<div class="modal-body container-fluid">
		<div class="col-xs-9 paddingLRZero">
			<div id="mapid"></div>
		</div>
		<div class="col-xs-3">
			<div class="input-group group-btn">
				<select class="select2 fieldsToGroup">
					{foreach from=$FIELDS_TO_GROUP item=FIELD_MODEL}
						<option value="{$FIELD_MODEL->getFieldName()}">{vtranslate($FIELD_MODEL->getFieldLabel(), $SRC_MODULE)}</option>
					{/foreach}
				</select>
				<div class="input-group-btn">
					<button class="btn btn-primary groupBy">{vtranslate('LBL_GROUP_BY', $MODULE_NAME)}</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
	</div>
{/strip}
