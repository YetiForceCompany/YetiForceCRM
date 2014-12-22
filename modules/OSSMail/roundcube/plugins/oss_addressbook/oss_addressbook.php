<?php
require_once(dirname(__FILE__) . '/oss_addressbook_backend.php');
/**
 * Sample plugin to add a new address book
 * with just a static list of contacts
 *
 * @license GNU GPLv3+
 * @author Thomas Bruederli
 */
class oss_addressbook extends rcube_plugin
{
  private $abook_id = 'static';
  private $abook_name = 'Vtiger CRM';

  public function init()
  {
    $this->add_hook('addressbooks_list', array($this, 'address_sources'));
    $this->add_hook('addressbook_get', array($this, 'get_address_book'));

    // use this address book for autocompletion queries
    // (maybe this should be configurable by the user?)
    $config = rcmail::get_instance()->config;
    $sources = (array) $config->get('autocomplete_addressbooks', array('sql'));
    if (!in_array($this->abook_id, $sources)) {
      $sources[] = $this->abook_id;
      $config->set('autocomplete_addressbooks', $sources);
    }
  }
  public function address_sources($p){
    $p['sources']['accounts'] = array(
      'id' => 'accounts',
      'name' => "Accounts",
      'readonly' => true,
      'groups' => false,
    );
    $p['sources']['contacts'] = array(
      'id' => 'contacts',
      'name' => "Contacts",
      'readonly' => true,
      'groups' => false,
    );
    $p['sources']['leads'] = array(
      'id' => 'leads',
      'name' => "Leads",
      'readonly' => true,
      'groups' => false,
    );
    return $p;
  }
  public function get_address_book($p){
    if ($p['id'] === 'leads') {
      $p['instance'] = new oss_addressbook_backend("Leads");
    }
    if ($p['id'] === 'contacts') {
      $p['instance'] = new oss_addressbook_backend("Contacts");
    }
    if ($p['id'] === 'accounts') {
      $p['instance'] = new oss_addressbook_backend("Accounts");
    }
    return $p;
  }
}