{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="input-group input-group-sm">
		<input name="tax{$ROW_NO}" value="{$FIELD->getEditValue($VALUE)}" type="text" class="tax form-control input-sm" readonly="readonly"/>
		<span class="input-group-addon cursorPointer changeTax">
			<img src="{vimage_path('Tax24.png')}" alt="{vtranslate('LBL_TAX', $SUP_VALUE)}" />
		</span>
	</div>
{/strip}
