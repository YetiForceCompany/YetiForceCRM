{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="validationEngineContainer">
		<div class="modal-header row no-margin">
			<div class="col-xs-12 paddingLRZero">
				<div class="col-xs-8 paddingLRZero">
					<h4>{vtranslate('LBL_TITLE_ADDED', $QUALIFIED_MODULE)}</h4>
				</div>
				<div class="pull-right">
					<button class="btn btn-warning marginLeft10" type="button" data-dismiss="modal" aria-label="Close" aria-hidden="true">&times;</button>
				</div>
			</div>
		</div>
		<div class="modal-body row">
			<div class="col-xs-12 form-horizontal">
				<div class="form-group">
					<div class="col-sm-3 control-label">
						<label>{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}</label>
					</div>
					<div class="col-sm-8">
						<input name="name" value="{$RECORD->getName()}" data-validation-engine="validate[required]" class="form-control"> 
					</div>
				</div>
			</div>
		</div>
		{include file='ModalFooter.tpl'|@vtemplate_path}
	</div>
{/strip}
