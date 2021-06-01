{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Picklist-ProcessStatusView -->
	{assign var=OLD_VALUE value=\App\Purifier::encodeHtml($PICKLIST_VALUE['picklistValue'])}
	<div class='modelContainer modal fade' tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						{\App\Language::translate('LBL_EDIT_PROCESS_LOCK_STATUS', $QUALIFIED_MODULE)}
						: {\App\Language::translate($PICKLIST_VALUE['picklistValue'], $SOURCE_MODULE)}
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form id="processStatusItemForm" class="form-horizontal" method="post" action="index.php">
					<input type="hidden" name="module" value="{$MODULE}"/>
					<input type="hidden" name="parent" value="Settings"/>
					<input type="hidden" name="source_module" value="{$SOURCE_MODULE}"/>
					<input type="hidden" name="action" value="SaveAjax"/>
					<input type="hidden" name="mode" value="processStatus"/>
					<input type="hidden" name="picklistName" value="{$FIELD_MODEL->getName()}"/>
					<input type="hidden" name="oldValue" value="{$OLD_VALUE}"/>
					<input type="hidden" name="primaryKeyId" value="{$PICKLIST_VALUE['picklistValueId']}"/>
					<input type="hidden" name="picklist_valueid" value="{$PICKLIST_VALUE['picklist_valueid']}"/>
					<input type="hidden" name="pickListValues" value='{\App\Purifier::encodeHtml(\App\Json::encode(App\Fields\Picklist::getEditableValues($FIELD_MODEL->getName())))}'/>
					<div class="modal-body tabbable">
						{if $FIELD_MODEL->get('uitype') === 15}
							<div class="form-group row align-items-center">
								<div class="col-md-3 col-form-label text-right">
									{\App\Language::translate('LBL_CLOSES_RECORD',$QUALIFIED_MODULE)}
									<div class="js-popover-tooltip ml-2 d-inline my-auto u-h-fit u-cursor-pointer" data-js="popover"
										data-content="{\App\Language::translate('LBL_BLOCKED_RECORD_INFO',$QUALIFIED_MODULE)}">
										<span class="fas fa-info-circle"></span>
									</div>
								</div>
								<div class="col-md-9 controls">
									<input class="form-control" type="checkbox" value="1" {if $PICKLIST_VALUE['close_state']}checked="checked"{/if} name="close_state">
								</div>
							</div>
						{/if}
						{if $FIELD_MODEL->isProcessStatusField()}
							{if isset($PICKLIST_VALUE['time_counting'])}
								<div class="form-group row align-items-center">
									<div class="col-md-3 col-form-label text-right">
										{\App\Language::translate('LBL_TIME_COUNTING',$QUALIFIED_MODULE)}
										<div class="js-popover-tooltip ml-2 d-inline my-auto u-h-fit u-cursor-pointer" data-js="popover"
											data-content="{\App\Language::translate('LBL_TIME_COUNTING_INFO',$QUALIFIED_MODULE)}">
											<span class="fas fa-info-circle"></span>
										</div>
									</div>
									<div class="col-md-9 controls">
										<select class="select2 form-control" name="time_counting">
											<option value="0"{if 0===$PICKLIST_VALUE['time_counting']} selected="selected"{/if}>{\App\Language::translate('LBL_NONE','_Base')}</option>
											<option value="{\App\RecordStatus::TIME_COUNTING_REACTION}"{if \App\RecordStatus::TIME_COUNTING_REACTION===$PICKLIST_VALUE['time_counting']} selected="selected"{/if}>{\App\Language::translate('LBL_TIME_COUNTING_REACTION',$QUALIFIED_MODULE)}</option>
											<option value="{\App\RecordStatus::TIME_COUNTING_RESOLVE}"{if \App\RecordStatus::TIME_COUNTING_RESOLVE===$PICKLIST_VALUE['time_counting']} selected="selected"{/if}>{\App\Language::translate('LBL_TIME_COUNTING_RESOLVE',$QUALIFIED_MODULE)}</option>
											<option value="{\App\RecordStatus::TIME_COUNTING_IDLE}"{if \App\RecordStatus::TIME_COUNTING_IDLE===$PICKLIST_VALUE['time_counting']} selected="selected"{/if}>{\App\Language::translate('LBL_TIME_COUNTING_IDLE',$QUALIFIED_MODULE)}</option>
										</select>
									</div>
								</div>
							{/if}
							{if $FIELD_MODEL->getFieldDataType() eq 'picklist' }
								<div class="form-group row align-items-center">
									<div class="col-md-3 col-form-label text-right">
										{\App\Language::translate('LBL_RECORD_STATE',$QUALIFIED_MODULE)}
										<div class="js-popover-tooltip ml-2 d-inline my-auto u-h-fit u-cursor-pointer" data-js="popover"
											data-content="{\App\Language::translate('LBL_RECORD_STATE_INFO',$QUALIFIED_MODULE)}">
											<span class="fas fa-info-circle"></span>
										</div>
									</div>
									<div class="col-md-9 controls">
									<select class="select2 form-control" name="record_state">
										<option value=""></option>
											{foreach item=$VALUE key=$KEY from=\App\RecordStatus::getLabels()}
										<option value="{$KEY}"
										{if isset($PICKLIST_VALUE['record_state']) && $PICKLIST_VALUE['record_state'] === $KEY} selected
										{elseif $KEY === \App\RecordStatus::RECORD_STATE_NO_CONCERN}selected {/if}
										>{\App\Language::translate($VALUE,$QUALIFIED_MODULE)}</option>
									{/foreach}
									</select>
									</div>
								</div>
							{/if}
						{/if}
					</div>
					{ASSIGN var=BTN_SUCCESS value='LBL_SAVE'}
					{include file=App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE) BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-Picklist-ProcessStatusView -->
{/strip}
