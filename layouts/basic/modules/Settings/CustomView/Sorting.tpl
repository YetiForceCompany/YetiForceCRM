{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	{assign 'CUSTOM_VIEW' CustomView_Record_Model::getInstanceById($CVID)}
	{assign 'SORT_ORDER_BY' ","|explode:$CUSTOM_VIEW->get('sort')}
	{assign 'SORT_ORDER' $SORT_ORDER_BY[1]}
	{if !$SORT_ORDER}
		{assign 'SORT_ORDER' 'ASC'}
	{/if}
	<form class="" id="sortingCustomView">
		<input type="hidden" id="cvid" name="cvid" value="{$CVID}" />
		<input type="hidden" id="sourceModule" value="{$SOURCE_MODULE}" />
		<input type="hidden" id="sortOrder" name="sortOrder" value="{$SORT_ORDER}" />
		<div class="modal-header">
			<div class="pull-left">
				<h3 class="modal-title">{vtranslate('LBL_SORTING_SETTINGS', $MODULE_NAME)}</h3>
			</div>
			<div class="clearfix"></div>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-md-12">
					<div class="alert alert-danger fade in">
						<a href="#" class="close" data-dismiss="alert">&times;</a>
						{vtranslate('LBL_SORTING_SETTINGS_WORNING', $MODULE_NAME)}
					</div>
				</div>
				<div class="col-xs-12 form-group">
					<label class="col-xs-12 control-label">
						{vtranslate('LBL_SELECT_FIELD_TO_SORT_RECORDS', $MODULE_NAME)}
					</label>
					<div class="col-md-9 col-sm-10 col-xs-12">
						<div class="input-group">
							<div class="input-group-btn" id="basic-addon1">
								<button type="button" class="btn btn-danger clear" title="{vtranslate('LBL_CLEAR', $MODULE_NAME)}">
									<span class="glyphicon glyphicon-remove-sign"></span>
								</button>
							</div>
							<select class="select2 form-control" name="defaultOrderBy" id="defaultOrderBy">
								<option></option>
								{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
									<optgroup label='{vtranslate($BLOCK_LABEL, $SOURCE_MODULE_MODEL->getName())}'>
										{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
											{if $FIELD_MODEL->isListviewSortable()}
												<option value="{$FIELD_MODEL->get('column')}"{if $FIELD_MODEL->get('column') eq $SORT_ORDER_BY[0]} selected{/if}>
													{vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE_MODEL->getName())}
												</option>
											{/if}
										{/foreach}
									</optgroup>
								{/foreach}
								{*Required to include event fields for columns in calendar module advanced filter*}
								{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$EVENT_RECORD_STRUCTURE}
									<optgroup label='{vtranslate($BLOCK_LABEL, 'Events')}'>
										{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
											{if $FIELD_MODEL->isListviewSortable()}
												<option value="{$FIELD_MODEL->get('column')}"{if $FIELD_MODEL->get('column') eq $SORT_ORDER_BY[0]} selected{/if}>
													{vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE_MODEL->getName())}
												</option>
											{/if}
										{/foreach}
									</optgroup>
								{/foreach}
							</select>
							<div class="input-group-btn" id="basic-addon2">
								<button type="button" class="btn btn-primary sortOrderButton" id="sortOrderButton">
									<span class="glyphicon glyphicon-sort-by-attributes{if $SORT_ORDER eq 'DESC'} hide{/if}" data-val="ASC" title="{vtranslate('LBL_SORT_ASCENDING_ORDER', $MODULE_NAME)}"></span>
									<span class="glyphicon glyphicon-sort-by-attributes-alt{if $SORT_ORDER eq 'ASC'} hide{/if}" data-val="DESC" title="{vtranslate('LBL_SORT_DESCENDING_ORDER', $MODULE_NAME)}"></span>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="submit" class="btn btn-success">{vtranslate('LBL_SAVE', $MODULE_NAME)}</button>
			<button type="button" class="btn btn-warning dismiss" data-dismiss="modal">{vtranslate('LBL_CLOSE', $MODULE_NAME)}</button>
		</div>
	</form>
{/strip}
