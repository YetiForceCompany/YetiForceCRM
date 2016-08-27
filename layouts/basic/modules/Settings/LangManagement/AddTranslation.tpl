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
<div class="modal fade AddNewTranslationMondal" tabindex="-1" role="dialog" aria-labelledby="AddTranslation" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">	
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h3 id="AddTranslation" class="modal-title">{vtranslate('LBL_ADD_Translate',$QUALIFIED_MODULE)}</h3>
			</div>
			<div class="modal-body">
				<form class="form-horizontal AddTranslationForm">	
					<input type="hidden" name="langs" value="" />
					<div class="form-group">
						<label for="translation_type" class="col-sm-4 control-label">{vtranslate('LBL_TranslationType', $QUALIFIED_MODULE)}:</label>
						<div class="col-sm-8">
							<select name="type" class="form-control" id="translation_type">
								<option value="php">{vtranslate('LBL_LangPHP', $QUALIFIED_MODULE)}</option>
								<option value="js">{vtranslate('LBL_LangJS', $QUALIFIED_MODULE)}</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="variable" class="col-sm-4 control-label">{vtranslate('LBL_variable', $QUALIFIED_MODULE)}:</label>
						<div class="col-sm-8">
							<input id="variable" name="variable" class="form-control" type="text" placeholder="{vtranslate('LBL_variable', $QUALIFIED_MODULE)}"/>
						</div>
					</div>
					<div class="add_translation_block">
					</div>
				</form>	
			</div>
			<div class="modal-footer">
				<button class="btn btn-primary">{vtranslate('LBL_AddLanguage', $QUALIFIED_MODULE)}</button>
				<button class="btn btn-warning" data-dismiss="modal" aria-hidden="true" type="button">{vtranslate('LBL_Cancel', $QUALIFIED_MODULE)}</button>
			</div>
		</div>
	</div>
</div>
