<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of txhe License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 * ****************************************************************************** */

class Campaigns extends CRMEntity
{
    public $table_name = 'vtiger_campaign';
    public $table_index = 'campaignid';
    public $tab_name = ['vtiger_crmentity', 'vtiger_campaign', 'vtiger_campaignscf', 'vtiger_entity_stats'];
    public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_campaign' => 'campaignid', 'vtiger_campaignscf' => 'campaignid', 'vtiger_entity_stats' => 'crmid'];

    /**
     * Mandatory table for supporting custom fields.
     */
    public $customFieldTable = ['vtiger_campaignscf', 'campaignid'];
    public $column_fields = [];
    public $list_fields = [
        'Campaign Name' => ['campaign' => 'campaignname'],
        'Campaign Type' => ['campaign' => 'campaigntype'],
        'Campaign Status' => ['campaign' => 'campaignstatus'],
        'Expected Revenue' => ['campaign' => 'expectedrevenue'],
        'Expected Close Date' => ['campaign' => 'closingdate'],
        'Assigned To' => ['crmentity' => 'smownerid'],
    ];
    public $list_fields_name = [
        'Campaign Name' => 'campaignname',
        'Campaign Type' => 'campaigntype',
        'Campaign Status' => 'campaignstatus',
        'Expected Revenue' => 'expectedrevenue',
        'Expected Close Date' => 'closingdate',
        'Assigned To' => 'assigned_user_id',
    ];

    /**
     * @var string[] List of fields in the RelationListView
     */
    public $relationFields = ['campaignname', 'campaigntype', 'campaignstatus', 'expectedrevenue', 'closingdate', 'assigned_user_id'];
    public $list_link_field = 'campaignname';
    //Added these variables which are used as default order by and sortorder in ListView
    public $default_order_by = '';
    public $default_sort_order = 'DESC';
    public $search_fields = [
        'Campaign Name' => ['vtiger_campaign' => 'campaignname'],
        'Campaign Type' => ['vtiger_campaign' => 'campaigntype'],
    ];
    public $search_fields_name = [
        'Campaign Name' => 'campaignname',
        'Campaign Type' => 'campaigntype',
    ];
    // Used when enabling/disabling the mandatory fields for the module.
    // Refers to vtiger_field.fieldname values.
    public $mandatory_fields = ['campaignname', 'createdtime', 'modifiedtime', 'assigned_user_id'];
    // For Alphabetical search
    public $def_basicsearch_col = 'campaignname';

    public function getRelationQuery($module, $secmodule, $table_name, $column_name, ReportRunQueryPlanner $queryPlanner)
    {
        $tab = vtlib\Deprecated::getRelationTables($module, $secmodule);

        foreach ($tab as $key => $value) {
            $tables[] = $key;
            $fields[] = $value;
        }
        $pritablename = $tables[0];
        $sectablename = $tables[1];
        $prifieldname = $fields[0][0];
        $secfieldname = $fields[0][1];
        $tmpname = $pritablename.'tmp'.$secmodule;
        $condition = '';
        if (!empty($tables[1]) && !empty($fields[1])) {
            $condvalue = $tables[1].'.'.$fields[1];
            $condition = "$pritablename.$prifieldname=$condvalue";
        } else {
            $condvalue = $table_name.'.'.$column_name;
            $condition = "$pritablename.$secfieldname=$condvalue";
        }

        // Look forward for temporary table usage as defined by the QueryPlanner
        $secQuery = "select $table_name.* from $table_name inner join vtiger_crmentity on ".
            "vtiger_crmentity.crmid=$table_name.$column_name and vtiger_crmentity.deleted=0";

        $secQueryTempTableQuery = $queryPlanner->registerTempTable($secQuery, [$column_name, $fields[1], $prifieldname]);

        $query = '';
        if ($pritablename == 'vtiger_crmentityrel') {
            $condition = "($table_name.$column_name={$tmpname}.{$secfieldname} ".
                "OR $table_name.$column_name={$tmpname}.{$prifieldname})";
            $query = " left join vtiger_crmentityrel as $tmpname ON ($condvalue={$tmpname}.{$secfieldname} ".
                "OR $condvalue={$tmpname}.{$prifieldname}) ";
        } elseif (strripos($pritablename, 'rel') === (strlen($pritablename) - 3)) {
            $instance = self::getInstance($module);
            $sectableindex = $instance->tab_name_index[$sectablename];
            $condition = "$table_name.$column_name=$tmpname.$secfieldname";
            if ($pritablename === 'vtiger_senotesrel') {
                $query = " left join $pritablename as $tmpname ON ($sectablename.$sectableindex=$tmpname.$prifieldname
                    && $tmpname.notesid IN (SELECT crmid FROM vtiger_crmentity WHERE setype='Documents' && deleted = 0))";
            } else {
                $query = " left join $pritablename as $tmpname ON ($sectablename.$sectableindex=$tmpname.$prifieldname)";
            }
            if ($secmodule === 'Leads') {
                $condition .= " && $table_name.converted = 0";
            }
        } elseif ($pritablename === 'vtiger_campaign_records') {
            $condition = "($table_name.$column_name={$tmpname}.{$secfieldname} ".
                "OR $table_name.$column_name={$tmpname}.{$prifieldname})";
            $query = " left join vtiger_campaign_records as $tmpname ON ($condvalue={$tmpname}.{$secfieldname} ".
                "OR $condvalue={$tmpname}.{$prifieldname}) ";
        }
        $query .= " left join $secQueryTempTableQuery as $table_name on {$condition}";

        return $query;
    }

    /**
     * Function to get the secondary query part of a report.
     *
     * @param string                $module
     * @param string                $secmodule
     * @param ReportRunQueryPlanner $queryPlanner
     *
     * @return string
     */
    public function generateReportsSecQuery($module, $secmodule, ReportRunQueryPlanner $queryplanner)
    {
        $matrix = $queryplanner->newDependencyMatrix();
        $matrix->setDependency('vtiger_crmentityCampaigns', ['vtiger_groupsCampaigns', 'vtiger_usersCampaignss', 'vtiger_lastModifiedByCampaigns', 'vtiger_campaignscf']);
        $matrix->setDependency('vtiger_campaign', ['vtiger_crmentityCampaigns', 'vtiger_productsCampaigns']);

        if (!$queryplanner->requireTable('vtiger_campaign', $matrix)) {
            return '';
        }

        $query = $this->getRelationQuery($module, $secmodule, 'vtiger_campaign', 'campaignid', $queryplanner);

        if ($queryplanner->requireTable('vtiger_crmentityCampaigns', $matrix)) {
            $query .= ' left join vtiger_crmentity as vtiger_crmentityCampaigns on vtiger_crmentityCampaigns.crmid=vtiger_campaign.campaignid and vtiger_crmentityCampaigns.deleted=0';
        }
        if ($queryplanner->requireTable('vtiger_productsCampaigns')) {
            $query .= ' 	left join vtiger_products as vtiger_productsCampaigns on vtiger_campaign.product_id = vtiger_productsCampaigns.productid';
        }
        if ($queryplanner->requireTable('vtiger_campaignscf')) {
            $query .= ' 	left join vtiger_campaignscf on vtiger_campaignscf.campaignid = vtiger_crmentityCampaigns.crmid';
        }
        if ($queryplanner->requireTable('vtiger_groupsCampaigns')) {
            $query .= ' left join vtiger_groups as vtiger_groupsCampaigns on vtiger_groupsCampaigns.groupid = vtiger_crmentityCampaigns.smownerid';
        }
        if ($queryplanner->requireTable('vtiger_usersCampaigns')) {
            $query .= ' left join vtiger_users as vtiger_usersCampaigns on vtiger_usersCampaigns.id = vtiger_crmentityCampaigns.smownerid';
        }
        if ($queryplanner->requireTable('vtiger_lastModifiedByCampaigns')) {
            $query .= ' left join vtiger_users as vtiger_lastModifiedByCampaigns on vtiger_lastModifiedByCampaigns.id = vtiger_crmentityCampaigns.modifiedby ';
        }
        if ($queryplanner->requireTable('vtiger_createdbyCampaigns')) {
            $query .= ' left join vtiger_users as vtiger_createdbyCampaigns on vtiger_createdbyCampaigns.id = vtiger_crmentityCampaigns.smcreatorid ';
        }

        return $query;
    }

    /**
     * Function to get the relation tables for related modules.
     *
     * @param bool|string $secModule secondary module name
     *
     * @return array with table names and fieldnames storing relations between module and this module
     */
    public function setRelationTables($secModule = false)
    {
        $relTables = [
            'Contacts' => ['vtiger_campaign_records' => ['campaignid', 'crmid'], 'vtiger_campaign' => 'campaignid'],
            'Leads' => ['vtiger_campaign_records' => ['campaignid', 'crmid'], 'vtiger_campaign' => 'campaignid'],
            'Accounts' => ['vtiger_campaign_records' => ['campaignid', 'crmid'], 'vtiger_campaign' => 'campaignid'],
            'Vendors' => ['vtiger_campaign_records' => ['campaignid', 'crmid'], 'vtiger_campaign' => 'campaignid'],
            'Partners' => ['vtiger_campaign_records' => ['campaignid', 'crmid'], 'vtiger_campaign' => 'campaignid'],
            'Competition' => ['vtiger_campaign_records' => ['campaignid', 'crmid'], 'vtiger_campaign' => 'campaignid'],
            'OSSMailView' => ['vtiger_ossmailview_relation' => ['crmid', 'ossmailviewid'], 'vtiger_campaign' => 'campaignid'],
            'Products' => ['vtiger_campaign' => ['campaignid', 'product_id']],
        ];
        if ($secModule === false) {
            return $relTables;
        }

        return $relTables[$secModule];
    }

    // Function to unlink an entity with given Id from another entity
    public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
    {
        if (empty($returnModule) || empty($returnId)) {
            return;
        }

        if (in_array($returnModule, ['Leads', 'Vendors', 'Contacts', 'Partners', 'Competition'])) {
            App\Db::getInstance()->createCommand()->delete('vtiger_campaign_records', ['campaignid' => $id, 'crmid' => $returnId])->execute();
        } elseif ($returnModule == 'Accounts') {
            $db = App\Db::getInstance();
            $db->createCommand()->delete('vtiger_campaign_records', ['campaignid' => $id, 'crmid' => $returnId])->execute();
            $db->createCommand()->delete('vtiger_campaign_records', ['campaignid' => $id, 'crmid' => (new \App\Db\Query())->select(['contactid'])->from('vtiger_contactdetails')->where(['parentid' => $returnId])])->execute();
        } else {
            parent::unlinkRelationship($id, $returnModule, $returnId, $relatedName);
        }
    }

    public function saveRelatedModule($module, $crmid, $withModule, $withCrmids, $relatedName = false)
    {
        if (!is_array($withCrmids)) {
            $withCrmids = [$withCrmids];
        }
        if (!in_array($withModule, ['Accounts', 'Leads', 'Vendors', 'Contacts', 'Partners', 'Competition'])) {
            parent::saveRelatedModule($module, $crmid, $withModule, $withCrmids, $relatedName);
        } else {
            foreach ($withCrmids as $withCrmid) {
                $checkResult = (new App\Db\Query())->from('vtiger_campaign_records')
                    ->where(['campaignid' => $crmid, 'crmid' => $withCrmid])
                    ->exists();
                if ($checkResult) {
                    continue;
                }
                App\Db::getInstance()->createCommand()->insert('vtiger_campaign_records', [
                    'campaignid' => $crmid,
                    'crmid' => $withCrmid,
                    'campaignrelstatusid' => 0,
                ])->execute();
            }
        }
    }
}
