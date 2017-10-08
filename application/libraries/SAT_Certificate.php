<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SAT_Certificate
 *
 * @author Ing. Erick Suárez Buendía
 */
class SAT_Certificate {

    private $_path = '';
    public $_keyPem = '';
    public $_cerPem = '';
    public $_pfx = '';
    private $_return = array();

    function __construct($pathCertificados = null) {
        $this->_path = $pathCertificados;
    }

    private function _estableceError($result, $mensajeError = null, $arrayExtras = null) {
        $this->_return = array();
        $this->_return['number'] = $result;
        if ($mensajeError != null) {
            $this->_return['description'] = $mensajeError;
        }
        if ($arrayExtras != null) {
            foreach ($arrayExtras as $key => $val) {
                $this->_return[$key] = $val;
            }
        }
    }

    function generaKeyPem($nombreKey, $password) {

        ob_start();
        $nombreKey = $this->_path . $nombreKey;
        if (file_exists($nombreKey)) {
            $salida = shell_exec('openssl pkcs8 -inform DER -in ' . $nombreKey . ' -out ' . $nombreKey . '.pem -passin pass:' . $password . ' 2>&1');
            if ($salida == '' || $salida == false || $salida == null) {
                $this->_keyPem = $nombreKey . '.pem';
                $this->_estableceError(CodeCertificate::CERT_OK, CodeCertificate::_CERT_OK_);
                return $this->_return;
            } else if (strpos($salida, 'Error decrypting') !== false) {
                $this->_estableceError(CodeCertificate::BAD_PASS, CodeCertificate::_BAD_PASS_);
                return $this->_return;
            } else {
                $this->_estableceError(CodeCertificate::NOT_CREATE_KEYPEM, CodeCertificate::_NOT_CREATE_KEYPEM_);
                return $this->_return;
            }
        } else {
            $this->_estableceError(CodeCertificate::BAD_PATH, CodeCertificate::_BAD_PATH_);
            return $this->_return;
        }
    }

    function generaCerPem($nombreCer) {

        ob_start();
        $nombreCer = $this->_path . $nombreCer;
        if (file_exists($nombreCer)) {
            $salida = shell_exec("openssl x509 -inform DER -outform PEM -in " . $nombreCer . " -pubkey -out " . $nombreCer . ".pem 2>&1");
            if (strpos($salida, 'BEGIN PUBLIC KEY') !== false) {
                $this->_cerPem = $nombreCer . '.pem';
                $this->_estableceError(CodeCertificate::CERT_OK, CodeCertificate::_CERT_OK_);
                return $this->_return;
            } else {
                $this->_estableceError(CodeCertificate::NOT_CREATE_CERPEM, CodeCertificate::_NOT_CREATE_CERPEM_);
                return $this->_return;
            }
        } else {
            $this->_estableceError(CodeCertificate::BAD_PATH, CodeCertificate::_BAD_PATH_);
            return $this->_return;
        }
    }

    function generaPFX($password, $nombreCerPem = null, $nombreKeyPem = null) {

        if ($nombreCerPem == null || $nombreKeyPem == null) {
            if ($this->_cerPem != null && $this->_keyPem != null) {
                $nombreCerPem = $this->_cerPem;
                $nombreKeyPem = $this->_keyPem;
            } else {
                $nombreKeyPem = $this->_path . 'desconocido.ccg';
                $nombreCerPem = $this->_path . 'desconocido.ccg';
            }
        } else {
            $nombreKeyPem = $this->_path . $nombreKeyPem;
            $nombreCerPem = $this->_path . $nombreCerPem;
        }

        $pfx = explode('.', $nombreKeyPem);
        $pfx = $pfx[0] . '.pfx';

        if (file_exists($nombreKeyPem) && file_exists($nombreCerPem)) {
            $salida = shell_exec('openssl pkcs12 -export -in ' . $nombreCerPem . ' -inkey ' . $nombreKeyPem . ' -out ' . $pfx . ' -passout pass:' . $password . ' 2>&1');
//            $salida = shell_exec('echo 4xBbCfSj | sudo -S openssl pkcs12 -export -inkey ' . $nombreKeyPem . ' -in ' . $nombreCerPem . ' -out ' . $pfx . ' -passout pass:' . $password . ' 2>&1');
//            if (strpos($salida, '[sudo] password for sandbox2014') !== false) {
            if (file_exists($pfx)) {
                $this->_pfx = $pfx;
                $this->_estableceError(CodeCertificate::CERT_OK, $salida);
                return $this->_return;
            } else {
                $this->_estableceError(CodeCertificate::NOT_CREATE_PFX, CodeCertificate::_NOT_CREATE_PFX_);
                return $this->_return;
            }
        } else {
            $this->_estableceError(CodeCertificate::BAD_PATH, CodeCertificate::_BAD_PATH_);
            return $this->_return;
        }
    }

    function getSerialCert($nombreCerPem = null) {

        if ($nombreCerPem == null) {
            if ($this->_cerPem != null) {
                $nombreCerPem = $this->_cerPem;
            } else {
                $nombreCerPem = $this->_path . 'desconocido.ccg';
            }
        } else {
            $nombreCerPem = $this->_path . $nombreCerPem;
        }

        if (file_exists($nombreCerPem)) {
            $salida = shell_exec('openssl x509 -in ' . $nombreCerPem . ' -noout -serial  2>&1');

            if (strpos($salida, 'serial=') !== false) {
                $salida = str_replace('serial=', '', $salida);
                $serial = '';
                for ($i = 0; $i < strlen($salida); $i++) {
                    if ($i % 2 != 0) {
                        $serial .= $salida[$i];
                    }
                }
                $this->_estableceError(CodeCertificate::CERT_OK, CodeCertificate::_CERT_OK_, array('serial' => $serial));
                return $this->_return;
            } else {
                $this->_estableceError(CodeCertificate::NOT_EXIST_SERIAL, CodeCertificate::_NOT_EXIST_SERIAL_);
                return $this->_return;
            }
        } else {
            $this->_estableceError(CodeCertificate::BAD_PATH, CodeCertificate::_BAD_PATH_);
            return $this->_return;
        }
    }

    function getFechaInicio($nombreCerPem = null) {
        if ($nombreCerPem == null) {
            if ($this->_cerPem != null) {
                $nombreCerPem = $this->_cerPem;
            } else {
                $nombreCerPem = $this->_path . 'desconocido.ccg';
            }
        } else {
            $nombreCerPem = $this->_path . $nombreCerPem;
        }

        if (file_exists($nombreCerPem)) {
            $salida = shell_exec('openssl x509 -in ' . $nombreCerPem . ' -noout -startdate 2>&1');
            $salida = trim(str_replace('notBefore=', '', $salida));
            $info_preg = array();
            $salida = str_replace('  ', ' ', $salida);
            preg_match('#([A-z]{3}) ([0-9]{1,2}) ([0-2][0-9]:[0-5][0-9]:[0-5][0-9]) ([0-9]{4})#', $salida, $info_preg);
            if (!empty($info_preg)) {
                $fecha = $info_preg[2] . '-' . $info_preg[1] . '-' . $info_preg[4] . ' ' . $info_preg[3];
                $this->_estableceError(CodeCertificate::CERT_OK, CodeCertificate::_CERT_OK_, array('fecha_inicio' => $fecha));
                return $this->_return;
            } else {
                $this->_estableceError(CodeCertificate::NOT_DATE_TO . CodeCertificate::_NOT_DATE_TO_);
                return $this->_return;
            }
        } else {
            $this->_estableceError(CodeCertificate::BAD_PATH, CodeCertificate::_BAD_PATH_);
            return $this->_return;
        }
    }

    function getFechaTermino($nombreCerPem = null) {
        if ($nombreCerPem == null) {
            if ($this->_cerPem != null) {
                $nombreCerPem = $this->_cerPem;
            } else {
                $nombreCerPem = $this->_path . 'desconocido.ccg';
            }
        } else {
            $nombreCerPem = $this->_path . $nombreCerPem;
        }

        if (file_exists($nombreCerPem)) {
            $salida = shell_exec('openssl x509 -in ' . $nombreCerPem . ' -noout -enddate 2>&1');
            $salida = str_replace('notAfter=', '', $salida);
            $info_preg = array();
            $salida = str_replace('  ', ' ', $salida);
            preg_match('#([A-z]{3}) ([0-9]{1,2}) ([0-2][0-9]:[0-5][0-9]:[0-5][0-9]) ([0-9]{4})#', $salida, $info_preg);
            if (!empty($info_preg)) {
                $fecha = $info_preg[2] . '-' . $info_preg[1] . '-' . $info_preg[4] . ' ' . $info_preg[3];
                $this->_estableceError(CodeCertificate::CERT_OK, CodeCertificate::_CERT_OK_, array('fecha_termino' => $fecha));
                return $this->_return;
            } else {
                $this->_estableceError(CodeCertificate::NOT_DATE_FROM . CodeCertificate::_NOT_DATE_FROM_);
                return $this->_return;
            }
        } else {
            $this->_estableceError(CodeCertificate::BAD_PATH, CodeCertificate::_BAD_PATH_);
            return $this->_return;
        }
    }

    function es_CSD_or_FIEL($nombreCerPem = null) {
        if ($nombreCerPem == null) {
            if ($this->_cerPem != null) {
                $nombreCerPem = $this->_cerPem;
            } else {
                $nombreCerPem = $this->_path . 'desconocido.ccg';
            }
        } else {
            $nombreCerPem = $this->_path . $nombreCerPem;
        }

        if (file_exists($nombreCerPem)) {
            $salida = shell_exec('openssl x509 -in ' . $nombreCerPem . ' -noout -subject 2>&1');
            $salida = str_replace('notBefore=', '', $salida);
            $info_preg = array();
            preg_match('#/OU=(.*)#', $salida, $info_preg);
            if (!empty($info_preg)) {
                $this->_estableceError(CodeCertificate::CERT_OK, CodeCertificate::_CERT_OK_, array('OU' => $info_preg[1]));
                return $this->_return;
            } else {
                $this->_estableceError(CodeCertificate::VALIDATION_FAILED, CodeCertificate::_VALIDATION_FAILED_);
                return $this->_return;
            }
        } else {
            $this->_estableceError(CodeCertificate::BAD_PATH, CodeCertificate::_BAD_PATH_);
            return $this->_return;
        }
    }

    function getFechaVigencia($fechaInicio, $fechaTermino) {
        $formatTo = DateTime::createFromFormat('j-M-Y H:i:s', $fechaInicio);
        $to = $formatTo->format("Y-m-d H:i:s");
        $formatFrom = DateTime::createFromFormat('j-M-Y H:i:s', $fechaTermino);
        $from = $formatFrom->format("Y-m-d H:i:s");
        $formatHoy = new DateTime('NOW');
        $hoy = $formatHoy->format("Y-m-d H:i:s");

        if ($hoy >= $to && $hoy <= $from) {
            $this->_estableceError(CodeCertificate::CERT_OK, CodeCertificate::_CERT_OK_);
            return $this->_return;
        } else {
            $this->_estableceError(CodeCertificate::VALIDITY_VALIDATION_FAILED, CodeCertificate::_VALIDITY_VALIDATION_FAILED_);
            return $this->_return;
        }
    }

    function pareja($nombreCerPem = null, $nombreKeyPem = null) {

        if ($nombreCerPem == null || $nombreKeyPem == null) {
            if ($this->_cerPem != null && $this->_keyPem != null) {
                $nombreCerPem = $this->_cerPem;
                $nombreKeyPem = $this->_keyPem;
            } else {
                $nombreKeyPem = $this->_path . 'desconocido.ccg';
                $nombreCerPem = $this->_path . 'desconocido.ccg';
            }
        } else {
            $nombreKeyPem = $this->_path . $nombreKeyPem;
            $nombreCerPem = $this->_path . $nombreCerPem;
        }


        if (file_exists($nombreCerPem) && file_exists($nombreKeyPem)) {
            $salidaCer = shell_exec('openssl x509 -noout -modulus -in ' . $nombreCerPem . ' 2>&1');
            $salidaKey = shell_exec('openssl rsa -noout -modulus -in ' . $nombreKeyPem . ' 2>&1');
            if ($salidaCer == $salidaKey) {
                $this->_estableceError(CodeCertificate::CERT_OK, CodeCertificate::_CERT_OK_);
                return $this->_return;
            } else {
                $this->_estableceError(CodeCertificate::NOT_PAIR, CodeCertificate::_NOT_PAIR_);
                return $this->_return;
            }
        } else {
            $this->_estableceError(CodeCertificate::BAD_PATH, CodeCertificate::_BAD_PATH_);
            return $this->_return;
        }
    }

}

class CodeCertificate {

    const CERT_OK = 200;
    const BAD_PASS = 210;
    const NOT_CREATE_KEYPEM = 220;
    const NOT_CREATE_CERPEM = 240;
    const NOT_CREATE_PFX = 250;
    const BAD_PATH = 230;
    const NOT_EXIST_SERIAL = 260;
    const NOT_DATE_TO = 270;
    const NOT_DATE_FROM = 280;
    const VALIDATION_FAILED = 290;
    const VALIDITY_VALIDATION_FAILED = 300;
    const NOT_PAIR = 310;
    const _CERT_OK_ = "Completado con exito";
    const _BAD_PASS_ = "Contraseña del certificado incorrecta";
    const _NOT_CREATE_KEYPEM_ = "Error al crear el archivo key.pem";
    const _NOT_CREATE_CERPEM_ = "Error al crear el archivo cer.pem";
    const _NOT_CREATE_PFX_ = "Error al crear el archivo PFX";
    const _BAD_PATH_ = "El archivo no se encontro fisicamente en la ruta";
    const _NOT_EXIST_SERIAL_ = "No se logro obtener el serial del certificado";
    const _NOT_DATE_TO_ = "No se logro obtener la fecha de inicio del certificado";
    const _NOT_DATE_FROM_ = "No se logro obtener la fecha de termino del certificado";
    const _VALIDATION_FAILED_ = "El certificado es de tipo FIEL. Solo son permitidos los de tipo CSD";
    const _VALIDITY_VALIDATION_FAILED_ = "Validacion fallida de la fecha de vigencia del certificado";
    const _NOT_PAIR_ = "Los archivos no son pareja";

}
