{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<select class="select2 taxMode" title="{vtranslate('LBL_TAX_MODE', $SUPMODULE)}" name="{$FIELD->getColumnName()}{$ROW_NO}" {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if}>
		<option value="0" {if $SUP_VALUE == '0'}selected{/if}>{vtranslate('LBL_GROUP', $SUPMODULE)}</option>
		<option value="1" {if $SUP_VALUE == '1'}selected{/if}>{vtranslate('LBL_INDIVIDUAL', $SUPMODULE)}</option>
	</select>
{/strip}
