{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign 'CUSTOM_VIEW' CustomView_Record_Model::getInstanceById($CVID)}
	{assign 'SORT_ORDER_BY' ","|explode:$CUSTOM_VIEW->get('sort')}

	{if !empty($SORT_ORDER_BY[1])}
		{assign 'SORT_ORDER' $SORT_ORDER_BY[1]}
	{else}
		{assign 'SORT_ORDER' 'ASC'}
	{/if}
	<form class="" id="js-sorting-filter" data-js="submit">
		<input type="hidden" id="cvid" name="cvid" value="{$CVID}"/>
		<input type="hidden" id="sourceModule" value="{$SOURCE_MODULE}"/>
		<input type="hidden" id="sortOrder" name="sortOrder" value="{$SORT_ORDER}"/>
		<div class="modal-header">
			<h5 class="modal-title">{\App\Language::translate('LBL_SORTING_SETTINGS', $MODULE_NAME)}</h5>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-md-12">
					<div class="alert alert-danger alert-dismissible fade show" role="alert">
						<h4 class="alert-heading">{\App\Language::translate('LBL_ATTENTION', $MODULE_NAME)}</h4>
						{\App\Language::translate('LBL_SORTING_SETTINGS_WORNING', $MODULE_NAME)}
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
				</div>
				<label class="col-12">
					{\App\Language::translate('LBL_SELECT_FIELD_TO_SORT_RECORDS', $MODULE_NAME)}
				</label>
				<div class="col-12">
					<div class="input-group js-sort-container flex-nowrap" data-js="value">
						<div class="input-group-prepend">
							<button type="button" class="btn btn-danger js-clear" data-js="click"
									title="{\App\Language::translate('LBL_CLEAR', $MODULE_NAME)}">
								<span class="fas fa-times-circle"></span>
							</button>
						</div>
						<select class="select2 form-control col-3" name="defaultOrderBy" id="defaultOrderBy">
							<option></option>
							{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
								<optgroup
										label='{\App\Language::translate($BLOCK_LABEL, $SOURCE_MODULE_MODEL->getName())}'>
									{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
										{if $FIELD_MODEL->isListviewSortable()}
											<option value="{$FIELD_MODEL->get('column')}"{if $FIELD_MODEL->get('column') eq $SORT_ORDER_BY[0]} selected{/if}>
												{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SOURCE_MODULE_MODEL->getName())}
											</option>
										{/if}
									{/foreach}
								</optgroup>
							{/foreach}
						</select>
						<div class="input-group-append">
							<button type="button" class="btn btn-primary js-sort-order-button" data-js="click">
								<span class="fas fa-sort-amount-up{if $SORT_ORDER eq 'DESC'} d-none{/if}" data-val="ASC"
									  title="{\App\Language::translate('LBL_SORT_ASCENDING_ORDER', $MODULE_NAME)}"></span>
								<span class="fas fa-sort-amount-down{if $SORT_ORDER eq 'ASC'} d-none{/if}"
									  data-val="DESC"
									  title="{\App\Language::translate('LBL_SORT_DESCENDING_ORDER', $MODULE_NAME)}"></span>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="submit" class="btn btn-success">
				<span class="fas fa-check mr-1"></span>
				{\App\Language::translate('LBL_SAVE', $MODULE_NAME)}
			</button>
			<button type="button" class="btn btn-danger dismiss" data-dismiss="modal">
				<span class="fas fa-times mr-1"></span>
				{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}
			</button>
		</div>
	</form>
{/strip}
