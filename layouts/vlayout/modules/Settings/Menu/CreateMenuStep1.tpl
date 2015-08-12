{*/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/*}
<div class="modal fade" tabindex="-1">
	<div class="modal-dialog">
        <div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">{vtranslate('LBL_CREATING_MENU', $QUALIFIED_MODULE)}</h4>
			</div>
			<div class="modal-body">
				{assign var=MENU_TYPES value=$MODULE_MODEL->getMenuTypes()}
				<input type="hidden" id="mode" value="step1" />
				<div class="row">
					<div class="col-md-5 marginLeftZero">{vtranslate('LBL_SELECT_TYPE_OF_MENU', $QUALIFIED_MODULE)}:</div>
					<div class="col-md-7">
						<select name="type" class="select2 form-control type">
							{foreach from=$MENU_TYPES item=ITEM key=KEY}
								<option value="{$KEY}">{vtranslate('LBL_'|cat:strtoupper($ITEM), $QUALIFIED_MODULE)}</option>
							{/foreach}
						</select>
					</div>
				</div>
				<br />
				<div class="well well-small" style="margin-bottom: 0;max-height: 280px;overflow-y: scroll;">
					{foreach from=$MENU_TYPES item=ITEM key=KEY}
						<h5>{vtranslate('LBL_'|cat:strtoupper($ITEM), $QUALIFIED_MODULE)}</h5>
						<p>{vtranslate('LBL_'|cat:strtoupper($ITEM)|cat:'_DESC', $QUALIFIED_MODULE)}</p>
					{/foreach}
				</div>
			</div>
			<div class="modal-footer">
				<div class="pull-right cancelLinkContainer" style="margin-top:0px;">
					<button class="btn btn-success nextButton" type="submit"><strong>{vtranslate('LBL_NEXT', $QUALIFIED_MODULE)}</strong></button>
					<button class="btn cancelLink btn-warning" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
				</div>
			</div>
		</div>
	</div>
</div>
