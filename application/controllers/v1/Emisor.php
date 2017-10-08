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
class Emisor extends REST_Controller {

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
        $http_headers = $this->input->request_headers();
        $data["apikey"] = $http_headers["x-api-key"];

        if ((new Token)->validated($http_headers)) {
            if ($this->form_validation->run('emisor') == FALSE) {
                $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, validation_errors());
            } else {
                $emisor = $dao->create_or_update("transmitter", Form::tokenRemove($data), array("rfc" => $data["rfc"]));
                if (!isset($emisor["code"])) { //Record exist
                    $msg = (is_array($emisor)) ? API_Response::UPDATE : API_Response::CREATE;
                    $APIRes->setResponseInsUpd(REST_Controller::HTTP_OK, $dao->find("transmitter", array("rfc" => $data["rfc"])), $msg);
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
     * @param string $name Description
     * @return array (http code, [code,data])
     */
    public function account_get() {
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $rfc = $this->get("rfc");

        if ((new Token)->validated($this->input->request_headers())) {
            $emisor = $dao->find("transmitter", array("rfc" => $rfc));
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

    public function account_delete($rfc) {
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $baja = array("status" => "BAJA", "downDate" => date("Y-m-d H:i:s"));

        if ((new Token)->validated($this->input->request_headers())) {
            $emisor = $dao->logicalDelete("transmitter", $baja, array("rfc" => $rfc));
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
    public function list_get($limit = 10, $offset = 0) {
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $link = array();
        $http_header = $this->input->request_headers();
        $where = array(
            "apikey" => $http_header["x-api-key"],
            "status" => "ACTIVO"
        );

        if ((new Token)->validated($http_header)) {
            $emisor = $dao->getList("transmitter", $where, $limit, $offset);
            (new Form)->recursive_unset($emisor, "apikey");
            if (!isset($emisor["code"])) { //Record exist
                $link = Form::get_pagination("transmitter", site_url($this->config->item("api_version") . "/emisor/list/" ));
                $APIRes->setResponse(REST_Controller::HTTP_OK, array_merge($emisor, $link));
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

    function sat_document_type($key) {
        return $this->validation_model->sat_document_type($key);
    }

}
