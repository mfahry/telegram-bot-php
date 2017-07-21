<?php

class Auth_request_model extends CI_Model {
  private $tableName = "auth_request";

  function get($where){
    $this->db->where($where);
    $result = $this->db->get($this->tableName);
    return $result->row();
  }

  function store($data){
    return $this->db->insert($this->tableName, $data);
  }

  function change($data, $where){
    $this->db->where($where);
    return $this->db->update($this->tableName, $data);
  }
}
