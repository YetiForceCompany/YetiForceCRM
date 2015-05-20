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
			<div class="row-fluid">
				<div class="span5 marginLeftZero">{vtranslate('Type widget', $QUALIFIED_MODULE)}:</div>
				<div class="span7">
					{vtranslate($TYPE, $QUALIFIED_MODULE)}
				</div>
				<div class="span5 marginLeftZero"><label class="">{vtranslate('Label', $QUALIFIED_MODULE)}:</label></div>
				<div class="span7"><input name="label" class="span3" type="text" value="{$WIDGETINFO['label']}" /></div>
				<div class="span5 marginLeftZero"><label class="">{vtranslate('No left margin', $QUALIFIED_MODULE)}:</label></div>
				<div class="span7">
					<input name="nomargin" class="span3" type="checkbox" value="1" {if $WIDGETINFO['nomargin'] == 1}checked{/if}/>
					<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('No left margin info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('No left margin', $QUALIFIED_MODULE)}"><i class="icon-info-sign"></i></a>
				</div>
				<div class="span5 marginLeftZero"><label class="">{vtranslate('Limit entries', $QUALIFIED_MODULE)}:</label></div>
				<div class="span7">
					<input name="limit" class="span3" type="text" value="{$WIDGETINFO['data']['limit']}"/>
					<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('Limit entries info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('Limit entries', $QUALIFIED_MODULE)}"><i class="icon-info-sign"></i></a>
				</div>
				<div class="span5 marginLeftZero">{vtranslate('Related module', $QUALIFIED_MODULE)}:</div>
				<div class="span7">
					<select name="relatedmodule" class="select2 span3 marginLeftZero">
						{foreach from=$RELATEDMODULES item=item key=key}
							<option value="{$item['related_tabid']}" {if $WIDGETINFO['data']['relatedmodule'] == $item['related_tabid']}selected{/if} >{vtranslate($item['label'], $item['name'])}</option>
						{/foreach}
					</select>
					<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('Related module info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('Related module', $QUALIFIED_MODULE)}"><i class="icon-info-sign"></i></a>
				</div>
				<div class="span5 marginLeftZero">{vtranslate('Columns', $QUALIFIED_MODULE)}:</div>
				<div class="span7">
					<select name="columns" class="select2 span3 marginLeftZero">
						{foreach from=$MODULE_MODEL->getColumns() item=item key=key}
							<option value="{$item}" {if $WIDGETINFO['data']['columns'] == $item}selected{/if} >{$item}</option>
						{/foreach}
					</select>
					<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('Columns info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('Columns', $QUALIFIED_MODULE)}"><i class="icon-info-sign"></i></a>
				</div>
				<div class="span5 marginLeftZero"><label class="">{vtranslate('Add button', $QUALIFIED_MODULE)}:</label></div>
				<div class="span7">
					<input name="action" class="span3" type="checkbox" value="1" {if $WIDGETINFO['data']['action'] == 1}checked{/if}/>
					<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('Add button info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('Add button', $QUALIFIED_MODULE)}"><i class="icon-info-sign"></i></a>
				</div>
				<div class="span5 marginLeftZero"><label class="">{vtranslate('Select button', $QUALIFIED_MODULE)}:</label></div>
				<div class="span7">
					<input name="actionSelect" class="span3" type="checkbox" value="1" {if $WIDGETINFO['data']['actionSelect'] == 1}checked{/if}/>
					<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('Select button info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('Select button', $QUALIFIED_MODULE)}"><i class="icon-info-sign"></i></a>
				</div>
				<div class="span5 marginLeftZero"><label class="">{vtranslate('No message', $QUALIFIED_MODULE)}:</label></div>
				<div class="span7">
					<input name="no_result_text" class="span3" type="checkbox" value="1" {if $WIDGETINFO['data']['no_result_text'] == 1}checked{/if}/>
					<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('No message info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('No message', $QUALIFIED_MODULE)}"><i class="icon-info-sign"></i></a>
				</div>
				<div class="span5 marginLeftZero">{vtranslate('Filter', $QUALIFIED_MODULE)}:</div>
				<div class="span7">
					<input type="hidden" name="filter_selected" value="{$WIDGETINFO['data']['filter']}">
					<select name="filter" class="select2 span3 marginLeftZero">
						<option value="-">{vtranslate('None', $QUALIFIED_MODULE)}</option>
					</select>
					<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('Filter info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('Filter', $QUALIFIED_MODULE)}"><i class="icon-info-sign"></i></a>
				</div>
				<div class="span5 marginLeftZero">{vtranslate('Switch', $QUALIFIED_MODULE)}:</div>
				<div class="span7">
					<input type="hidden" name="checkbox_selected" value="{$WIDGETINFO['data']['checkbox']}">
					<select name="checkbox" class="select2 span3 marginLeftZero">
						<option value="-">{vtranslate('None', $QUALIFIED_MODULE)}</option>
					</select>
					<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('Switch info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('Switch', $QUALIFIED_MODULE)}"><i class="icon-info-sign"></i></a>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn btn-success saveButton" data-dismiss="modal" aria-hidden="true" >{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
	</div>
</form>
