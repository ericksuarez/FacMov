<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require APPPATH . '/libraries/GenericDAO.php';
/**
 * Description of Authorize_model
 *
 * @author Ing. Erick Suárez Buendía
 */
class Authorize_model extends CI_Model {

    public function login($user, $pass) {
        $dao = new GenericDAO();
        $auh = $dao->find("user", array("user" => $user, "password" => $pass));
        return $auh;
    }
    
    public function account($data) {
        $dao = new GenericDAO();
        $id = $dao->create("prospects", $data);
        return $id;
    }

    private function getQuery($sql) {
        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            $array = $query->result_array();
        } else {
            $array = array();
        }
        return $array;
    }

    private function getOnlyRow($sql) {
        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            $array = $query->result_array();
        } else {
            $array = array();
        }
        return $array[0];
    }

}
