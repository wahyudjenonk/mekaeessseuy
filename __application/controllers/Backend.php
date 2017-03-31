<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

class Backend extends JINGGA_Controller {
	
	function __construct(){
		parent::__construct();
		if(!$this->auth){
			$this->nsmarty->display('backend/main-login.html');
			exit;
		}
		$this->nsmarty->assign('acak', md5(date('H:i:s')) );
		$this->temp="backend/";
		$this->load->model('mbackend');
		$this->load->library(array('encrypt','lib'));
	}
	
	function index(){
		if($this->auth){
			$this->nsmarty->display( 'backend/main-backend.html');
		}else{
			$this->nsmarty->display( 'backend/main-login.html');
		}
	}
	
	function modul($p1,$p2){
		if($this->auth){
			switch($p1){
				case "beranda":
					$data=$this->mbackend->getdata('dashboard','result_array');
					//echo "<pre>";print_r($data);
					$this->nsmarty->assign('data',$data);
				break;
				case "transaksi":
					
				break;
				case "setting":
					
				break;
			}
			
			$this->nsmarty->assign("main", $p1);
			$this->nsmarty->assign("mod", $p2);
			$temp = 'backend/modul/'.$p1.'/'.$p2.'.html';
			if(!file_exists($this->config->item('appl').APPPATH.'views/'.$temp)){$this->nsmarty->display('konstruksi.html');}
			else{$this->nsmarty->display($temp);}	
		}
	}	
	
	function get_grid($mod,$db_flag){
		$temp = 'backend/modul/grid_config.html';
		$filter=$this->combo_option($mod);
		$this->nsmarty->assign('data_select',$filter);
		$this->nsmarty->assign('mod',$mod);
		$this->nsmarty->assign('db_flag',$db_flag);
		if(!file_exists($this->config->item('appl').APPPATH.'views/'.$temp)){$this->nsmarty->display('konstruksi.html');}
		else{$this->nsmarty->display($temp);}
	}
	
	
	
	function get_report($mod){
		$temp="backend/report/".$mod.".html";
		$this->nsmarty->assign('mod',$mod);
		switch($mod){	
			case "inv_buku":
			case "inv_detil_buku":
			case "inv_media":
			case "inv_detil_media":
				$temp="backend/modul/report/report_main.html";
			break;
			case "report_inv_buku":
			case "report_inv_detil_buku":
			case "report_inv_media":
			case "report_inv_detil_media":
				$data=$this->mbackend->getdata('report','result_array',$mod);
				//print_r($data);
				$this->nsmarty->assign('data',$data);
				$kat=$this->input->post('type_trans');
				$this->nsmarty->assign('kat',$kat);
			break;
			
		}
		$this->nsmarty->assign('temp',$temp);
		if(!file_exists($this->config->item('appl').APPPATH.'views/'.$temp)){$this->nsmarty->display('konstruksi.html');}
		else{$this->nsmarty->display($temp);}
	}
	function get_konten($p1=""){
		if($p1!="")$mod=$p1;
		else $mod=$this->input->post('mod');
		if($this->input->post('table'))$mod=$this->input->post('table');
		//echo $mod;
		$this->nsmarty->assign('mod',$mod);
		$temp="backend/modul/".$mod.".html";
		switch($mod){
			case "trans_buku_sekolah":
			case "trans_buku_umum":
				//if($mod=='gudang_konfirmasi'){}
				$cetak=$this->input->post('flag_cetak');
				$temp="backend/modul/invoice.html";
				$data=$this->mbackend->getdata('get_pemesanan_buku','result_array');
				//echo "<pre>";print_r($data);
				$this->nsmarty->assign('data',$data);
			break;
			case "trans_media_sekolah":
			case "trans_media_umum":
				//if($mod=='gudang_konfirmasi'){}
				$cetak=$this->input->post('flag_cetak');
				$temp="backend/modul/invoice.html";
				$data=$this->mbackend->getdata('get_pemesanan_media','result_array');
				//echo "<pre>";print_r($data);
				$this->nsmarty->assign('data',$data);
			break;
		}
		$this->nsmarty->assign('temp',$temp);
		if(!file_exists($this->config->item('appl').APPPATH.'views/'.$temp)){$this->nsmarty->display('konstruksi.html');}
		else{$this->nsmarty->display($temp);}
	}
	function get_form($mod){
		$temp='backend/form/'.$mod.".html";
		$sts=$this->input->post('editstatus');
		$this->nsmarty->assign('sts',$sts);
		
		if($sts=='edit'){
			$data=$this->mbackend->getdata($mod,'get');
			$this->nsmarty->assign('data',$data);
		}
		
		switch($mod){
			case "sales":
				$temp='backend/modul/setting/sales_form.html';
				$this->nsmarty->assign('combo_prov', $this->lib->fillcombo('cl_provinsi', 'return', ($sts == 'edit' ? $data["cl_provinsi_id"] : "") ));
				$this->nsmarty->assign('combo_kab', $this->lib->fillcombo('cl_kab_kota', 'return', ($sts == 'edit' ? $data["cl_kab_kota_id"] : ""), ($sts == 'edit' ? $data["cl_provinsi_id"] : "") ));
				$this->nsmarty->assign('combo_kec', $this->lib->fillcombo('cl_kecamatan', 'return', ($sts == 'edit' ? $data["cl_kecamatan_id"] : ""), ($sts == 'edit' ? $data["cl_kab_kota_id"] : "") ));
			break;
		}
		$this->nsmarty->assign('mod',$mod);
		$this->nsmarty->assign('temp',$temp);
		
		if(!file_exists($this->config->item('appl').APPPATH.'views/'.$temp)){$this->nsmarty->display('konstruksi.html');}
		else{$this->nsmarty->display($temp);}
		
	}
	
	function getcombo($type=""){
		echo $this->lib->fillcombo($type, 'return');
	}	
	
	function getdata($p1,$p2="",$p3=""){
		echo $this->mbackend->getdata($p1,'json',$p3);
	}
	
	function simpandata($p1="",$p2=""){
		if($this->input->post('mod'))$p1=$this->input->post('mod');
		$post = array();
        foreach($_POST as $k=>$v){
			if($this->input->post($k)!=""){
				$post[$k] = $this->db->escape_str($this->input->post($k));
				//$post[$k] = $this->input->post($k);
			}else{
				$post[$k] = null;
			}
			
		}
		if(isset($post['editstatus'])){$editstatus = $post['editstatus'];unset($post['editstatus']);}
		else $editstatus = $p2;
		
		echo $this->mbackend->simpandata($p1, $post, $editstatus);
	}
	
	function test(){
		$a=array
		(
			'id'=> 6,
			'services_name' => 'Basic Housekeeping Service',
			'code'=> 'A.1',
			'desc_services_eng' => 'Basic Housekeeping Service is when host is providing the cleaning tools as mentioned in Terms & Conditions'
		);
		$b=array
		(
			'id_price' => 1,
			'tbl_services_id' => 6,
			'of_unit' => 1,
			'of_area_item' => 1,
			'percen' => '',
			'rate' => 8000,
			'type' => 'per m2',
			'remark' => '1x time payment'
		);
		print_r(array_merge($a,$b));
		
	}
	function combo_option($mod){
		$opt="";
		switch($mod){
			case "registration":
				$opt .="<option value='A.email'>Email</option>";
				$opt .="<option value='A.owner_name_last'>Last Name</option>";
				$opt .="<option value='A.owner_name_first'>First Name</option>";
				$opt .="<option value='A.id_number'>ID Number</option>";
				$opt .="<option value='A.company_name'>Company Name</option>";
			break;
			case "registration":
				$opt .="<option value='A.email_address'>Email</option>";
				$opt .="<option value='B.owner_name_last'>Last Name</option>";
				$opt .="<option value='B.owner_name_first'>First Name</option>";
				$opt .="<option value='B.id_number'>ID Number</option>";
				$opt .="<option value='B.company_name'>Company Name</option>";
			break;
			case "property":
				$opt .="<option value='C.owner_name_first'>First Name</option>";
				$opt .="<option value='C.owner_name_last'>Last Name</option>";
				$opt .="<option value='A.apartment_name'>Apartment Name</option>";
			break;
			case "housekeeping":
			case "check":
			case "hosting":
			case "linen":
			case "full_host":
				$opt .="<option value='services_name'>Services Name</option>";
			break;
			case "invoice_package":
			case "invoice":
			case "planning":
			case "planning_package":
			case "reservation":
				$opt .="<option value='A.no_invoice'>No Invoice</option>";
				$opt .="<option value='B.method_payment'>Method Payment</option>";
				$opt .="<option value='D.owner_name_first'>First Name</option>";
				$opt .="<option value='D.owner_name_last'>Last Name</option>";
				$opt .="<option value='E.apartment_name'>Unit Name</option>";
			break;
			case "cl_facility_unit":
				$opt .="<option value='A.facility_name'>Facility Name</option>";
				$opt .="<option value='A.unit'>Unit</option>";
			break;
			case "cl_compulsary_periodic_payment":
				$opt .="<option value='A.compulsary_periodic_payment'>Comp. Per. Payment</option>";
				$opt .="<option value='A.description'>Desc</option>";
			break;
			case "cl_room_type":
				$opt .="<option value='A.room_type'>Room Type</option>";
				$opt .="<option value='A.description'>Desc</option>";
			break;
		}
		return $opt;
	}
	function set_flag($p1){
		$post = array();
        foreach($_POST as $k=>$v){
			if($this->input->post($k)!=""){
				$post[$k] = $this->input->post($k);
			}
			
		}
		echo $this->mbackend->set_flag($p1,$post);
	}
	function cetak(){
		$mod=$this->input->post('mod');
			switch($mod){
				case "inv_buku_SEKOLAH":
				case "inv_buku_UMUM":
				case "inv_detil_buku_UMUM":
				case "inv_detil_buku_SEKOLAH":
				case "inv_media_SEKOLAH":
				case "inv_media_UMUM":
				case "inv_detil_media_UMUM":
				case "inv_detil_media_SEKOLAH":
					$mod_na="report_".$this->input->post('mod_na');
					$judul=$this->input->post('judul');
					$data=$this->mbackend->getdata('report','result_array',$mod_na);
					//$data=$this->mbackend->getdata('get_lap_rekap','result_array');
					$file_name=$this->input->post('mod');
				break;
			}
		$this->hasil_output('pdf',$mod,$data,$file_name,$judul);
	}
	function hasil_output($p1,$mod,$data,$file_name,$judul_header,$nomor="",$param=""){
		switch($p1){
			case "pdf":
				$this->load->library('mlpdf');	
				//$data=$this->mhome->getdata('cetak_voucher');
				if($this->input->post('type_trans'))$this->nsmarty->assign('kat',$this->input->post('type_trans'));
				$pdf = $this->mlpdf->load();
				$this->nsmarty->assign('param', $param);
				$this->nsmarty->assign('judul_header', $judul_header);
				$this->nsmarty->assign('nomor', $nomor);
				$this->nsmarty->assign('data', $data);
				$this->nsmarty->assign('mod', $mod);
				
				
				$htmlcontent = $this->nsmarty->fetch("backend/template/temp_pdf.html");
				$htmlheader = $this->nsmarty->fetch("backend/template/header.html");
				
				//echo $htmlcontent;exit;
				
				$spdf = new mPDF('', 'A4', 0, '', 12.7, 12.7, 33, 20, 5, 2, 'P');
				$spdf->ignore_invalid_utf8 = true;
				// bukan sulap bukan sihir sim salabim jadi apa prok prok prok
				$spdf->allow_charset_conversion = true;     // which is already true by default
				$spdf->charset_in = 'iso-8859-1';  // set content encoding to iso
				$spdf->SetDisplayMode('fullpage');		
				$spdf->SetHTMLHeader($htmlheader);
				//$spdf->keep_table_proportions = true;
				$spdf->useSubstitutions=false;
				$spdf->simpleTables=true;
				
				$spdf->SetHTMLFooter('
					<div style="font-family:arial; font-size:8px; text-align:center; font-weight:bold;">
						<table width="100%" style="font-family:arial; font-size:8px;">
							<tr>
								<td width="30%" align="left">
									
								</td>
								<td width="40%" align="center">
									
								</td>
								<td width="30%" align="right">
									Hal. {PAGENO} dari {nbpg}
								</td>
							</tr>
						</table>
					</div>
				');				
				//$file_name = date('YmdHis');
				$spdf->SetProtection(array('print'));				
				$spdf->WriteHTML($htmlcontent); // write the HTML into the PDF
				//$spdf->Output('repositories/Dokumen_LS/LS_PDF/'.$filename.'.pdf', 'F'); // save to file because we can
				//$spdf->Output('repositories/Billing/'.$filename.'.pdf', 'F');
				$spdf->Output($file_name.'.pdf', 'I'); // view file	
			break;
		}
	}
	function get_chart(){
		$chart=array();
		$x=array();
		$y=array();
		$mod=$this->input->post('mod');
		switch($mod){
			case "penjualan_inde":
				$tgl_akhir=date('Y-m-d');
				$tgl_milai = date('Y-m-d', strtotime($tgl_akhir .' -7 day'));
				$period = new DatePeriod(
					 new DateTime($tgl_milai),
					 new DateInterval('P1D'),
					 new DateTime($tgl_akhir)
				);
				$data=$this->mbackend->getdata('d_penjualan_inde','result_array');
				$idx=0;
				$x['name']='Total ( * 1000 )';
				$x['data']=array();
				foreach($period as $time) {
					$y[] = $time->format("Y-m-d");
					$x['data'][$idx]=0;
					foreach($data as $v=>$z){
						if($time->format("Y-m-d")==$z['tgl'])$x['data'][$idx]=(float)($z['total']/1000);
					}
					$idx++;
				}
				$chart['x']=array($x);
				$chart['y']=$y;
				//echo "<pre>";
				//print_r($chart);exit;
			break;
			case "penjualan_paket":
				$tgl_akhir=date('Y-m-d');
				$tgl_milai = date('Y-m-d', strtotime($tgl_akhir .' -7 day'));
				$period = new DatePeriod(
					 new DateTime($tgl_milai),
					 new DateInterval('P1D'),
					 new DateTime($tgl_akhir)
				);
				$data=$this->mbackend->getdata('d_penjualan_paket','result_array');
				$idx=0;
				$x['name']='Total ( * 1000 )';
				$x['data']=array();
				foreach($period as $time) {
					$y[] = $time->format("Y-m-d");
					$x['data'][$idx]=0;
					foreach($data as $v=>$z){
						if($time->format("Y-m-d")==$z['tgl'])$x['data'][$idx]=(float)($z['total']/1000);
					}
					$idx++;
				}
				$chart['x']=array($x);
				$chart['y']=$y;
				//echo "<pre>";
				//print_r($chart);exit;
			break;
		}
		echo json_encode($chart);
	}
	
	function getdisplay($type=""){
		switch($type){
			case "user_profile":
				$dataprofil = $this->mbackend->getdata('userprofile', 'row_array');
				$this->nsmarty->assign("data", $dataprofil);
				$temp = 'backend/modul/setting/user_profile.html';
			break;
			case "ubah_password":
				$temp = 'backend/modul/setting/ubah_password.html';
			break;
		}
		
		if(!file_exists($this->config->item('appl').APPPATH.'views/'.$temp)){$this->nsmarty->display('konstruksi.html');}
		else{$this->nsmarty->display($temp);}
	}
	
	function generatepdf($type){
		$this->load->library('mlpdf');	
		switch($type){
			case "cetak_kartu":
				$email = $this->input->post('email');
				if(!$email){
					echo "tutup tab browser ini, dan generate kembali melalui tombol di web www.aldeaz.id";
					exit;
				}
				
				$data_profil = $this->mbackend->getdata('userprofile', 'row_array', $email);
				if($data_profil){
					$this->nsmarty->assign('data_profil', $data_profil);
				}
				
				$filename = "KARTUMARKETING-".$email;
				$htmlcontent = $this->nsmarty->fetch('backend/modul/setting/cetak_kartu.html');
				
				$pdf = $this->mlpdf->load();
				$spdf = new mPDF('', 'A5-L', 0, '', 5, 5, 5, 5, 0, 0);
				$spdf->ignore_invalid_utf8 = true;
				$spdf->allow_charset_conversion = true;     // which is already true by default
				$spdf->charset_in = 'iso-8859-2';  // set content encoding to iso
				$spdf->SetDisplayMode('fullpage');		
				$spdf->SetProtection(array('print'));				
				$spdf->WriteHTML($htmlcontent); // write the HTML into the PDF
				//$spdf->Output($general_path.$subgroup."/".$io_number."/"."PARTIAL-".$partial_no."/LOA/".$filename.'.pdf', 'F'); // save to file because we can
				$spdf->Output($filename.'.pdf', 'I'); // view file
			break;
		}
	}
	
	function testblay(){
		echo "<pre>";
		print_r($this->auth);
		exit;
	}
}
