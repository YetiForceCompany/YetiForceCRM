{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="massEditHeader" class="modal-title">{vtranslate('LBL_PDF_EXPORT', $PDF_MODULE)}</h3>
		{foreach from=$TEMPLATES item=TEMPLATE}
			<a href="index.php?parent=Settings&module=PDF&action=Export{$EXPORT_VARS}&template={$TEMPLATE['id']}" class="btn btn-success">{vtranslate('LBL_EXPORT', $PDF_MODULE)} {$TEMPLATE['primary_name']}</a><br />
		{/foreach}
	</div>
	{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
{/strip}
