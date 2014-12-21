<?php
/* * *******************************************************************************
 * The content of this file is subject to the MYC Vtiger Customer Portal license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is Proseguo s.l. - MakeYourCloud
 * Portions created by Proseguo s.l. - MakeYourCloud are Copyright(C) Proseguo s.l. - MakeYourCloud
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */
 
class Products extends BaseModule{
		
	public function get_list(){
		$allow_all = $GLOBALS["sclient"]->call('show_all',array('module'=>$this->module));
		
		if($allow_all!='true') $onlymine="true";
		
		$sparams = array(
			'id' => $_SESSION["loggeduser"]['id'], 
			'block'=>$this->module,
			'sessionid'=>$_SESSION["loggeduser"]['sessionid'],
			'onlymine'=>$onlymine
		);
		
		$lmod = $GLOBALS["sclient"]->call('get_product_list_values', $sparams);
		
		if(isset($lmod) && count($lmod)>0 && $lmod!=""){
			$tablenames[0]="LBL_PRODUCTS_RELATED_PRODUCTS";
			$tablenames[1]="LBL_PRODUCTS_RELATED_QUOTES";
			$tablenames[2]="LBL_PRODUCTS_RELATED_INVOICES";
			
			$count=0;
			foreach($lmod as $table){
				$datas[$count]['tablename']=$tablenames[$count];
				$datas[$count]['recordlist']=$table[$this->module]['data'];
				$datas[$count]['tableheader']=$table[$this->module]['head'][0];
				$count++;
			} 
		
		}
		Template::display($this->module,$datas,'list');
	}
}