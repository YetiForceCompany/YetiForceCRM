{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
<form class="form-modalAddWidget" style="width: 450px;">
	<input type="hidden" name="wid" value="{$WID}">
	<input type="hidden" name="type" value="{$TYPE}">
	<div class="modal-header contentsBackground">
		<button type="button" data-dismiss="modal" class="close" title="Zamknij">×</button>
		<h3 id="massEditHeader">{vtranslate('Add widget', $QUALIFIED_MODULE)}</h3>
	</div>
	<div class="modal-body">
		<div class="modal-Fields">
			<div class="row">
				<div class="col-md-5 marginLeftZero">{vtranslate('Type widget', $QUALIFIED_MODULE)}:</div>
				<div class="col-md-7">
					{vtranslate($TYPE, $QUALIFIED_MODULE)}
				</div>
				<div class="col-md-5 marginLeftZero"><label class="">{vtranslate('Label', $QUALIFIED_MODULE)}:</label></div>
				<div class="col-md-7"><input name="label" class="col-md-3" type="text" value="{$WIDGETINFO['label']}" /></div>
				<div class="col-md-5 marginLeftZero"><label class="">{vtranslate('No left margin', $QUALIFIED_MODULE)}:</label></div>
				<div class="col-md-7">
					<input name="nomargin" class="col-md-3" type="checkbox" value="1" {if $WIDGETINFO['nomargin'] == 1}checked{/if}/>
					<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('No left margin info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('No left margin', $QUALIFIED_MODULE)}"><i class="glyphicon glyphicon-info-sign"></i></a>
				</div>
				<div class="col-md-5 marginLeftZero"><label class="">{vtranslate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}:</label></div>
				<div class="col-md-7">
					<select name="FastEdit" multiple class="select2 col-md-3 marginLeftZero columnsSelect">
						<option value="-">{vtranslate('None', $QUALIFIED_MODULE)}</option>
						{assign var="FIELDS" value=$MODULE_MODEL->getFields( $SOURCE, array('15','16') ) }
						{foreach from=$FIELDS['labels'] item=item key=key}
							<option {if isset ($WIDGETINFO['data']['FastEdit']) && in_array($key, $WIDGETINFO['data']['FastEdit']) }selected{/if} value="{$key}">{$item}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn btn-success saveButton" data-dismiss="modal" aria-hidden="true" >{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
	</div>
</form>
