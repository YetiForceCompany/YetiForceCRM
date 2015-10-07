{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal-footer">
		<a id="generate_pdf" href="" target="_blank" data-url="index.php?parent=Settings&module=PDF&action=Export{$EXPORT_VARS}&template=" class="btn btn-success">
			{vtranslate('LBL_GENERATE', $QUALIFIED_MODULE)}
		</a> 
		<button class="btn btn-warning" type="reset" data-dismiss="modal"><strong>{vtranslate('LBL_CANCEL', $MODULE)}</strong></button>
	</div>
{/strip}
