<?php

class User_accepted_request_model extends CI_Model {
  private $tableName = "user_accepted_request";

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
  function getDetail($where){
    $this->db->select("a.TICKET_ID, MESSAGE, MESSAGE_DATE");
		$this->db->from("user_accepted_request a");
		$this->db->join("telegram_message b", "a.ticket_id = b.ticket_id");
		$this->db->where($where);
		$result = $this->db->get();

		return $result->result();
  }  
}
