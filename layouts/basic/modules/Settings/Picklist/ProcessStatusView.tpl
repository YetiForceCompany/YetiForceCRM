{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Picklist-ProcessStatusView -->
	{assign var=OLD_VALUE value=\App\Purifier::encodeHtml($PICKLIST_VALUE['picklistValue'])}
	<div class='modelContainer modal fade' tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						{\App\Language::translate('LBL_EDIT_PROCESS_STATUS', $QUALIFIED_MODULE)}
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
					<input type="hidden" name="pickListValues"
						   value='{\App\Purifier::encodeHtml(\App\Json::encode(App\Fields\Picklist::getEditablePicklistValues($FIELD_MODEL->getName())))}'/>
					<div class="modal-body tabbable">
						<div class="form-group row align-items-center">
							<div class="col-md-3 col-form-label text-right">
								{\App\Language::translate('LBL_TIME_COUNTING',$QUALIFIED_MODULE)}
								<div class="js-popover-tooltip ml-2 d-inline my-auto u-h-fit u-cursor-pointer" data-js="popover"
									data-content="{\App\Language::translate('LBL_TIME_COUNTING_INFO',$QUALIFIED_MODULE)}">
									<span class="fas fa-info-circle"></span>
								</div>
							</div>
							<div class="col-md-9 controls">
								<select class="select2 form-control" multiple name="time_counting[]">
									<option value="1"{if in_array('1',$PICKLIST_VALUE['time_counting'])} selected="selected"{/if}>{\App\Language::translate('LBL_TIME_COUNTING_REACTION',$QUALIFIED_MODULE)}</option>
									<option value="2"{if in_array('2',$PICKLIST_VALUE['time_counting'])} selected="selected"{/if}>{\App\Language::translate('LBL_TIME_COUNTING_RESOLVE',$QUALIFIED_MODULE)}</option>
									<option value="3"{if in_array('3',$PICKLIST_VALUE['time_counting'])} selected="selected"{/if}>{\App\Language::translate('LBL_TIME_COUNTING_IDLE',$QUALIFIED_MODULE)}</option>
								</select>
							</div>
						</div>
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
									<input class="form-control" type="checkbox" value="1"
										   {if $PICKLIST_VALUE['close_state']}checked="checked"{/if}
										   name="close_state">
								</div>
							</div>
						{/if}
						{if $FIELD_MODEL->getFieldDataType() eq 'picklist' }
							<div class="form-group row align-items-center">
								<div class="col-md-3 col-form-label text-right">
									{\App\Language::translate('LBL_AUTOMATION',$QUALIFIED_MODULE)}
									<div class="js-popover-tooltip ml-2 d-inline my-auto u-h-fit u-cursor-pointer" data-js="popover"
										data-content="{\App\Language::translate('LBL_AUTOMATION_INFO',$QUALIFIED_MODULE)}">
										<span class="fas fa-info-circle"></span>
									</div>
								</div>
								<div class="col-md-9 controls">
								<select class="select2 form-control" name="automation">
									<option value=""></option>
										{foreach item=$VALUE key=$KEY from=Settings_Picklist_Module_Model::getAutomationStatus()}
									<option value="{$KEY}"
									{if isset($PICKLIST_VALUE['automation']) && $PICKLIST_VALUE['automation'] === $KEY} selected
									{elseif $KEY === Settings_Picklist_Module_Model::AUTOMATION_NO_CONCERN}selected {/if}
									>{\App\Language::translate($VALUE,$QUALIFIED_MODULE)}</option>
								{/foreach}
								</select>
								</div>
							</div>
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
