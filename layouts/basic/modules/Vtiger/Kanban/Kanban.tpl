{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Kanban-Kanban -->
	<div class="mt-3 mx-0 c-kanban__columns">
		{foreach item=COLUMN key=COLUMN_NAME from=$COLUMNS}
			<div class="px-1 c-kanban__column">
				<span class="c-kanban__color-bar {$COLUMN['colorBg']}"></span>
				<div class="c-kanban__header active w-100 text-center {if isset($COLUMN['class'])}{$COLUMN['class']}{/if}">
					{if !empty($COLUMN['image'])}
						<img class="c-user-image" src="{$COLUMN['image']}">
					{elseif !empty($COLUMN['icon'])}
						<span class="{$COLUMN['icon']} mr-2"></span>
					{/if}
					{if !$COLUMN['isEditable']}
						<span class="fas fa-ban text-danger mr-3 js-popover-tooltip"
							data-content="{\App\Language::translate('LBL_OPERATION_NOT_PERMITTED')}" data-js="popover"></span>
					{/if}
					{$COLUMN['label']}
					<span class="ml-1 badge badge-secondary">{$DATA['columnCounter'][$COLUMN_NAME]}</span>
					{if !empty($COLUMN['description'])}
						<span class="c-kanban__info js-popover-tooltip float-right"
							data-content="{\App\Purifier::encodeHtml($COLUMN['description'])}" data-placement="bottom"
							data-js="popover">
							<span class="fas fa-info-circle"></span>
						</span>
					{/if}
				</div>
				{if isset($DATA['sum'][$COLUMN_NAME])}
					<div class="c-kanban__sum w-100">
						{foreach key=FIELD_NAME item=VALUE from=$DATA['sum'][$COLUMN_NAME]}
							{assign var=FIELD_MODEL value=$MODULE_MODEL->getFieldByName($FIELD_NAME)}
							{assign var=ICON value=$FIELD_MODEL->getIcon('Kanban')}
							{if isset($ICON['name'])}<span class="{$ICON['name']} mr-2"></span>{/if}
							{$FIELD_MODEL->getFullLabelTranslation()}: {$FIELD_MODEL->getDisplayValue($VALUE)}<br />
						{/foreach}
					</div>
				{/if}
				<div class="c-kanban__records {if $COLUMN['isEditable']}js-kanban-records{/if}"
					data-field="{$ACTIVE_FIELD->getName()}" data-value="{$COLUMN_NAME}" data-js="container">
					{if !empty($DATA['records'][$COLUMN_NAME])}
						{foreach key=RECORD_ID item=RECORD from=$DATA['records'][$COLUMN_NAME]}
							{assign var=RECORD_IS_EDITABLE value=$RECORD->isEditable()}
							<div class="c-kanban__record js-kanban-record {if $COLUMN['isEditable'] && $RECORD_IS_EDITABLE}u-cursor-move{else}js-kanban-disabled u-cursor-no-move{/if} mt-2"
								data-id="{$RECORD_ID}" data-js="sortable">
								<div class="card {$COLUMN['colorBr']}">
									<div class="card-body px-2 py-0">
										<p class="card-text js-popover-tooltip--record" href="{$RECORD->getDetailViewUrl()}">
											{foreach item=NAME from=$ACTIVE_BOARD['detail_fields']}
												{assign var=VALUE value=$RECORD->getDisplayValue($NAME, false, false, 30)}
												{if $NAME !== 'assigned_user_id' && $VALUE}
													{assign var=FIELD_MODEL value=$MODULE_MODEL->getFieldByName($NAME)}
													{assign var=ICON value=$FIELD_MODEL->getIcon('Kanban')}
													{if isset($ICON['name'])}<span class="{$ICON['name']} mr-1"></span>{/if}
													{$VALUE}<br />
												{/if}
											{/foreach}
										</p>
									</div>
									<div class="card-footer p-1 text-right">
										<div class="float-left pr-1 btns">
											{if $MODULE_MODEL->isSummaryViewSupported()}
												<button type="button" role="button"
													class="btn btn-xs btn-light js-popover-tooltip js-show-modal"
													data-url="index.php?module={$MODULE_NAME}&view=QuickDetailModal&record={$RECORD_ID}"
													data-content="{\App\Language::translate('LBL_SHOW_QUICK_DETAILS')}"
													data-js="popover|click" data-placement="bottom">
													<span class="far fa-caret-square-right u-fs-xs"></span>
												</button>
											{/if}
											<a class="btn btn-xs btn-light js-popover-tooltip" href="{$RECORD->getDetailViewUrl()}"
												data-content="{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS')}" data-js="popover"
												data-placement="bottom">
												<span class="fas fa-th-list u-fs-xs"></span>
											</a>
											{if $COLUMN['isEditable'] && $RECORD_IS_EDITABLE}
												<a class="btn btn-xs btn-light js-popover-tooltip{if $MODULE_MODEL->isQuickCreateSupported()} js-quick-edit-modal{/if}"
													href="{$RECORD->getEditViewUrl()}"
													data-content="{\App\Language::translate('BTN_RECORD_EDIT')}"
													data-module="{$MODULE_NAME}" data-record="{$RECORD_ID}" data-js="popover|click"
													data-placement="bottom">
													<span class="yfi yfi-full-editing-view u-fs-xs"></span>
												</a>
											{/if}
										</div>
										{if in_array('assigned_user_id',$ACTIVE_BOARD['detail_fields'])}
											<span class="text-right c-kanban__record__user">
												{if \App\Fields\Owner::getType($RECORD->get('assigned_user_id')) === 'Users'}
													{assign var=ICON value=\App\User::getImageById($RECORD->get('assigned_user_id'))}
													{if !empty($ICON['url'])}
														<img class="c-user-image ml-1 mr-0" src="{$ICON['url']}">
													{else}
														<span class="fas fa-user ml-1"></span>
													{/if}
												{else}
													<span class="adminIcon-groups ml-1"></span>
												{/if}
												<span class="u-text-ellipsis"
													title="{\App\Purifier::encodeHtml($RECORD->getDisplayValue('assigned_user_id'))}">{$RECORD->getDisplayValue('assigned_user_id')}
												</span>
											</span>
										{/if}
									</div>
								</div>
							</div>
						{/foreach}
					{/if}
				</div>
			</div>
		{/foreach}
	</div>
	<!-- tpl-Base-Kanban-Kanban -->
{/strip}
