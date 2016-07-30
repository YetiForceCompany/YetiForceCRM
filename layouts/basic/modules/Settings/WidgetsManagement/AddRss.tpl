{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div id="addRssWidgetContainer" class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">&times;</button>
					<button type="button" class="btn btn-primary addChannel pull-right marginRight10">{vtranslate('LBL_ADD_CHANNEL', $MODULE)}</button>
					<h3 id="massEditHeader" class="modal-title">{vtranslate('LBL_ADD_RSS', $MODULE)}</h3>
				</div>
				<form class="form-horizontal validateForm" >
					<input type="hidden" name="module" value="{$MODULE_NAME}">
					<input type="hidden" name="action" value="addWidget">
					<input type="hidden" name="parent" value="Settings">
					<input type="hidden" name="blockid">
					<input type="hidden" name="linkid">
					<input type="hidden" name="width" value="4">
					<input type="hidden" name="height" value="4">
					<div class="formContainer">
						<div class="form-group margin0px padding1per">
							<label class="col-sm-4 control-label">{vtranslate('LBL_TITLE_WIDGET', $MODULE)}<span class="redColor">*</span> </label>
							<div class="col-sm-8 controls">
								<input type="text" name="title" class="form-control" data-validation-engine="validate[required]" />
							</div>
						</div>
						<div class="form-group margin0px padding1per">
							<label class="col-sm-4 control-label">{vtranslate('LBL_ADDRESS_RSS', $MODULE)}<span class="redColor">*</span> </label>
							<div class="col-sm-8 controls">	
								<div class="input-group">
									<input type="text" class="form-control channelRss" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-validator='[ { "name":"Url" } ]'  />
									<span class="input-group-btn">
										<button class="removeChannel btn btn-default" type="button"><span class="glyphicon glyphicon-remove"></span></button>
									</span>
								</div>
							</div>
						</div>
						<div class="form-group margin0px padding1per newChannel hide">
							<label class="col-sm-4 control-label">{vtranslate('LBL_ADDRESS_RSS', $MODULE)}<span class="redColor">*</span> </label>
							<div class="col-sm-8 controls">	
								<div class="input-group">
									<input type="text" disabled="disabled" class="form-control channelRss" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-validator='[ { "name":"Url" } ]' />
									<span class="input-group-btn">
										<button class="removeChannel btn btn-default" type="button"><span class="glyphicon glyphicon-remove"></span></button>
									</span>
								</div>
							</div>
						</div>
					</div>
					{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
				</form>

			</div>
		</div>
	</div>
{/strip}
