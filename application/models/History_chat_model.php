<?php

class History_chat_model extends CI_Model {
  private $tableName = "tt_history_chat";

  public function __construct() {
		parent::__construct();
    $this->db = $this->load->database("portal_mac", TRUE);
	}

  function getList($where = null){
    if($where != null){
      $this->db->where($where);
    }

    $result = $this->db->get($this->tableName);
    return $result->result();
  }

  function get($where){
    $this->db->where($where);
    $result = $this->db->get($this->tableName);
    return $result->row();
  }

  function change($data, $where){
    $this->db->where($where);
    return $this->db->update($this->tableName, $data);
  }

  function store($data){
    $this->db->insert($this->tableName, $data);
  }

  function destroy($where){
    return $this->db->delete($this->tableName, $where);
  }
}
