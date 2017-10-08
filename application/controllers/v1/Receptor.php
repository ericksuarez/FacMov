<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';

/**
 * Class Authorize will given access all users with token
 *
 * @package         Controller
 * @subpackage      api/v1
 * @category        Emisor
 * @author          Ing. Erick Suarez Buendia
 */
class Receptor extends REST_Controller {

    function __construct() {
        // Construct the parent class
        parent::__construct();
        $this->load->helper('security');
        $this->load->library('Form');
        $this->load->model('Form_Validation_model', 'validation_model');
    }

    /**
     * Class Account post this method check if the account exist the method update
     * in other case it do an insert for the new account.
     * @param string $name Description
     * @return array (http code, [code,msg,data])
     */
    public function account_post() {
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $data = $this->input->post();
        $http_header = $this->input->request_headers();
        $where = array(
            "apikey" => $http_header["x-api-key"],
            "rfc" => $data["rfc"],
            "rfc_emisor" => $data["rfc_emisor"]
        );

        if ((new Token)->validated($http_header)) {
            if ($this->form_validation->run('receptor') == FALSE) {
                $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, validation_errors());
            } else {
                $emisor = $dao->create_or_update("customer", array_merge($data, $where), $where);

                if (!isset($emisor["code"])) { //Record exist
                    $msg = (is_array($emisor)) ? API_Response::UPDATE : API_Response::CREATE;
                    $APIRes->setResponseInsUpd(REST_Controller::HTTP_OK, $dao->find("customer", $where), $msg);
                } elseif ($emisor["code"] > 0) { //There are some error in db
                    $APIRes->setResponseInsUpd(REST_Controller::HTTP_INTERNAL_SERVER_ERROR, array("error" => $emisor), API_Response::ERROR);
                } else if ($emisor["code"] == 0) { //Record not exist
                    $APIRes->setResponseInsUpd(REST_Controller::HTTP_CONFLICT, array("error" => API_Response::UNAUTHORIZED), API_Response::ERROR);
                }
            }
        } else {
            $APIRes->setResponseInsUpd(REST_Controller::HTTP_BAD_REQUEST, array("error" => API_Response::EXPIRED_TOKEN), API_Response::ERROR);
        }
        $this->set_response($APIRes->getResponse(), $APIRes->getHttpcode());
    }

    /**
     * Class Account get return the  record
     * @param string $rfc RFC´s customer
     * @return array (http code, [code,data])
     */
    public function account_get() {
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $rfc = $this->get("rfc");
        $http_header = $this->input->request_headers();
        $where = array(
            "apikey" => $http_header["x-api-key"],
            "rfc" => $rfc
        );

        if ((new Token)->validated($http_header)) {
            $emisor = $dao->find("customer", $where);
            if (!isset($emisor["code"])) { //Record exist
                $APIRes->setResponse(REST_Controller::HTTP_OK, $emisor);
            } elseif ($emisor["code"] > 0) { //There are some error in db
                $APIRes->setResponse(REST_Controller::HTTP_INTERNAL_SERVER_ERROR, array("error" => $emisor));
            } else if ($emisor["code"] == 0) { //Record not exist
                $APIRes->setResponse(REST_Controller::HTTP_CONFLICT, array("error" => API_Response::NOT_EXIST));
            }
        } else {
            $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, array("error" => API_Response::EXPIRED_TOKEN));
        }
        $this->set_response($APIRes->getResponse(), $APIRes->getHttpcode());
    }

    /**
     * Class Account delete return the code and description of the action
     * @param string $rfc RFC´s customer
     * @return array (http code, [code,data])
     */
    public function account_delete($rfc) {
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $baja = array("status" => "BAJA", "downDate" => date("Y-m-d H:i:s"));
        $http_header = $this->input->request_headers();
        $where = array(
            "apikey" => $http_header["x-api-key"],
            "rfc" => $rfc
        );

        if ((new Token)->validated($http_header)) {
            $emisor = $dao->logicalDelete("customer", $baja, $where);
            if (!isset($emisor["code"])) { //Record exist
                $APIRes->setResponse(REST_Controller::HTTP_OK, API_Response::DELETE);
            } elseif ($emisor["code"] > 0) { //There are some error in db
                $APIRes->setResponse(REST_Controller::HTTP_INTERNAL_SERVER_ERROR, array("error" => $emisor));
            } else if ($emisor["code"] == 0) { //Record not exist
                $APIRes->setResponse(REST_Controller::HTTP_CONFLICT, array("error" => API_Response::NOT_EXIST));
            }
        } else {
            $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, array("error" => API_Response::EXPIRED_TOKEN));
        }
        $this->set_response($APIRes->getResponse(), $APIRes->getHttpcode());
    }

    /**
     * Class Account get return a list of all receptor
     * @param string $rfc customer RFC have some receptor
     * @return array (http code, [code,data])
     */
    public function list_get($rfc, $limit = 10, $offset = 0) {
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $link = array();
        $http_header = $this->input->request_headers();
        $where = array(
            "apikey" => $http_header["x-api-key"],
            "rfc_emisor" => $rfc,
            "status" => "ACTIVO"
        );

        if ((new Token)->validated($http_header)) {
            $receptor = $dao->getList("customer", $where, $limit, $offset);
            (new Form)->recursive_unset($receptor, "apikey");
            if (!isset($receptor["code"])) { //Record exist
                $link = Form::get_pagination("customer", site_url($this->config->item("api_version") . "/receptor/list/" . $rfc));
                $APIRes->setResponse(REST_Controller::HTTP_OK, array_merge($receptor, $link));
            } elseif ($receptor["code"] > 0) { //There are some error in db
                $APIRes->setResponse(REST_Controller::HTTP_INTERNAL_SERVER_ERROR, array("error" => $receptor));
            } else if ($receptor["code"] == 0) { //Record not exist
                $APIRes->setResponse(REST_Controller::HTTP_CONFLICT, array("error" => API_Response::NOT_EXIST));
            }
        } else {
            $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, array("error" => API_Response::EXPIRED_TOKEN));
        }
        $this->set_response($APIRes->getResponse(), $APIRes->getHttpcode());
    }

    /**
     * Class Account get return a list of all receptor
     * @param string $rfc customer RFC have some receptor
     * @return array (http code, [code,data])
     */
    public function listall_get($limit = 10, $offset = 0) {
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $link = array();

        if ((new Token)->validated($this->input->request_headers())) {
            $receptor = $dao->getList("customer", NULL, $limit, $offset);
            (new Form)->recursive_unset($receptor, "apikey");
            if (!isset($receptor["code"])) { //Record exist
                $link = Form::get_pagination("customer", site_url($this->config->item("api_version") . "/receptor/list"));
                $APIRes->setResponse(REST_Controller::HTTP_OK, array_merge($receptor, $link));
            } elseif ($receptor["code"] > 0) { //There are some error in db
                $APIRes->setResponse(REST_Controller::HTTP_INTERNAL_SERVER_ERROR, array("error" => $receptor));
            } else if ($receptor["code"] == 0) { //Record not exist
                $APIRes->setResponse(REST_Controller::HTTP_CONFLICT, array("error" => API_Response::NOT_EXIST));
            }
        } else {
            $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, array("error" => API_Response::EXPIRED_TOKEN));
        }
        $this->set_response($APIRes->getResponse(), $APIRes->getHttpcode());
    }

}
