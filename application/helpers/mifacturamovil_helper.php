<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 999999.999 - entregado, mostrar pasado
 */

function clean_form($form) {
    $clean = array();
    foreach ($form as $key => $value) {
        $clean[$key] = json_decode($value);
    }
    return $clean;
}

function config_page($title,$smalltitle,$show_seccion_edit,$show_button,$active_input,$always_block) {
    $conf = array(
        "seo" => trim($title),
        "title" => trim($title)." <small>".trim($smalltitle)."</small>",
        "show_seccion_edit" => trim($show_seccion_edit), //hide
        "show_button" => trim($show_button), //hide
        "active_input" => trim($active_input), //disabled
        "always_block" => trim($always_block), //disabled
    );
    
    return $conf;
}
