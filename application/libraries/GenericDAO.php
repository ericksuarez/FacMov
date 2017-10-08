<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GenericDAO
 *
 * @author Ing. Erick Suárez Buendía
 */
class GenericDAO {

    public function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->database();
    }

    public function _initDB($username, $password) {
        $config = array(
            'username' => $username,
            'password' => $password
        );
        $this->CI->load->database($config);
    }

    /*
     * @param $tabla name's tables of the databases
     * @param $data is an array the items to insert into the table
     * @return it's bring the last id insert into the database
     */

    public function create($table, $data) {
        $this->CI->db->insert($table, $data); // Produces: INSERT INTO mytable (`name`) VALUES ('{$name}')
        $error = $this->CI->db->error();
        if ($error["code"] == 0) {
            return $this->CI->db->insert_id();
        } else {
            return $this->CI->db->error();
        }
    }

    /*
     * @param $field_value is an array to describe the column name and value that you are going to search
     */

    public function find($table, $field_value) {
        $user = $this->CI->db->get_where($table, $field_value);
        if($user->num_rows() == NULL){
            return array("code" => 0, "message" => "No se econtraron registros");
        }else{
            return $user->row_array();
        }
    }

    /*
     * @param $data is an array the item for set the new values
     * @return number of row affects
     */

    public function update($table, $data, $where) {
        $this->CI->db->update($table, $data, $where);
        $error = $this->CI->db->error();
        if ($error["code"] == 0) {
            return $this->CI->db->affected_rows();
        } else {
            return $this->CI->db->error();
        }
    }

    /*
     * @param $where is an array to describe the parameters from condition
     * @return number of row affects
     */

    public function delete($table, $where) {
        $this->CI->db->delete($table, $where);  // Produces: // DELETE FROM mytable  // WHERE id = $id
        $error = $this->CI->db->error();
        if ($error["code"] == 0) {
            return $this->CI->db->affected_rows();
        } else {
            return $this->CI->db->error();
        }
    }

    /*
     * @param $where is an array to describe the parameters from condition
     */

    public function create_or_update($table, $data, $where) {
        $record = $this->find($table, $where);
        if (isset($record["code"])) { //Record not exist
            return $this->create($table, $data);
        } else {
            $this->update($table, $data, $where);
            return $this->find($table, $where);
        }
    }

    public function logicalDelete($table, $data, $where) {
        $this->CI->db->update($table, $data, $where);
        $error = $this->CI->db->error();
        if ($error["code"] == 0) {
            return $this->CI->db->affected_rows();
        } else {
            return $this->CI->db->error();
        }
    }

    public function getListAll($table) {
        $list = $this->CI->db->get($table);
        $error = $this->CI->db->error();
        if ($error["code"] == 0) {
            return $this->getQuery($list);
        } else {
            return $this->CI->db->error();
        }
    }

    public function getListCondition($table, $where) {
        $list = $this->CI->db->get_where($table, $where);
        $error = $this->CI->db->error();
        if ($error["code"] == 0) {
            return $this->getQuery($list);
        } else {
            return $this->CI->db->error();
        }
    }
    public function getList($table, $where, $limit, $offset) {
        $list = $this->CI->db->get_where($table, $where, $limit, $offset);
        $error = $this->CI->db->error();
        if ($error["code"] == 0) {
            return $this->getQuery($list);
        } else {
            return $this->CI->db->error();
        }
    }

    public function getListLimit($table, $limit, $offset) {
        $list = $this->CI->db->get($table, $limit, $offset);
        $error = $this->CI->db->error();
        if ($error["code"] == 0) {
            return $this->getQuery($list);
        } else {
            return $this->CI->db->error();
        }
    }

    public function getSpecialQuery($query) {
        $list = $this->CI->db->query($query);
        $error = $this->CI->db->error();
        if ($error["code"] == 0) {
            return $this->getQuery($list);
        } else {
            return $this->CI->db->error();
        }
    }

    private function getQuery($query) {
        if ($query->num_rows() > 0) {
            $array = $query->result_array();
        } else {
            $array = array();
        }
        return $array;
    }

    private function getOnlyRow($query) {
        if ($query->num_rows() > 0) {
            $tmp = $query->result_array();
            $array = $tmp[0];
        } else {
            $array = array();
        }
        return $array;
    }

}
