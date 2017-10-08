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
class Product extends REST_Controller {

    function __construct() {
        // Construct the parent class
        parent::__construct();
        $this->load->helper('security');
        $this->load->library('Form');
        $this->load->model('Form_Validation_model', 'validation_model');
    }

    /**
     * Class Address post this method check if the account exist the method update
     * in other case it do an insert for the new account.
     * @param string $name Description
     * @return array (http code, [code,msg,data])
     */
    public function linkup_post() {
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $data = $this->input->post();
        $http_header = $this->input->request_headers();

        if ((new Token)->validated($http_header)) {
            if ($this->form_validation->run('my_products') == FALSE) {
                $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, validation_errors());
            } else {
                $where = array(
                    "apikey" => $http_header["x-api-key"],
                    "rfc" => $data["rfc"],
                    "c_ClaveProdServ" => $data["c_ClaveProdServ"]
                );
                $product = $dao->create_or_update("my_products", array_merge($data, $where), $where);

                if (!isset($product["code"])) { //Record exist
                    $msg = (is_array($product)) ? API_Response::UPDATE : API_Response::CREATE;
                    $APIRes->setResponseInsUpd(REST_Controller::HTTP_OK, $dao->find("my_products", $where), $msg);
                } elseif ($product["code"] > 0) { //There are some error in db
                    $APIRes->setResponseInsUpd(REST_Controller::HTTP_INTERNAL_SERVER_ERROR, array("error" => $product), API_Response::ERROR);
                } else if ($product["code"] == 0) { //Record not exist
                    $APIRes->setResponseInsUpd(REST_Controller::HTTP_CONFLICT, array("error" => API_Response::UNAUTHORIZED), API_Response::ERROR);
                }
            }
        } else {
            $APIRes->setResponseInsUpd(REST_Controller::HTTP_BAD_REQUEST, array("error" => API_Response::EXPIRED_TOKEN), API_Response::ERROR);
        }
        $this->set_response($APIRes->getResponse(), $APIRes->getHttpcode());
    }

    /**
     * Class Address get return the  record
     * @param string $rfc RFC´s customer or trasmitter
     * @return array (http code, [code,data])
     */
    public function linkup_get() {
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $rfc = $this->get("rfc");
        $c_ClaveProdServ = $this->get("clave");
        $http_header = $this->input->request_headers();

        if ((new Token)->validated($http_header)) {
            $where = array(
                "apikey" => $http_header["x-api-key"],
                "rfc" => $rfc,
                "c_ClaveProdServ" => $c_ClaveProdServ
            );
            $product = $dao->find("my_products", $where);
            if (!isset($product["code"])) { //Record exist
                $APIRes->setResponse(REST_Controller::HTTP_OK, $product);
            } elseif ($product["code"] > 0) { //There are some error in db
                $APIRes->setResponse(REST_Controller::HTTP_INTERNAL_SERVER_ERROR, array("error" => $product));
            } else if ($product["code"] == 0) { //Record not exist
                $APIRes->setResponse(REST_Controller::HTTP_CONFLICT, array("error" => API_Response::NOT_EXIST));
            }
        } else {
            $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, array("error" => API_Response::EXPIRED_TOKEN));
        }
        $this->set_response($APIRes->getResponse(), $APIRes->getHttpcode());
    }

    /**
     * Class Address delete return the code and description of the action
     * @param string $rfc RFC´s customer or trasmitter
     * @return array (http code, [code,data])
     */
    public function linkup_delete($rfc, $c_ClaveProdServ) {
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $http_header = $this->input->request_headers();
        $baja = array("status" => "BAJA", "downDate" => date("Y-m-d H:i:s"));

        if ((new Token)->validated($http_header)) {
            $where = array(
                "apikey" => $http_header["x-api-key"],
                "rfc" => $rfc,
                "c_ClaveProdServ" => $c_ClaveProdServ
            );
            $product = $dao->logicalDelete("my_products", $baja, $where);
            if (!isset($product["code"])) { //Record exist
                $APIRes->setResponse(REST_Controller::HTTP_OK, API_Response::DELETE);
            } elseif ($product["code"] > 0) { //There are some error in db
                $APIRes->setResponse(REST_Controller::HTTP_INTERNAL_SERVER_ERROR, array("error" => $product));
            } else if ($product["code"] == 0) { //Record not exist
                $APIRes->setResponse(REST_Controller::HTTP_CONFLICT, array("error" => API_Response::NOT_EXIST));
            }
        } else {
            $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, array("error" => API_Response::EXPIRED_TOKEN));
        }
        $this->set_response($APIRes->getResponse(), $APIRes->getHttpcode());
    }

    /**
     * Class Address get return a list of all receptor
     * @param string $rfc customer or trasmitter address
     * @return array (http code, [code,data])
     */
    public function list_get($rfc, $limit = 10, $offset = 0) {
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $link = array();
        $http_header = $this->input->request_headers();
        $where = array(
            "apikey" => $http_header["x-api-key"],
            "rfc" => $rfc,
            "status" => "ACTIVO"
        );

        if ((new Token)->validated($http_header)) {
            $product = $dao->getList("my_products", $where, $limit, $offset);
            (new Form)->recursive_unset($product, "apikey");
            if (!isset($product["code"])) { //Record exist
                $link = Form::get_pagination("my_products", site_url($this->config->item("api_version") . "/product/list/" . $rfc));
                $APIRes->setResponse(REST_Controller::HTTP_OK, array_merge($product, $link));
            } elseif ($product["code"] > 0) { //There are some error in db
                $APIRes->setResponse(REST_Controller::HTTP_INTERNAL_SERVER_ERROR, array("error" => $product));
            } else if ($product["code"] == 0) { //Record not exist
                $APIRes->setResponse(REST_Controller::HTTP_CONFLICT, array("error" => API_Response::NOT_EXIST));
            }
        } else {
            $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, array("error" => API_Response::EXPIRED_TOKEN));
        }
        $this->set_response($APIRes->getResponse(), $APIRes->getHttpcode());
    }

    function sat_services_products($key) {
        return $this->validation_model->sat_services_products($key);
    }

    function transmitter($key) {
        return $this->validation_model->transmitter($key);
    }

    function sat_unit($key) {
        return $this->validation_model->sat_unit($key);
    }

}
