{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div id="transferOwnershipContainer" class='modelContainer modal fade' tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 id="massEditHeader" class="modal-title">{\App\Language::translate('LBL_ChangeType', $MODULE)}</h5>
					<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="alert alert-block alert-warning u-m-5px">
					<button type="button" class="close" data-dismiss="alert">×</button>
					<p>{\App\Language::translate('Alert_ChangeType_desc', $MODULE)}</p>
				</div>
				<form class="form-horizontal" id="ChangeType" name="ChangeType" method="post" action="index.php">
					<div class="modal-body tabbable">
						<div class="form-group">
							<label class="col-md-3 col-form-label">{\App\Language::translate('LBL_SELECT_TYPE',$MODULE)}</label>
							<div class="col-md-6 controls">
								<select class="select2-container columnsSelect form-control" id="mail_type" name="mail_type">
									{foreach key=key item=item from=$TYPE_LIST}
										<option value="{$key}">{\App\Language::translate( $item,$MODULE )}</option>
									{/foreach}
								</select>
							</div></br>
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
{/strip}
