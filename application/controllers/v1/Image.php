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
class Image extends REST_Controller {

    var $path_logo = './assets/emisor/';
    var $extension_logo = 'bmp|gif|jpg|jpeg|tiff|tif|png';

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
    public function logo_post() {
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $data = $this->input->post();
        $http_headers = $this->input->request_headers();
        $data["apikey"] = $http_headers["x-api-key"];
        $where = array(
            "rfc" => $data["rfc"],
            "apikey" => $http_headers["x-api-key"]
        );

        if ((new Token)->validated($http_headers)) {
            $this->createFolder($this->path_logo, $data["rfc"]);
            $this->_init_upload($data["rfc"], "Logo", $this->extension_logo);

            if (!$this->upload->do_upload('userfile')) {
                $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, array('error' => $this->upload->display_errors()));
            } else {
                $upload_data = $this->upload->data();
                $image = $dao->create_or_update("upload_file", array_merge($data, $upload_data), $where);
                if (!isset($image["code"])) { //Record exist
                    $msg = (is_array($image)) ? API_Response::UPDATE : API_Response::CREATE;
                    $APIRes->setResponseInsUpd(REST_Controller::HTTP_OK, $upload_data, $msg);
                } elseif ($image["code"] > 0) { //There are some error in db
                    $APIRes->setResponseInsUpd(REST_Controller::HTTP_INTERNAL_SERVER_ERROR, array("error" => $image), API_Response::ERROR);
                } else if ($image["code"] == 0) { //Record not exist
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
    public function logo_get() {
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $rfc = $this->get("rfc");
        $http_headers = $this->input->request_headers();
        $where = array(
            "rfc" => $rfc,
            "apikey" => $http_headers["x-api-key"]
        );

        if ((new Token)->validated($http_headers)) {
            $emisor = $dao->find("upload_file", $where);
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

    public function logo_delete($rfc) {
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $baja = array("status" => "BAJA", "downDate" => date("Y-m-d H:i:s"));
        $http_headers = $this->input->request_headers();
        $where = array(
            "rfc" => $rfc,
            "apikey" => $http_headers["x-api-key"]
        );

        if ((new Token)->validated($this->input->request_headers())) {
            $emisor = $dao->logicalDelete("upload_file", $baja, $where);
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


    private function createFolder($path, $rfc) {
        $month = array(
            "01 Enero", "02 Febrero", "03 Marzo", "04 Abril", "05 Mayo", "06 Junio",
            "07 Julio", "08 Agosto", "09 Septiembre", "10 Octubre", "11 Noviembre", "12 Diciembre"
        );
        $certificados = $path . $rfc . "/Certificados/";
        $logo = $path . $rfc . "/Logo/";
        $cfdi = $path . $rfc . "/CFDi/" . date("Y") . "/";
        if (!file_exists($path . $rfc)) {
            if (!mkdir($certificados, 0777, true)) {
                die('Fallo al crear las carpetas del certificado...');
            }
            if (!mkdir($logo, 0777, true)) {
                die('Fallo al crear las carpetas de logos...');
            }
            if (!mkdir($cfdi, 0777, true)) {
                die('Fallo al crear las carpetas de los CFDi...');
            }
            foreach ($month as $key => $value) {
                if (!mkdir($cfdi . $value . "/", 0777, true)) {
                    die('Fallo al crear las carpetas de los meses...');
                }
            }
        }
    }

    private function _init_upload($rfc, $folder, $extension) {
        $config['upload_path'] = $this->path_logo . $rfc . '/' . $folder . '/';
        $config['allowed_types'] = $extension;
        $config['max_size'] = 2048;
        $config['max_width'] = 0;
        $config['max_height'] = 0;
        $this->load->library('upload', $config);
    }

}
