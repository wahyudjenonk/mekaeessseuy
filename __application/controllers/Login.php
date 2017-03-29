<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends JINGGA_Controller {
	
	function __construct(){
		parent::__construct();
		$this->load->library(array('encrypt','lib'));
	}
	
	public function index(){
		$user = $this->db->escape_str($this->input->post('user'));
		$pass = $this->db->escape_str($this->input->post('pwd'));
		$error=false;
		//echo $user;exit;
		//echo $this->encrypt->encode($pass);exit;
		if($user && $pass){
			$cek_user = $this->mbackend->getdata('data_login', 'row_array', $user);
			//print_r($cek_user);exit;
			if(count($cek_user)>0){
				if(isset($cek_user['status']) && $cek_user['status']==1){
					//echo $this->encrypt->decode($cek_user['pwd']);exit;
					if($pass == $this->encrypt->decode($cek_user['pwd'])){
						$this->session->set_userdata('44mpp3R4', base64_encode(serialize($cek_user)));
					}else{
						$error=true;
						$this->session->set_flashdata('error', 'Password Tidak Benar');
					}
				}else{
					$error=true;
					$this->session->set_flashdata('error', 'USER Sudah Tidak Aktif Lagi');
				}
			}else{
				$error=true;
				$this->session->set_flashdata('error', 'User Tidak Terdaftar');
			}
		}else{
			$error=true;
			$this->session->set_flashdata('error', 'Isi User Dan Password');
		}
		header("Location: " . $this->host ."backoffice");
	
		
	}
	
	function logout(){
		//$log = $this->db->update('tbl_user', array('last_log_date'=>date('Y-m-d')), array('nama_user'=>$this->auth['nama_user']) );
		//if($log){
			$this->session->unset_userdata('44mpp3R4', 'limit');
			$this->session->sess_destroy();
			header("Location: " . $this->host ."backoffice");
		//}
	}
	
	function viewregistrasi(){
		$this->nsmarty->assign('cl_provinsi_id', $this->lib->fillcombo('cl_provinsi', 'return') );
		$this->nsmarty->display( 'backend/main-register.html');
	}
	
	function submitregistrasi(){
		$this->load->library('encrypt');
		
		$post = array();
		foreach($_POST as $k=>$v){
			if($this->input->post($k)!=""){
				$post[$k] = $this->input->post($k);
			}else{
				$post[$k] = null;
			}
		}
		
		//echo "<pre>";
		//print_r($post);exit;
		
		$cek_data = $this->db->get_where('tbl_member', array('email_address'=>$post['edMail']) )->row_array();
		if($cek_data){
			echo "<center>Data Email anda sudah ada dalam system kami!<br/>Silahkan coba dengan email yang lainnya</center>";
			exit;
		}
		
		$array_register = array(
			'registration_date' => date('Y-m-d H:i:s'),
			'nama_lengkap' => $post['edNmLengkap'],
			'email_address' => $post['edMail'],
			'cl_provinsi_id' => $post['edProvID'],
			'cl_kab_kota_id' => $post['edKabKotaID'],
			'cl_kecamatan_id' => $post['edKecID'],
			'kode_pos' => $post['edKdPos'],
			'alamat' => $post['edAlamatLengkap'],
			'no_handphone' => $post['edNoHP'],
		);
		$register = $this->db->insert('tbl_registration', $array_register);
		if($register){
			$getregister = $this->db->get_where('tbl_registration', array('email_address'=>$post['edMail']) )->row_array();
			$pswd = $this->lib->randomString(6, 'huruf');
			//$member_user = $post['edProvID'].$post['edKabKotaID'].$post['edKecID']."-".$this->lib->randomString(5, 'angkahuruf');
			$member_user = $this->lib->randomString(5, 'angkahuruf');
			$array_member = array(
				'member_user' => $member_user,
				'email_address' => $post['edMail'],
				'pwd' => $this->encrypt->encode($pswd),
				'tbl_registration_id' => $getregister['id'],
				'flag' => 1,
				'create_date' => date('Y-m-d H:i:s'),
				'create_by' => "SYS",
			);
			$member = $this->db->insert('tbl_member', $array_member);
			if($member){
				$kirim_email = $this->lib->kirimemail('email_register', $post['edMail'], $pswd, $member_user);
				if($kirim_email){
					echo 1;
				}
			}
		}
		
	}
	
	function viewlupapassword(){
		$this->nsmarty->assign('cl_provinsi_id', $this->lib->fillcombo('cl_provinsi', 'return') );
		$this->nsmarty->display( 'backend/main-forgotpassword.html');
	}

	function submitlupapassword(){
		$cek_data = $this->db->get_where('tbl_member', array('email_address'=>$this->input->post('edMail')) )->row_array();
		if(!$cek_data){
			echo "<center>Data anda tidak ada dalam system kami</center>";
			exit;
		}
		
		$cek_data['password'] = $this->encrypt->decode($cek_data['pwd']);
		$kirim_email = $this->lib->kirimemail('email_lupa_password', $this->input->post('edMail'), $cek_data);
		if($kirim_email){
			echo 1;
		}
		
	}
	
	function getcombo($type=""){
		echo $this->lib->fillcombo($type, 'return');
	}
	
}
