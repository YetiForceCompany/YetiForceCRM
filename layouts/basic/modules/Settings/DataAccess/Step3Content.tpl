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
<div class="row padding1per contentsBackground no-margin" style="border:1px solid #ccc;box-shadow: 2px 2px 2px rgba(0, 0, 0, 0.5);">
	<div id="advanceFilterContainer" class="">
		<a class="btn btn-primary pull-right marginBottom10px" href="index.php?module={$MODULE_NAME}&parent=Settings&view=AddAction&id={$TPL_ID}&base_module={$BASE_MODULE}">
			<strong>{vtranslate('LBL_NEW_ACTION', $QUALIFIED_MODULE)}</strong>
		</a>
		<h5 class="padding-bottom1per"><strong>{vtranslate('LBL_CHOOSE_ACTIONS',$QUALIFIED_MODULE)}</strong></h5>
		{include file='ListAction.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
		<br>
		<div class="pull-right">
			<a class="btn btn-danger backStep" href="index.php?module=DataAccess&parent=Settings&view=Step2&tpl_id={$TPL_ID}&base_module={$BASE_MODULE}"><strong>{vtranslate('BACK', $QUALIFIED_MODULE)}</strong></a>
			<a class="btn btn-success" href="index.php?module=DataAccess&parent=Settings&view=Index">{vtranslate('NEXT', $QUALIFIED_MODULE)}</a>
			<a class="cancelLink btn btn-warning" href="index.php?module=DataAccess&parent=Settings&view=Index">{vtranslate('CANCEL', $QUALIFIED_MODULE)}</a>
		</div>
	</div>
<div class="clearfix"></div>
