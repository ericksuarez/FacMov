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
class Address extends REST_Controller {

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
    public function fiscal_post() {
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $data = $this->input->post();

        if ((new Token)->validated($this->input->request_headers())) {
            if ($this->form_validation->run('address') == FALSE) {
                $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, validation_errors());
            } else {
                $address = $dao->create_or_update("address", Form::tokenRemove($data), array("rfc" => $data["rfc"]));

                if (!isset($address["code"])) { //Record exist
                    $msg = (is_array($address)) ? API_Response::UPDATE : API_Response::CREATE;
                    $APIRes->setResponseInsUpd(REST_Controller::HTTP_OK, $dao->find("address", array("rfc" => $data["rfc"])), $msg);
                } elseif ($address["code"] > 0) { //There are some error in db
                    $APIRes->setResponseInsUpd(REST_Controller::HTTP_INTERNAL_SERVER_ERROR, array("error" => $address), API_Response::ERROR);
                } else if ($address["code"] == 0) { //Record not exist
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
    public function fiscal_get() {
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $rfc = $this->get("rfc");

        if ((new Token)->validated($this->input->request_headers())) {
            $address = $dao->find("address", array("rfc" => $rfc));
            if (!isset($address["code"])) { //Record exist
                $APIRes->setResponse(REST_Controller::HTTP_OK, $address);
            } elseif ($address["code"] > 0) { //There are some error in db
                $APIRes->setResponse(REST_Controller::HTTP_INTERNAL_SERVER_ERROR, array("error" => $address));
            } else if ($address["code"] == 0) { //Record not exist
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
    public function fiscal_delete($rfc) {
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $baja = array("status" => "BAJA", "downDate" => date("Y-m-d H:i:s"));

        if ((new Token)->validated($this->input->request_headers())) {
            $emisor = $dao->logicalDelete("address", $baja, array("rfc" => $rfc));
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
     * Class Address get return a list of all receptor
     * @param string $rfc customer or trasmitter address
     * @return array (http code, [code,data])
     */
    public function list_get($limit = 10, $offset = 0) {
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $link = array();

        if ((new Token)->validated($this->input->request_headers())) {
            $address = $dao->getList("address", NULL, $limit, $offset);
            (new Form)->recursive_unset($address, "apikey");
            if (!isset($address["code"])) { //Record exist
                $link = Form::get_pagination("address", site_url($this->config->item("api_version") . "/address/list"));
                $APIRes->setResponse(REST_Controller::HTTP_OK, array_merge($address, $link));
            } elseif ($address["code"] > 0) { //There are some error in db
                $APIRes->setResponse(REST_Controller::HTTP_INTERNAL_SERVER_ERROR, array("error" => $address));
            } else if ($address["code"] == 0) { //Record not exist
                $APIRes->setResponse(REST_Controller::HTTP_CONFLICT, array("error" => API_Response::NOT_EXIST));
            }
        } else {
            $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, array("error" => API_Response::EXPIRED_TOKEN));
        }
        $this->set_response($APIRes->getResponse(), $APIRes->getHttpcode());
    }

    function sat_country($key) {
        return $this->validation_model->sat_country($key);
    }

    function sat_code_postal($key) {
        return $this->validation_model->sat_code_postal($key);
    }

}
