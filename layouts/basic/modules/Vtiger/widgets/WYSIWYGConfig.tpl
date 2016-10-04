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
{strip}
<div class="modal fade" tabindex="-1">
	<div class="modal-dialog">
        <div class="modal-content">
			<form class="form-modalAddWidget">
				<input type="hidden" name="wid" value="{$WID}">
				<input type="hidden" name="type" value="{$TYPE}">
				<div class="modal-header">
					<button type="button" data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE', $QUALIFIED_MODULE)}">×</button>
					<h3 id="massEditHeader" class="modal-title">{vtranslate('Add widget', $QUALIFIED_MODULE)}</h3>
				</div>
				<div class="modal-body">
					<div class="modal-Fields">
						<div class="form-horizontal">
							<div class="form-group">
								<div class="col-md-4"><strong>{vtranslate('Type widget', $QUALIFIED_MODULE)}</strong>:</div>
							<div class="col-md-7">
								{vtranslate($TYPE, $QUALIFIED_MODULE)}
							</div>
							</div>
							<div class="form-group">
								<div class="col-md-4"><label class="control-label">{vtranslate('Label', $QUALIFIED_MODULE)}:</label></div>
								<div class="col-md-7"><input name="label" class="form-control" type="text" value="{$WIDGETINFO['label']}" /></div>
							</div>
							<div class="form-group">
								<div class="col-md-4"><label class="control-label">{vtranslate('No left margin', $QUALIFIED_MODULE)}:</label></div>
								<div class="col-md-7">
									<input name="nomargin" class="" type="checkbox" value="1" {if $WIDGETINFO['nomargin'] == 1}checked{/if}/>
									<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{vtranslate('No left margin info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('No left margin', $QUALIFIED_MODULE)}"><i class="glyphicon glyphicon-info-sign"></i></a>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-4"><label class="control-label">{vtranslate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}:</label></div>
								<div class="col-md-7">
									<select name="field_name" class="select2 form-control">
										{foreach from=$MODULE_MODEL->getWYSIWYGFields($SOURCE,$SOURCEMODULE) item=item key=key}
											<option {if $WIDGETINFO['data']['field_name'] == $key}selected{/if} value="{$key}">{$item}</option>
										{/foreach}
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
			</form>
		</div>
	</div>
</div>
{/strip}
