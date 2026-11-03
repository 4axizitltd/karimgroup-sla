<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

Class Site_controller extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->enforce_sla();
        //$this->load->model("dashboard/notice_m");
        $this->load->model("dashboard/m_common");
        $user_type = $this->session->userdata('user_type');
        $user_id = $this->session->userdata('user_id');
        if (!empty($user_id)) {
            $userData = $this->m_common->get_row_array('users', array('user_id' => $user_id), '*');
            if ($user_type == 1 || $user_type ==3)
                $this->companies = $this->m_common->get_row_array('companies', '', '*');
            else
                $this->companies = $this->m_common->get_row_array('companies', array('id' => $userData[0]['company']), '*');
        }
    }

    /**
     * 4axiz SLA: modules listed in config/sla.php (report) only work with an active SLA.
     */
    private function enforce_sla() {
        $this->load->library('sla_license');
        // Status is needed on every page for the warning popup (see MY_Controller::_output)
        $this->sla_status = $this->sla_license->check();
        if ($this->sla_status['valid'] || !$this->sla_license->is_protected_module($this->router->fetch_module())) {
            return;
        }
        $sla = $this->sla_status;
        if (!headers_sent()) {
            header('HTTP/1.1 403 Forbidden');
        }
        include APPPATH . 'views/sla_blocked.php';
        exit;
    }

    protected function is_logged_in($check = FALSE) {
// inactive in seconds
        $inactive = 1;
        if (!isset($_SESSION['timeout']))
            $_SESSION['timeout'] = time() + $inactive;

        $session_life = time() - $_SESSION['timeout'];

        if ($session_life > $inactive) {
            session_destroy();
            $this->session->unset_userdata('logged_in');
            $this->session->unset_userdata('user_id');
            $this->session->unset_userdata('user_type');
            $this->session->unset_userdata('role');
            $this->session->unset_userdata('username');
            $this->session->unset_userdata('companyId');
            $this->session->unset_userdata('companyName');
            $this->session->sess_destroy();
            redirect_with_msg('login/index', 'Your session is expired');
        }

        $_SESSION['timeout'] = time();
        if ($check == FALSE) {
            return FALSE;
        } else {
            return true;
        }
        //redirect (DBORD);

        /* $id = $_SESSION['user_id'];
          if(!empty($id)) return TRUE;
          else return FALSE; */
    }

    public function reCheckForCokie($pageVal) {

        $this->load->model("dashboard/m_dashboard");
        $cookieVal = base64_decode($this->input->cookie('contms'));

        if (isset($cookieVal) && !empty($cookieVal)) {
            $result = $this->m_dashboard->checkUser($cookieVal);
            $result = $result->result_array();
            if (!empty($result[0]['u_id']) && isset($result[0]['u_id'])) {
                $result = $this->m_dashboard->user_auth($result[0]['user_name'], $result[0]['user_pass']);
                if (!empty($result[0]->u_id) && isset($result[0]->u_id)) {
                    $user_info = array(
                        'user_id' => $result[0]->u_id,
                        'username' => $result[0]->user_name,
                        'auth_module_id' => $result[0]->auth_module_id,
                        'auth_module_name' => $result[0]->auth_module_name,
                        'v_code' => $result[0]->v_code,
                        'logged_in' => TRUE
                    );
                    $this->session->set_userdata($user_info);
                    $this->is_logged_in($this->session->userdata('logged_in'));
                    redirect($pageVal);
                } else {
                    redirect('login');
                }
            } else {
                redirect('login');
            }
        } else {
            redirect('login');
        }
    }

    protected function title($title) {
        $this->data['title'] = 'HR & Payroll ::' . trim($title);
    }

    protected function count_row_of_table($table, $column = 'is_deleted', $column_val = 0) {
        $this->db->where($column, $column_val);
        return $this->db->count_all_results($table);
    }

    public function convert_number_to_words($number) {
        $hyphen = '-';
        $conjunction = ' and ';
        $separator = ' ';
        $negative = 'negative ';
        $decimal = ' point ';
        $dictionary = array(
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            30 => 'thirty',
            40 => 'fourty',
            50 => 'fifty',
            60 => 'sixty',
            70 => 'seventy',
            80 => 'eighty',
            90 => 'ninety',
            100 => 'hundred',
            1000 => 'thousand',
            1000000 => 'million',
            1000000000 => 'billion',
            1000000000000 => 'trillion',
            1000000000000000 => 'quadrillion',
            1000000000000000000 => 'quintillion'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                    'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX, E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . $this->convert_number_to_words(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens = ((int) ($number / 10)) * 10;
                $units = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . $this->convert_number_to_words($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = $this->convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= $this->convert_number_to_words($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }

    /*
      Why function : This function is for language change.
      @  $short_code = this parameter is for detect language
     */

    public function change_language($short_code) {
        if ($short_code == 'bn') {
            $language = 'bangla';
        } else {
            $language = 'english';
        }
        $this->change_language_action($short_code, $language);
        $current_url = $this->session->userdata('current_url');
        redirect($current_url);
    }

    /*
      Why function : This function is for language change action .
      @  $short_code = this parameter is for detect language
      @  $language = this parameter is the language name
     */

    public function change_language_action($short_code, $language) {
        $this->session->set_userdata('short_code', $short_code);
        $this->session->set_userdata('language', $language);
        $this->lang->load($short_code, $language);
    }

}
