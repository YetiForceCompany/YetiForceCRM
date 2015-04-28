<?php
class oss_addressbook_backend extends rcube_addressbook
{
  public $primary_key = 'ID';
  public $readonly = true;
  public $groups = false;

  private $filter;
  private $result;
  private $name;

  private $data;
  
  public function __construct($name)
  {
    $this->ready = true;
    $this->name = $name;
  }

  public function get_name()
  {
    return $this->name;
  }

  public function set_search_set($filter)
  {
    $this->filter = $filter;
  }

  public function get_search_set()
  {
    return $this->filter;
  }

  public function reset()
  {
    $this->result = null;
    $this->filter = null;
  }

  public function list_records($cols=null, $subset=0){
	$this->data = $this->oss_get_list_records($this->name);
	$this->result = $this->count();
	foreach($this->data as $key => $row){
		$this->result->add(array('ID' => $key, 'name' => $row['name'], 'firstname' => $row['firstname'], 'surname' => $row['surname'], 'email' => $row['email']));
	}
    //$this->result->add(array('ID' => '111', 'name' => "Example Contact", 'firstname' => "Example", 'surname' => "Contact", 'email' => "example@roundcube.net"));
    return $this->result;
  }
  public function oss_get_list_records($name) {
    $url="http://d.opensaas.pl/roundcube_crm/listrecords.php";
    $json = file_get_contents($url);
    $data = json_decode($json, TRUE);
	
$file = 'xxxxxxx.txt';
$current = file_get_contents($file);
$current .= '1';
file_put_contents($file,$current );  
	return $data['result'];
  }
  public function search($fields, $value, $strict=false, $select=true, $nocount=false, $required=array())
  {
    // no search implemented, just list all records
    return $this->list_records();
  }

  public function count()
  {
    return new rcube_result_set(count($this->data), ($this->list_page-1) * $this->page_size);
  }

  public function get_result()
  {
    return $this->result;
  }

public function get_record($id, $assoc=false){
    $this->list_records();
    $this->result->seek($id);
    $conn = $this->result->current();
    $this->result = new rcube_result_set(1);
    $this->result->add($conn);
    return false;
  }
  
  function create_group($name)
  {
    $result = false;

    return $result;
  }
  function delete_group($gid)
  {
    return false;
  }

  function rename_group($gid, $newname)
  {
    return $newname;
  }

  function add_to_group($group_id, $ids)
  {
    return false;
  }

  function remove_from_group($group_id, $ids)
  {
     return false;
  }

}
