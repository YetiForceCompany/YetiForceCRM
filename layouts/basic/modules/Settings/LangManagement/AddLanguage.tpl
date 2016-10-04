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
<div class="modal fade AddNewLangMondal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
        <div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h3 id="myModalLabel" class="modal-title">{vtranslate('LBL_ADD_LANG',$QUALIFIED_MODULE)}</h3>
			</div>
			<div class="modal-body form-horizontal">
				<div class="form-group">
					<label class="control-label col-md-3">{vtranslate('LBL_Lang_label', $QUALIFIED_MODULE)}:</label>
					<div class="col-md-7"><input name="label" class="form-control" type="text" /></div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">{vtranslate('LBL_Lang_name', $QUALIFIED_MODULE)}:</label>
					<div class="col-md-7"><input name="name" class="form-control" type="text" /></div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">{vtranslate('LBL_Lang_prefix', $QUALIFIED_MODULE)}:</label>
					<div class="col-md-7"><input name="prefix" class="form-control" type="text" /></div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-primary">{vtranslate('LBL_AddLanguage', $QUALIFIED_MODULE)}</button>
				<button class="btn btn-warning" data-dismiss="modal" type="button" aria-hidden="true">{vtranslate('LBL_Cancel', $QUALIFIED_MODULE)}</button>
			</div>
		</div>
	</div>
</div>
