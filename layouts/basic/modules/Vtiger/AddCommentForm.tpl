{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	<!-- tpl-Base-AddCommentForm -->
	{* Change to this also refer: RecentComments.tpl *}
	{assign var="COMMENT_TEXTAREA_DEFAULT_ROWS" value="2"}
	<div id="add-comment__container" class="js-add-comment__container modal fade" tabindex="-1" role="dialog" data-js="hasClass">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header contentsBackground">
					<h5 class="modal-title">
						<i class="fa fa-comments"></i> {\App\Language::translate('LBL_MASS_ADD_COMMENT', $MODULE)}
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form class="form-horizontal" id="massSave" method="post" action="index.php">
					<input type="hidden" name="module" value="{$MODULE}"/>
					<input type="hidden" name="source_module" value="{$SOURCE_MODULE}"/>
					<input type="hidden" name="action" value="MassSaveAjax"/>
					<input type="hidden" name="viewname" value="{$CVID}"/>
					<input type="hidden" name="selected_ids" value="{\App\Purifier::encodeHtml(\App\Json::encode($SELECTED_IDS))}">
					<input type="hidden" name="excluded_ids" value="{\App\Purifier::encodeHtml(\App\Json::encode($EXCLUDED_IDS))}">
					<input type="hidden" name="search_key" value="{$SEARCH_KEY}"/>
					<input type="hidden" name="operator" value="{$OPERATOR}"/>
					<input type="hidden" name="search_value" value="{$ALPHABET_VALUE}"/>
					<input type="hidden" name="search_params" value="{\App\Purifier::encodeHtml(\App\Json::encode($SEARCH_PARAMS))}"/>
					<input type="hidden" name="entityState" value="{$ENTITY_STATE}"/>
					<div class="modal-body">
						<textarea name="commentcontent" class="c-textarea--completions" data-validation-engine="validate[required]"></textarea>
						<div contenteditable="true" class="form-control-lg form-control js-comment-content js-completions"
							 id="commentcontent" data-completions-textarea="true"
							 title="{\App\Language::translate('LBL_WRITE_YOUR_COMMENT_HERE', $MODULE)}"
							 placeholder="{\App\Language::translate('LBL_WRITE_YOUR_COMMENT_HERE', $MODULE)}..."
							 data-js="html | tribute.js"></div>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
	<!-- /tpl-Base-AddCommentForm -->
{/strip}
