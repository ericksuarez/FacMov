<?php

/**
 * Description of Correo
 *
 * @author Erick Suarez Buendia
 */
class Correo {

    var $para;
    var $cc;
    var $asunto;
    var $mensaje;
    var $rechazados;
    var $enviados;
    var $error;

    public function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->library('email');
    }

    private function Setting_Email_Preferences() {
        $config = array(
            'protocol' => 'smtp',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_user' => 'erick.suarez.buendia@gmail.com',
            'smtp_pass' => 'e57913294',
            'smtp_port' => '465',
            'smtp_crypto' => 'ssl',
            'mailtype' => 'html',
            'wordwrap' => TRUE,
            'charset' => 'utf-8'
        );

        $this->CI->email->initialize($config);
    }

    public function enviar() {
        $this->Setting_Email_Preferences();

        $this->CI->email->set_newline("\r\n");
        $this->CI->email->from($this->CI->config->item('supportemail_site'), $this->CI->config->item('name_site'));
        $this->CI->email->to($this->getPara());
        $this->CI->email->cc($this->getCc());

        $this->CI->email->subject($this->getAsunto());
        $this->CI->email->message($this->getMensaje());

        if (!$this->CI->email->send()) {
            $this->setError($this->CI->email->print_debugger());
        }
    }

    public function setAdjunto($archivos) {
        foreach ($archivos as $key => $value) {
            $this->CI->email->attach($this->CI->config->item('RutaAdjunto') . '/' . $value['Documento']);
        }
    }

    function getPara() {
        return $this->para;
    }

    function getCc() {
        return $this->cc;
    }

    function getAsunto() {
        return $this->asunto;
    }

    function getMensaje() {
        return $this->mensaje;
    }

    function getRechazados() {
        return $this->rechazados;
    }

    function getEnviados() {
        return $this->enviados;
    }

    function setPara($para) {
        $this->para = str_replace(";", ",", $para);
    }

    function setCc($cc) {
        $this->cc = str_replace(";", ",", $cc);
    }

    function setAsunto($asunto) {
        $this->asunto = $asunto;
    }

    function setMensaje($mensaje) {
        $this->mensaje = $mensaje;
    }

    function setRechazados($rechazados) {
        $this->rechazados = $rechazados;
    }

    function setEnviados($enviados) {
        $this->enviados = $enviados;
    }

    function getError() {
        return $this->error;
    }

    function setError($error) {
        $this->error = $error;
    }

}
