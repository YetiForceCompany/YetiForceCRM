{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
<div class="modal fade bs-example-modal-lg" role="dialog" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header contentsBackground">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 class="modal-title">{\App\Language::translate('LBL_VIEW_DETAIL', $MODULE)} - {$RECORD->getName()}</h3>
			</div>
			<div class="modal-body">
				{include file='DetailViewBlockView.tpl'|@vtemplate_path:$MODULE_NAME RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}
			</div>
			<div class="modal-footer">
				<div class="pull-right cancelLinkContainer" style="margin-top:0px;">
					<button type="button" class="btn btn-default" data-dismiss="modal">{\App\Language::translate('LBL_CANCEL', $MODULE)}</button>
				</div>
			</div>
		</div>
	</div>
</div>
{/strip}
