<?php

/*
 * Author: fouraxiz
 * Purpose: SLA activation (reports require an active SLA from sla.4axiz.com)
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Sla extends Site_Controller {

    function __construct() {
        parent::__construct();
        if (!$this->is_logged_in($this->session->userdata('logged_in'))) {
            redirect('login');
        }
        $this->setTemplateFile('setting');
    }

    function index() {
        $this->page_name = 'sla';
        $this->title("SLA Activation");
        $data['sla'] = $this->sla_license->check();
        $data['state'] = $this->sla_license->state();
        $data['online'] = $this->sla_license->online_enabled();
        $this->load->view('v_sla', $data);
    }

    function activate() {
        $code = $this->input->post('code');
        $result = $this->sla_license->activate($code);
        $this->session->set_flashdata('sla_result', $result);
        redirect('setting/sla');
    }

}
