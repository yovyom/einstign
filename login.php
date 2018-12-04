<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/* Author: Diwakar Pandey
 * Description: Login controller class
 */

class Login extends MX_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('login_model');
        $this->load->library('form_validation');
		$this->load->library('session');
    }

    public function index() {

        if ($this->session->userdata('logged_in')) {
			if($this->session->userdata('user_role') == 1)
				redirect('teacherDashboard');
			else 
				redirect('dashboard');
        }

        $data['msg'] = "";
        $this->load->view('login_view', $data);
    }

    public function forgotpassword() {
        if ($this->uri->segment(3) == "fail") {
            $data['error'] = $this->lang->line("forgot_email_notfound");
        } else {
            $data['error'] = "";
        }
        $this->template->write_view('content', 'login_forgot', $data);
        $this->template->render();
    }

    public function chkforgotpass() {
        $email = $this->input->post('email');
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('email', 'Email Address', 'trim|required|email|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $data['msg'] = "";
            $data['error'] = "";
            $this->template->write_view('content', 'login_forgot', $data);
            $this->template->render();
        } else {

            $row = $this->login_model->checkEmail($email);
            if (is_object($row)) {


                $password = $this->login_model->mosMakePassword(8);
                $data['password'] = hash_make($password);
                $name = $row->name . " " . $row->lname;
                $subject = $this->lang->line("forgot_password_sub");
                $message = $this->lang->line('common_mail_header');
                $message.= sprintf($this->lang->line("forgot_password_body"), $name, '#', $password);
                $message.= $this->lang->line('common_mail_footer');
                $toEmail = $row->email;
                if ($this->login_model->updatePassword($data, $row->id)) {
                    mailSend($toEmail, '', '', $this->lang->line('common_from_emailid'), $this->lang->line('common_from_name'), $subject, $message);
                    redirect('login/index/y');
                }
            } else {
                redirect('login/forgotpassword/fail');
            }
        }
    }

    public function chklogin() {

        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }
        $this->form_validation->set_rules('email', 'Email', 'required|xss_clean|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        if ($this->form_validation->run() == FALSE) {
            $data['msg'] = "User ID or Password is invalid.";
            
            $this->load->view('login_view', $data);
        } else {
              $email = $this->input->post('email');  
              $password = $this->input->post('password'); 
             
             $result = $this->check_username($email,$password);
           
             if($result==TRUE) {
                    redirect('dashboard');
             }else{
                   $data['msg'] = "<div style='color:red'>Invaid Email-id or Password</div>";
                    $this->load->view('login_view', $data);
             }           
                    
        }
    }

    function check_username($email,$password) {
        
        $row = $this->login_model->login($email,$password); 				
		//echo "<pre>"; print_r($row); die;
        if ($row) {
            $sess_array = array();
            $sess_array = array('id_user' => $row->id_user, 'user_name' => $row->user_name, 'user_role' => $row->user_role,  'logged_in' => TRUE);
            //$this->session->set_userdata('logged_in', $sess_array);
			$this->session->set_userdata($sess_array); 
			//echo "<pre>"; print_r($this->session->userdata['logged_in']); die;
            return TRUE;
        } else {

            return FALSE;
        }
    }

    public function logout() {      

        $this->session->sess_destroy();
        $this->data['user'] = '';
        redirect('login');
    }

}

/* End of file login.php */
/* Location: ./application/modules/login/controllers/login.php */