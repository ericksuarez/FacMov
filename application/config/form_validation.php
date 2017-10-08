<?php

/**
 * Description of form_validation
 *
 * @author Ing. Erick Suárez Buendía
 */
$config = array(
    'login' => array(
        array(
            'field' => 'username',
            'label' => 'Username',
            'rules' => 'required|alpha|trim'
        ),
        array(
            'field' => 'password',
            'label' => 'Password',
            'rules' => 'required|trim'
        )
    ),
    'emisor' => array(
        array(
            'field' => 'rfc',
            'label' => 'RFC',
            'rules' => 'required|trim'
        ),
        array(
            'field' => 'bussinessName',
            'label' => 'Razon Social',
            'rules' => 'required|trim'
        ),
        array(
            'field' => 'email',
            'label' => 'Correo',
            'rules' => 'required|valid_email|trim'
        ),
        array(
            'field' => 'nickname',
            'label' => 'Alias',
            'rules' => 'alpha|trim'
        ),
        array(
            'field' => 'kindofFile',
            'label' => 'Tipo de Documento',
            'rules' => 'required|callback_sat_document_type'
        ),
        array(
            'field' => 'note',
            'label' => 'Notas',
            'rules' => 'trim'
        ),
        array(
            'field' => 'website',
            'label' => 'Pagina Web',
            'rules' => 'trim'
        )
    ),
    'receptor' => array(
        array(
            'field' => 'rfc',
            'label' => 'RFC',
            'rules' => 'required|trim'
        ),
        array(
            'field' => 'bussinessName',
            'label' => 'Razon Social',
            'rules' => 'required|trim'
        ),
        array(
            'field' => 'email',
            'label' => 'Correo',
            'rules' => 'required|valid_email|trim'
        ),
        array(
            'field' => 'emailCC',
            'label' => 'Correo con Copia',
            'rules' => 'trim'
        ),
        array(
            'field' => 'note',
            'label' => 'Notas',
            'rules' => 'trim'
        )
    ),
    'address' => array(
        array(
            'field' => 'rfc',
            'label' => 'RFC',
            'rules' => 'required|trim'
        ),
        array(
            'field' => 'street',
            'label' => 'Calle',
            'rules' => 'alpha|trim'
        ),
        array(
            'field' => 'interiorNum',
            'label' => 'Núm. Interior',
            'rules' => 'trim'
        ),
        array(
            'field' => 'outdoorNum',
            'label' => 'Núm. Exterior',
            'rules' => 'trim'
        ),
        array(
            'field' => 'country',
            'label' => 'País',
            'rules' => 'required|callback_sat_country'
        ),
        array(
            'field' => 'state',
            'label' => 'Estado',
            'rules' => 'trim'
        ),
        array(
            'field' => 'township',
            'label' => 'Delegación / Municipio',
            'rules' => 'trim'
        ),
        array(
            'field' => 'zip',
            'label' => 'Codígo Postal',
            'rules' => 'required|callback_sat_code_postal'
        ),
        array(
            'field' => 'colony',
            'label' => 'Colonia',
            'rules' => 'trim'
        ),
        array(
            'field' => 'city',
            'label' => 'Ciudad',
            'rules' => 'trim'
        )
    ),
    'my_products' => array(
        array(
            'field' => 'rfc',
            'label' => 'RFC',
            'rules' => 'required|callback_transmitter'
        ),
        array(
            'field' => 'c_ClaveProdServ',
            'label' => 'Codigo de Producto o Servicio',
            'rules' => 'required|numeric|callback_sat_services_products'
        ),
        array(
            'field' => 'unity',
            'label' => 'Tipo Unidad',
            'rules' => 'required|callback_sat_unit'
        ),
        array(
            'field' => 'priceUnitary',
            'label' => 'Precio Unitario',
            'rules' => 'required|numeric'
        )
    )
);
