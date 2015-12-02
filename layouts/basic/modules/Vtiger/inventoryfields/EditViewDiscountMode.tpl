{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<select class="select2 discountMode" title="{vtranslate('LBL_TAX_MODE', $MODULE)}" name="{$FIELD->getColumnName()}{$ROW_NO}" {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if}>
		<option value="0" {if $ITEM_VALUE == '0'}selected{/if}>{vtranslate('LBL_GROUP', $MODULE)}</option>
		<option value="1" {if $ITEM_VALUE == '1'}selected{/if}>{vtranslate('LBL_INDIVIDUAL', $MODULE)}</option>
	</select>
{/strip}
