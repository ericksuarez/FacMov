<?php

/**
 * Description of Form
 *
 * @author Ing. Erick Suárez Buendía
 */
class Form {

    public function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->helper('security');
    }

    public function clean($form) {

        $clean = array();
        foreach ($form as $key => $value) {
            $clean[$key] = json_decode($value);
        }
        return $clean;
    }

    public function xss_clean($post) {

        $clean = array();
        $clean_xss = array();
        foreach ($post as $key => $value) {
            if ($key != "token") {
                $clean_xss[$key] = $this->CI->input->post($key, TRUE);
            }
        }
        $clean = $this->clean($clean_xss);
        return $clean;
    }

    public static function tokenRemove($post) {
        unset($post["token"]);
        return $post;
    }

    public static function get_pagination($table, $url) {
        $CI = & get_instance();
        $total_rows = $CI->db->count_all($table);
        $j = 10;
        for ($i = 0; $i <= $total_rows; $i += 10) {
            $links["link_" . ($i / 10)] = $url . "/" . $j . "/" . $i;
            $j += 10;
        }
        $link["pagination"] = array(
            "total_rows" => $total_rows,
            "all_links" => $links,
            "first" => $url . "/10/0",
            "last" => $url . "/" . $total_rows . "/" . ($total_rows - ($total_rows % 10))
        );
        return $link;
    }

    public function recursive_unset(&$array, $unwanted_key) {
        unset($array[$unwanted_key]);
        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->recursive_unset($value, $unwanted_key);
            }
        }
    }

}
