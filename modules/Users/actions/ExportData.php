<?php

class Users_ExportData_Action extends Vtiger_ExportData_Action{

  /**
   * Function exports the data based on the mode
   * @param Vtiger_Request $request
   */
  function ExportData(Vtiger_Request $request) {
    global $adb;
    $moduleName = $request->get('source_module');

    $this->moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
    $this->moduleFieldInstances = $this->moduleInstance->getFields();
    $this->focus = CRMEntity::getInstance($moduleName);
    $query = $this->getExportQuery($request);
    $result = $adb->pquery($query, array());
    $headers=array('User Name','Title','First Name','Last Name','Email','Other Email','Secondary Email','Office Phone','Mobile','Fax','Street','City','State','Country','Postal Code');
    foreach($headers as $header){
      $translatedHeaders[]=vtranslate(html_entity_decode($header, ENT_QUOTES), $moduleName);
    }
    $entries = array();
    for($j=0; $j<$adb->num_rows($result); $j++) {
      $entries[] = $adb->fetchByAssoc($result, $j);
    }

    $this->output($request, $translatedHeaders, $entries);
  }

  /**
   * Function that generates Export Query based on the mode
   * @param Vtiger_Request $request
   * @return <String> export query
   */
  function getExportQuery(Vtiger_Request $request) {
    $currentUser = Users_Record_Model::getCurrentUserModel();
    $cvId = $request->get('viewname');
    $moduleName = $request->get('source_module');

    $queryGenerator = new QueryGenerator($moduleName, $currentUser);
    if(!empty($cvId)){
    $queryGenerator->initForCustomViewById($cvId);
  }
    $acceptedFields=array('user_name','title','first_name','last_name','email1','email2','secondaryemail','phone_work','phone_mobile','phone_fax','address_street','address_city','address_state','address_country','address_postalcode');
    $queryGenerator->setFields($acceptedFields);
    $query = $queryGenerator->getQuery();
    return $query;
  }
}
