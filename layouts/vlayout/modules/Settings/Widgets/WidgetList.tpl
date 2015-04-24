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
<form class="form-modalAddWidget" style="width: 400px;">  
	<div class="modal-header contentsBackground">
		<button type="button" data-dismiss="modal" class="close" title="Zamknij">×</button>
		<h3 id="massEditHeader">{vtranslate('Add widget', $QUALIFIED_MODULE)}</h3>
	</div>
	<div class="modal-body">
		<div class="modal-Fields">
			<div class="row-fluid">
				<div class="span4">{vtranslate('LBL_WIDGET_TYPE', $QUALIFIED_MODULE)}:</div>
				<div class="span8">
					<select name="type" class="select2 span3 marginLeftZero">
					{foreach from=$MODULE_MODEL->getType($SOUNRCE_MODULE) item=item key=key}
						<option value="{$key}" >{vtranslate($item, $QUALIFIED_MODULE)}</option>
					{/foreach}
					</select>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn btn-success saveButton" data-dismiss="modal" aria-hidden="true" >{vtranslate('LBL_SELECT', $QUALIFIED_MODULE)}</button>
	</div>
</form>
