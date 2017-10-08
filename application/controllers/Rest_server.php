<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Rest_server extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        // Load the library
        $this->load->library('Rest');
    }

    public function index() {

        $this->load->view('rest_server');
    }

    public function login() {

// Set config options (only 'server' is required to work)

        $config = array('server' => base_url(),
                'api_key' => 'Setec_Astronomy',
                'api_name' => 'X-API-KEY'
                //'http_user' 		=> 'username',
                //'http_pass' 		=> 'password',
                //'http_auth' 		=> 'basic',
                //'ssl_verify_peer' => TRUE,
                //'ssl_cainfo' 		=> '/certs/cert.pem'
        );

// Run some setup
//        $this->rest->initialize($config);
//        
//        $this->rest->debug();

// Pull in an array of info
//        $tweets = $this->rest->get('v1/example/users');
//        
//        var_dump($tweets);
    }

}
