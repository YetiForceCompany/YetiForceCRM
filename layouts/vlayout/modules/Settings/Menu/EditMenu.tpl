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
				<h3 class="modal-title">{vtranslate('LBL_EDITION_MENU', $QUALIFIED_MODULE)}</h3>
			</div>
			<div class="modal-body">
				{assign var=MENU_TYPES value=$MODULE_MODEL->getMenuTypes()}
				{assign var=MENU_TYPE value=$MENU_TYPES[$RECORD->get('type')]}
				<form class="form-horizontal">
					<input type="hidden" id="menuType" value="{$MENU_TYPE}" />
					<input type="hidden" name="id" value="{$ID}" />
					<input type="hidden" name="role" value="{$RECORD->get('role')}" />
					<div class="form-group">
						<label class="col-md-4 control-label">{vtranslate('LBL_TYPE_OF_MENU', $QUALIFIED_MODULE)}:</label>
						<div class="col-md-7 form-control-static">
							{vtranslate('LBL_'|cat:strtoupper($MENU_TYPE), $QUALIFIED_MODULE)}
						</div>
					</div>
					{include file='types/'|cat:$MENU_TYPE|cat:'.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
				</form>
			</div>
			<div class="modal-footer">
				<div class="pull-right cancelLinkContainer" style="margin-top:0px;">
					<a class="btn cancelLink btn-warning" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
					<a class="btn btn-success saveButton"><strong>{vtranslate('LBL_SAVE_MENU', $QUALIFIED_MODULE)}</strong></a>
				</div>
			</div>
		</div>
	</div>
</div>
