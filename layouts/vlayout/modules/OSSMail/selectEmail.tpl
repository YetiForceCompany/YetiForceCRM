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
<div id="sendEmailContainer" class="modelContainer modal fade" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h3 class="modal-title" id="myModalLabel">{vtranslate('LBL_SELECT_EMAIL_IDS', 'Emails')}</h3>
			</div>
			<div class="modal-body">
				<div class="padding20">
					<h4>{vtranslate('LBL_MUTIPLE_EMAIL_SELECT_ONE', 'Vtiger')}</h4>
				</div>
				<div class="modal-Fields">
					{foreach from=$RESP item=item key=key}
						<div class="form-group">
							<label class="radio">
								<div class="row">
									<div class="col-md-3"><input style="float: right;" type="radio" name="selectedFields" value="{$item.email}"></div>
									<div class="col-md-3">{$item.fieldlabel}:</div>
									<div class="col-md-6">{$item.email}</div>
								</div>
							</label>
						</div>
					{/foreach}
				</div>
			</div>	
			<div class="modal-footer">
			<button class="btn btn-default" id="closeModal" data-dismiss="modal" aria-hidden="true">{vtranslate('LBL_CANCEL', 'Vtiger')}</button>
			<button class="btn btn-default addButton" id="selectEmail" data-dismiss="modal" aria-hidden="true" >{vtranslate('LBL_SELECT', 'Vtiger')}</button>
			</div>
		</div>
	</div>
</div>
