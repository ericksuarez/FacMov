<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Form_Validation_model
 *
 * @author Ing. Erick Suárez Buendía
 */
class Form_Validation_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
    }

    public function sat_document_type($key) {

        if (ctype_upper($key)) {
            $dao = new GenericDAO();
            $record = $dao->find("sat_document_type", array("c_TipoDeComprobante" => $key));

            if (!isset($record["c_TipoDeComprobante"])) { //Record not exist
                $this->form_validation->set_message('sat_document_type', 'El campo {field} no es valido.');
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            $this->form_validation->set_message('sat_document_type', 'El campo {field} debe de ser en mayusculas.');
            return FALSE;
        }
    }

    public function sat_country($key) {

        if (ctype_upper($key)) {
            $dao = new GenericDAO();
            $record = $dao->find("sat_country", array("c_Pais" => $key));

            if (!isset($record["c_Pais"])) { //Record not exist
                $this->form_validation->set_message('sat_country', 'El campo {field} no es valido.');
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            $this->form_validation->set_message('sat_country', 'El campo {field} debe de ser en mayusculas.');
            return FALSE;
        }
    }

    public function sat_code_postal($key) {

        $dao = new GenericDAO();
        $record = $dao->find("sat_code_postal", array("c_CodigoPostal" => $key));

        if (!isset($record["c_CodigoPostal"])) { //Record not exist
            $this->form_validation->set_message('sat_code_postal', 'El campo {field} no es valido.');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function sat_services_products($key) {

        $dao = new GenericDAO();
        $record = $dao->find("sat_services_products", array("c_ClaveProdServ" => $key));

        if (!isset($record["c_ClaveProdServ"])) { //Record not exist
            $this->form_validation->set_message('sat_services_products', 'El campo {field} no es valido.');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function transmitter($key) {

        $dao = new GenericDAO();
        $record = $dao->find("transmitter", array("rfc" => $key));

        if (!isset($record["rfc"])) { //Record not exist
            $this->form_validation->set_message('transmitter', 'El campo {field} no es valido.');
            return FALSE;
        } else {
            return TRUE;
        }
    }
    
    public function sat_unit($key) {

        $dao = new GenericDAO();
        $record = $dao->find("sat_unit", array("c_ClaveUnidad" => $key));

        if (!isset($record["c_ClaveUnidad"])) { //Record not exist
            $this->form_validation->set_message('sat_unit', 'El campo {field} no es valido.');
            return FALSE;
        } else {
            return TRUE;
        }
    }

}
