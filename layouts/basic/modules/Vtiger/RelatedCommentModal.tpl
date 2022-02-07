{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-RelatedCommentModal -->
	<div class="modal-header">
		<h5 class="modal-title">{\App\Language::translate('LBL_EDIT_RELATED_COMMENT', $MODULE)}</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<div class="modal-body">
		<input type="hidden" class="relatedRecord" value="{$RELATED_RECORD}" />
		<input type="hidden" class="relatedModuleName" value="{$RELATED_MODULE}" />
		<textarea class="form-control comment" rows="4">{$COMMENT}</textarea>
	</div>
	<div class="modal-footer">
		<div class="float-right">
			<button class="btn btn-success mr-1" type="submit" name="saveButton">
				<strong>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong></button>
			<button class="btn btn-danger" type="reset" data-dismiss="modal">
				<strong>{\App\Language::translate('LBL_CANCEL', $MODULE)}</strong></button>
		</div>
	</div>
	<!-- /tpl-Base-RelatedCommentModal -->
{/strip}
