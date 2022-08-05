{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Kanban-Fields -->
	{if empty($BOARDS)}
		<div class="recordDetails">
			<p class="textAlignCenter">{\App\Language::translate('LBL_NO_BOARDS',$QUALIFIED_MODULE)}</p>
		</div>
	{else}
		<div class="js-boards" data-js="container">
			{foreach item=BOARD from=$BOARDS}
				{if isset($FIELDS_MODELS[$BOARD['fieldid']])}
					{assign var=FIELD_MODEL value=$FIELDS_MODELS[$BOARD['fieldid']]}
					<div class="card mb-2 js-board" data-id="{$BOARD['id']}">
						<div class="card-header d-flex justify-content-between align-items-center px-2 py-1">
							<h5 class="card-title my-0 form-row">
								<a class="px-2 u-cursor-move js-drag" data-js="ui-sortable-handle"><img class="align-baseline" src="{\App\Layout::getImagePath('drag.png')}" title="{\App\Language::translate('LBL_DRAG',$QUALIFIED_MODULE)}" /></a>
								{$FIELD_MODEL->getFullLabelTranslation()}
								{if !$FIELD_MODEL->isAjaxEditable()}
									<div class="js-popover-tooltip ml-2" data-js="popover" data-trigger="hover focus" data-content="{\App\Language::translate('LBL_NOT_VISIBLE_KANBAN', $QUALIFIED_MODULE)}">
										<span class="fas fa-triangle-exclamation text-danger"></span>
									</div>
								{/if}
							</h5>
							<div class="btn-toolbar btn-group-xs">
								<button type="button" class="btn btn-sm btn-danger float-right js-delete" title="{\App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}" data-js="click">
									<span class="fas fa-times"></span>
								</button>
							</div>
						</div>
						<div class="card-body">
							<div class="form-horizontal js-related-column-list-container" data-js="container">
								<div class="form-group row">
									<label class="col-sm-2 col-form-label text-right">{\App\Language::translate('LBL_DETAIL_FIELDS',$QUALIFIED_MODULE)} :</label>
									<div class="col-sm-10">
										<select multiple="multiple" data-type="detail_fields" class="select2 form-control js-sortable-fields" data-select-cb="registerSelectSortable" data-js="sortable|select2">
											{foreach item=LIST_FIELD_MODEL from=$FIELDS_MODELS}
												<option value="{$LIST_FIELD_MODEL->getName()}" {if in_array($LIST_FIELD_MODEL->getName(),$BOARD['detail_fields'])}selected="selected" data-sort-index="{array_search($LIST_FIELD_MODEL->getName(), $BOARD['detail_fields'])}" {/if}>
													{$LIST_FIELD_MODEL->getFullLabelTranslation()}
												</option>
											{/foreach}
										</select>
									</div>
								</div>
							</div>
							<div class="form-horizontal js-related-column-list-container" data-js="container">
								<div class="form-group row">
									<label class="col-sm-2 col-form-label text-right">{\App\Language::translate('LBL_SUM_FIELDS',$QUALIFIED_MODULE)} :</label>
									<div class="col-sm-10">
										<select multiple="multiple" data-type="sum_fields" class="select2 form-control js-sortable-fields" data-select-cb="registerSelectSortable" data-js="sortable|select2">
											{foreach item=LIST_FIELD_MODEL from=$SUM_FIELDS_MODELS}
												<option value="{$LIST_FIELD_MODEL->getName()}" {if in_array($LIST_FIELD_MODEL->getName(),$BOARD['sum_fields'])}selected="selected" data-sort-index="{array_search($LIST_FIELD_MODEL->getName(), $BOARD['sum_fields'])}" {/if}>
													{$LIST_FIELD_MODEL->getFullLabelTranslation()}
												</option>
											{/foreach}
										</select>
									</div>
								</div>
							</div>
						</div>
					</div>
				{/if}
			{/foreach}
		</div>
	{/if}
	<!-- /tpl-Settings-Kanban-Fields -->
{/strip}
