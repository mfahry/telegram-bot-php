<?php

class Auth_model extends CI_Model {
  private $tableName = "auth";

  function get($data){
    $this->db->where($data);
    $result = $this->db->get($this->tableName);
    return $result->row();
  }

  function change($data, $where){
    $this->db->where($where);
    return $this->db->update($this->tableName, $data);
  }
}
