<?php

defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of API_Response
 *
 * @author Ing. Erick Suárez Buendía
 */
class API_Response extends CI_Controller {

    var $response;
    var $httpcode;
    var $data;

    const UNAUTHORIZED = "EL Usuario y/o Contraseña son invalidos.";
    const ALREADY_EXISTS = "EL Email ya se encuentra registrado.";
    const CREATED_ERROR = "Error el crear el Proveedor. El RFC ya existe.";
    const CREATED_ERROR_CUSTOMER = "Error el crear el Cliente. El RFC ya existe.";
    const RFC_EXIST = "El RFC capturado ya se encuntra actualmente en uso.";
    const PASSWORD_ERROR = "Error al guardar la contraseña de los certificados.";
    const QUERY_EMPTY = "No existen registros para consulta.";
    const UPD_PRODUCT = "Se ha modificado de manera correcta el producto.";
    const NOT_EXIST = "El Registro no éxiste para este RFC.";
    const EXPIRED_TOKEN = "El Token ha expirado. Favor de iniciar sesion de nuevo.";
    const CREATE = "Registro Creado correctamente.";
    const UPDATE = "Registro Actualizado correctamente.";
    const DELETE = "Registro Eliminado correctamente.";
    const ERROR = "El registro tiene errores.";
    const PFX_OK = "El archivo PFX se creo correctamente";

    public function __construct() {
        
    }

    public function getHttpcode() {
        return $this->httpcode;
    }

    public function getData() {
        return $this->data;
    }

    public function setHttpcode($httpcode) {
        $this->httpcode = $httpcode;
    }

    public function setData($data) {
        $this->data = $data;
    }

    public function setResponse($httpcode, $data) {
        $this->httpcode = $httpcode;
        $this->data = $data;
        $this->response = ['code' => $httpcode, 'data' => $data];
    }

    public function setResponseInsUpd($httpcode, $data, $msg) {
        $this->httpcode = $httpcode;
        $this->data = $data;
        $this->response = ['code' => $httpcode, 'message' => $msg, 'data' => $data];
    }

    public function setResponseList($httpcode, $data, $msg) {
        $this->httpcode = $httpcode;
        $this->data = $data;
        $this->response = ['code' => $httpcode, 'message' => $msg, 'data' => $data];
    }

    public function getResponse() {
        if (isset($this->response["data"]["apikey"])) {
            unset($this->response["data"]["apikey"]);
        }
        return $this->response;
    }

}
