{*<!--
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
-->*}
{strip}
	<div class="modal-header">
		<button class="close" data-dismiss="modal" title="{vtranslate('LBL_CLOSE')}">x</button>
		<h3 class="modal-title">{vtranslate('LBL_GENERATE_RECORD_FOR_MODULE', $QUALIFIED_MODULE)}</h3>
	</div>
	<div class="modal-body">
		<div class="btn-toolbar">
			{foreach item=TEMPLATE from=$TEMPLATES}
				<a class="btn" href="{$TEMPLATE->getGeneratingEditView()|cat:$RECORD}">{vtranslate($TEMPLATE->getRelatedName(), $TEMPLATE->getRelatedName())}
					<img class="image-in-button" src="{vimage_path($TEMPLATE->getRelatedName()|cat:'48.png'}">
				</a>
			{/foreach}
		</div>
	</div>
	<div class="modal-footer">
		<div class="pull-right">
			<button type="button" class="btn btn-warning dismiss" data-dismiss="modal">{vtranslate('LBL_CLOSE', $MODULE_NAME)}</button>
		</div>
	</div>
{/strip}
