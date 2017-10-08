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
 * @category        Controller
 * @author          Ing. Erick Suarez Buendia
 */
class Authorize extends REST_Controller {

    function __construct() {
        // Construct the parent class
        parent::__construct();
    }

    public function login_get() {
        
        $this->form_validation->set_data(array("username" => $this->get('username'), "password" => $this->get('password')));
        $API_Response = new API_Response();
        
        if ($this->form_validation->run('login') == FALSE) {
            $API_Response->setResponse(REST_Controller::HTTP_BAD_REQUEST, validation_errors());
            
        } else {
            $dao = new GenericDAO();
            $infouser = $dao->find("user", array("user" => $this->get('username'), "password" => $this->get('password')));
            
            if (!isset($infouser["code"]) && !empty($infouser)) {
                $API_Response->setResponse(REST_Controller::HTTP_OK, array("token" =>Token::getToken($this->input->server("x-api-key"), $infouser)));
                
            } elseif (isset($infouser["code"]) && $infouser["code"] > 0) {
                $API_Response->setResponse(REST_Controller::HTTP_INTERNAL_SERVER_ERROR, array("error" => $infouser));
                
            } else if ($infouser["code"] == 0) {
                $API_Response->setResponse(REST_Controller::HTTP_CONFLICT,array("error" => API_Response::UNAUTHORIZED));
            }
        }

        $response = $API_Response->getResponse();
        $this->set_response($response, $API_Response->getHttpcode());
    }


}
