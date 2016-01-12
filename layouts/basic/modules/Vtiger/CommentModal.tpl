{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
<div class="commentModal" tabindex="-1">
	<div  class="modal fade commentModalContent ">
		<div class="modal-dialog ">
			<div class="modal-content">
				<div class="modal-header">
					<div class="row no-margin">
						<div class="col-md-7 col-xs-10">
							<h3 class="modal-title">{vtranslate('LBL_COMMENTS', $MODULE_NAME)}</h3>
						</div>
						<div class="pull-right">
							<div class="pull-right">
								<button class="btn btn-warning " type="button" data-dismiss="modal" aria-label="Close" aria-hidden="true">&times;</button>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-body row ">
					{if $COMMENTS_MODULE_MODEL->isPermitted('EditView')}
						<div class="addCommentBlock row no-margin col-xs-12">
							<div class="row reason hide">
								<div class="col-md-12 marginTop10">
									{vtranslate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}
								</div>
								<div class="col-md-12 marginTop10 marginBottom10px">
									<input type="text" name="reasonToEdit" title="{vtranslate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}" placeholder="{vtranslate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}" class="input-block-level form-control"/>
								</div>
							</div>
							<div class="col-md-12 marginTop10 paddingLRZero marginBottom10px">
								{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}
							</div>
							<textarea type="text"  name="commentcontent" class="commentcontent form-control col-md-12 ckEditorSource nameField" title="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}">
							</textarea>
							<div class="pull-right paddingTop10">
								<button class="btn btn-success modalSaveComment" type="button" data-mode="add">
									<strong>{vtranslate('LBL_POST', $MODULE_NAME)}</strong>
								</button>
							</div>
						</div>
					{/if}
				</div>
			</div>
		</div>
	</div>
</div>
{/strip}
