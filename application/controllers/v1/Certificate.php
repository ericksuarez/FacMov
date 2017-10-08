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
class Certificate extends REST_Controller {

    var $path_cert = './assets/emisor/';
    var $extension_cert = '*';
    var $http_code;
    var $data = array();
    var $msg;

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
    public function cfdi_post() {
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
            $this->createFolder($this->path_cert, $data["rfc"]);
            $this->_init_upload($data["rfc"], "Certificados", $this->extension_cert);

            if (isset($_FILES["cer"]) && isset($_FILES["key"])) {
                $this->_do_upload_success("cer", $data, $dao, $where);
                $this->_do_upload_success("key", $data, $dao, $where);
                $APIRes->setResponseInsUpd($this->http_code, $this->data, $this->msg);
            } elseif (isset($_FILES["cer"])) {
                if ($_FILES["cer"]["type"] == "application/x-x509-ca-cert") {
                    $this->_do_upload_success("cer", $data, $dao, $where);
                    $APIRes->setResponseInsUpd($this->http_code, $this->data, $this->msg);
                } else {
                    $APIRes->setResponseInsUpd(REST_Controller::HTTP_BAD_REQUEST, array("error" => "El tipo de archivo no esta permitido para [CER]"), API_Response::ERROR);
                }
            } elseif (isset($_FILES["key"])) {
                if ($_FILES["key"]["type"] == "application/octet-stream") {
                    $this->_do_upload_success("key", $data, $dao, $where);
                    $APIRes->setResponseInsUpd($this->http_code, $this->data, $this->msg);
                } else {
                    $APIRes->setResponseInsUpd(REST_Controller::HTTP_BAD_REQUEST, array("error" => "El tipo de archivo no esta permitido  para [KEY]"), API_Response::ERROR);
                }
            } else {
                $APIRes->setResponseInsUpd(REST_Controller::HTTP_BAD_REQUEST, array("error" => "El tipo de archivo no esta permitido"), API_Response::ERROR);
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
    public function cfdi_get() {
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $rfc = $this->get("rfc");
        $ext = $this->get("file");
        $http_headers = $this->input->request_headers();
        $where = array(
            "rfc" => $rfc,
            "apikey" => $http_headers["x-api-key"],
            "file_ext" => "." . $ext
        );

        if ((new Token)->validated($http_headers)) {
            $cfdi = $dao->find("upload_file", $where);
            if (!isset($cfdi["code"])) { //Record exist
                $APIRes->setResponse(REST_Controller::HTTP_OK, $cfdi);
            } elseif ($cfdi["code"] > 0) { //There are some error in db
                $APIRes->setResponse(REST_Controller::HTTP_INTERNAL_SERVER_ERROR, array("error" => $cfdi));
            } else if ($cfdi["code"] == 0) { //Record not exist
                $APIRes->setResponse(REST_Controller::HTTP_CONFLICT, array("error" => API_Response::NOT_EXIST));
            }
        } else {
            $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, array("error" => API_Response::EXPIRED_TOKEN));
        }
        $this->set_response($APIRes->getResponse(), $APIRes->getHttpcode());
    }

    public function cfdi_delete($rfc, $ext) {
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $baja = array("status" => "BAJA", "downDate" => date("Y-m-d H:i:s"));
        $http_headers = $this->input->request_headers();
        $where = array(
            "rfc" => $rfc,
            "apikey" => $http_headers["x-api-key"],
            "file_ext" => "." . $ext
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
            "rfc" => $rfc,
            "apikey" => $http_header["x-api-key"],
            "status" => "ACTIVO"
        );

        if ((new Token)->validated($http_header)) {
            $cfdi = $dao->getList("upload_file", $where, $limit, $offset);
            (new Form)->recursive_unset($cfdi, "apikey");
            if (!isset($cfdi["code"])) { //Record exist
                $link = Form::get_pagination("upload_file", site_url($this->config->item("api_version") . "/certificate/list/" . $rfc));
                $APIRes->setResponse(REST_Controller::HTTP_OK, array_merge($cfdi, $link));
            } elseif ($cfdi["code"] > 0) { //There are some error in db
                $APIRes->setResponse(REST_Controller::HTTP_INTERNAL_SERVER_ERROR, array("error" => $cfdi));
            } else if ($cfdi["code"] == 0) { //Record not exist
                $APIRes->setResponse(REST_Controller::HTTP_CONFLICT, array("error" => API_Response::NOT_EXIST));
            }
        } else {
            $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, array("error" => API_Response::EXPIRED_TOKEN));
        }
        $this->set_response($APIRes->getResponse(), $APIRes->getHttpcode());
    }

    public function sing_post() {
        $this->load->library("SAT_Certificate", "certif");
        $dao = new GenericDAO();
        $APIRes = new API_Response();
        $data = $this->input->post();
        $http_headers = $this->input->request_headers();
        $whereCER = array("rfc" => $data["rfc"], "apikey" => $http_headers["x-api-key"], "file_ext" => ".cer");
        $whereKEY = array("rfc" => $data["rfc"], "apikey" => $http_headers["x-api-key"], "file_ext" => ".key");
        
        if ((new Token)->validated($http_headers)) {
            $cer = $dao->find("upload_file", $whereCER);
            $key = $dao->find("upload_file", $whereKEY);
            if (!isset($cer["code"]) && !isset($key["code"])) { //Record exist
                $sat_certs = new SAT_Certificate($cer["file_path"]);
                $keypem = $sat_certs->generaKeyPem($key["file_name"], $data["cerpassword"]);
                if ($keypem["number"] === CodeCertificate::CERT_OK) {
                    $cerpem = $sat_certs->generaCerPem($cer["file_name"]);
                    if ($cerpem["number"] === CodeCertificate::CERT_OK) {
                        $this->validation_sat_certificate($cer["file_path"], $key["file_name"] . ".pem", $cer["file_name"] . ".pem", $data["cerpassword"], $APIRes);
                    } else {
                        $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, array("error" => $cerpem));
                    }
                } else {
                    $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, array("error" => $keypem));
                }
            } elseif ($cer["code"] > 0 || $key["code"] > 0) { //There are some error in db
                $APIRes->setResponse(REST_Controller::HTTP_INTERNAL_SERVER_ERROR, array("error" => array_merge($cer, $key)));
            } else if ($cer["code"] == 0 || $key["code"] == 0) { //Record not exist
                $APIRes->setResponse(REST_Controller::HTTP_CONFLICT, array("error" => API_Response::NOT_EXIST));
            }
        } else {
            $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, array("error" => API_Response::EXPIRED_TOKEN));
        }
        $this->set_response($APIRes->getResponse(), $APIRes->getHttpcode());
    }

    private function validation_sat_certificate($path, $nombreKeyPem, $nombreCerPem, $password, $APIRes) {

        $sat_certs = new SAT_Certificate($path);

        $validCer = $sat_certs->es_CSD_or_FIEL($nombreCerPem);
        if ($validCer["number"] === CodeCertificate::CERT_OK) {
            $pareja = $sat_certs->pareja($nombreCerPem, $nombreKeyPem);
            if ($pareja["number"] === CodeCertificate::CERT_OK) {
                $fecha_inicio = $sat_certs->getFechaInicio($nombreCerPem);
                if ($fecha_inicio["number"] === CodeCertificate::CERT_OK) {
                    $fecha_termino = $sat_certs->getFechaTermino($nombreCerPem);
                    if ($fecha_termino["number"] === CodeCertificate::CERT_OK) {
                        $fecha_vigencia = $sat_certs->getFechaVigencia($fecha_inicio["fecha_inicio"], $fecha_termino["fecha_termino"]);
                        if ($fecha_vigencia["number"] === CodeCertificate::CERT_OK) {
                            $serialCer = $sat_certs->getSerialCert($nombreCerPem);
                            if ($serialCer["number"] === CodeCertificate::CERT_OK) {
                                $pfx = $sat_certs->generaPFX($password, $nombreCerPem, $nombreKeyPem);
                                if ($pfx["number"] === CodeCertificate::CERT_OK) {
                                    $APIRes->setResponse(REST_Controller::HTTP_OK, API_Response::PFX_OK);
                                } else {
                                    $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, array("error" => $pfx));
                                }
                            } else {
                                $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, array("error" => $serialCer));
                            }
                        } else {
                            $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, array("error" => $fecha_vigencia));
                        }
                    } else {
                        $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, array("error" => $fecha_termino));
                    }
                } else {
                    $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, array("error" => $fecha_inicio));
                }
            } else {
                $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, array("error" => $pareja));
            }
        } else {
            $APIRes->setResponse(REST_Controller::HTTP_BAD_REQUEST, array("error" => $validCer));
        }
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
        $config['upload_path'] = $this->path_cert . $rfc . '/' . $folder . '/';
        $config['overwrite'] = TRUE;
        $config['encrypt_name'] = TRUE;
        $config['allowed_types'] = $extension;
        $config['max_size'] = 2048;
        $config['max_width'] = 0;
        $config['max_height'] = 0;
        $this->load->library('upload', $config);
        // Alternately you can set preferences by calling the ``initialize()`` method. Useful if you auto-load the class:
        $this->upload->initialize($config);
    }

    private function _do_upload_success($uploadField, $data, $dao, $where) {
        if (!$this->upload->do_upload($uploadField)) {
            $this->http_code = REST_Controller::HTTP_BAD_REQUEST;
            $this->data = array('error' => $this->upload->display_errors());
            $this->msg = "";
        } else {
            $upload_data = $this->upload->data();
            $where["file_ext"] = $upload_data["file_ext"];
            $certs = $dao->create_or_update("upload_file", array_merge($data, $upload_data), $where);
            if (!isset($certs["code"])) { //Record exist
                $msg = (is_array($certs)) ? API_Response::UPDATE : API_Response::CREATE;
                $this->http_code = REST_Controller::HTTP_OK;
                $this->data[$upload_data["file_ext"]] = $upload_data;
                $this->msg = $msg;
            } elseif ($certs["code"] > 0) { //There are some error in db
                $this->http_code = REST_Controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->data[$upload_data["file_ext"]] = array("error" => $certs);
                $this->msg = API_Response::ERROR;
            } else if ($certs["code"] == 0) { //Record not exist
                $this->http_code = REST_Controller::HTTP_CONFLICT;
                $this->data[$upload_data["file_ext"]] = array("error" => API_Response::UNAUTHORIZED);
                $this->msg = API_Response::ERROR;
            }
        }
    }

}
