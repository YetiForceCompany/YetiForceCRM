{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	<select class="select2 taxMode" title="{\App\Language::translate('LBL_TAX_MODE', $MODULE)}" name="{$FIELD->getColumnName()}" {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if}>
		<option value="0" {if $ITEM_VALUE == '0'}selected{/if}>{\App\Language::translate('LBL_GROUP', $MODULE)}</option>
		<option value="1" {if $ITEM_VALUE == '1'}selected{/if}>{\App\Language::translate('LBL_INDIVIDUAL', $MODULE)}</option>
	</select>
{/strip}
