{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal fade bs-example-modal-lg" role="dialog" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">{\App\Language::translate('LBL_VIEW_DETAIL', $MODULE)} - {$RECORD->getName()}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true" title="{\App\Language::translate('LBL_CLOSE')}">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					{include file=\App\Layout::getTemplatePath('DetailViewBlockView.tpl', $MODULE_NAME) RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}
				</div>
				<div class="modal-footer">
					<div class="float-right cancelLinkContainer" style="margin-top:0px;">
						<button type="button" class="btn btn-light" data-dismiss="modal">{\App\Language::translate('LBL_CANCEL', $MODULE)}</button>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
