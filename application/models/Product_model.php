<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//require APPPATH . '/libraries/GenericDAO.php';
/**
 * Description of Authorize_model
 *
 * @author Ing. Erick Suárez Buendía
 */
class Product_model extends CI_Model {

    public function myproducts($rfc,$SATcode=0) {
        $dao = new GenericDAO();
        $sql = "SELECT * 
                FROM my_products AS mp 
                INNER JOIN sat_services_products AS sp ON sp.c_ClaveProdServ=mp.c_ClaveProdServ
                WHERE mp.rfc='".$rfc."' ";
        
        if ($SATcode > 0){
            $sql .= " AND mp.c_ClaveProdServ = '".$SATcode."'";
        }
        
        $query = $dao->getSpecialQuery($sql);
        return $query;
    }
    
    public function list_myproducts($rfc) {
        $dao = new GenericDAO();
        $sql = "SELECT * 
                FROM my_products AS mp 
                INNER JOIN sat_services_products AS sp ON sp.c_ClaveProdServ=mp.c_ClaveProdServ
                WHERE mp.rfc='".$rfc."'";
        $query = $dao->getSpecialQuery($sql);
        return $query;
    }
    
    public function assign_list_product($rfc, $limit, $offset) {
        $dao = new GenericDAO();
        $sql = "SELECT * ,
                    COALESCE(
                        (
                            SELECT 
                              IF(mp.c_ClaveProdServ > 0, 'checked', NULL) 
                            FROM my_products AS mp 
                            WHERE mp.c_ClaveProdServ=sp.c_ClaveProdServ AND mp.rfc='" . $rfc . "'),'') AS assign
                   FROM sat_services_products AS sp
                   LIMIT " . $offset . ", " . $limit;
        $query = $dao->getSpecialQuery($sql);
        return $query;
    }

    public function search_product($rfc, $nameProduct, $limit, $offset) {
        $dao = new GenericDAO();
        $query = [];
        if (trim($nameProduct) == "") {
            $query = $this->assign_list_product($rfc, $limit, $offset);
        } else {
            $sql = "SELECT * ,
                    COALESCE(
                        (
                            SELECT 
                              IF(mp.c_ClaveProdServ > 0, 'checked', NULL) 
                            FROM my_products AS mp 
                            WHERE mp.c_ClaveProdServ=sp.c_ClaveProdServ AND mp.rfc='" . $rfc . "'),'') AS assign                
                FROM sat_services_products AS sp
                WHERE MATCH (sp.c_ClaveProdServ,sp.Descripcion)
                AGAINST ('" . $nameProduct . "' IN NATURAL LANGUAGE MODE)
                LIMIT " . $offset . ", " . $limit;
            $query = $dao->getSpecialQuery($sql);
        }
        return $query;
    }

}
