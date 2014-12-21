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
<div id="AddNewTranslationMondal" class="modal hide fade" tabindex="2" role="dialog" aria-labelledby="AddTranslation" aria-hidden="true">
<form id="AddTranslationForm" >
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="AddTranslation">{vtranslate('LBL_ADD_Translate',$QUALIFIED_MODULE)}</h3>
	</div>
	<div class="modal-body">
	<div class="row-fluid">
		<input type="hidden" name="langs" value="" />
		<div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_TranslationType', $QUALIFIED_MODULE)}:</label></div>
		<div class="span7">
			<select name="type" class="span3" id="translation_type">
				<option value="php">{vtranslate('LBL_LangPHP', $QUALIFIED_MODULE)}</option>
				<option value="js">{vtranslate('LBL_LangJS', $QUALIFIED_MODULE)}</option>
			</select>
		</div>
		<div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_variable', $QUALIFIED_MODULE)}:</label></div>
		<div class="span7"><input name="variable" class="span3" type="text" /></div>
	</div>
	<div class="row-fluid add_translation_block">
	
	</div>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">{vtranslate('LBL_Cancel', $QUALIFIED_MODULE)}</button>
		<button class="btn btn-primary">{vtranslate('LBL_AddLanguage', $QUALIFIED_MODULE)}</button>
	</div>
</form>
</div>