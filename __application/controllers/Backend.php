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
					$this->nsmarty->assign('data',$data);
				break;
				case "transaksi":
					if($p2 == "invoice"){
						$data = $this->mbackend->getdata('detail_invoice', 'variable');
						if($data){
							$id = $this->input->post('id');
							$datatracking = $this->mbackend->getdata('tracking_pesanan', 'row_array', $id);							
							$this->nsmarty->assign("datatracking", $datatracking);
						}
						$this->nsmarty->assign("data", $data);
					}elseif($p2 == "konfirmasi"){
						$no_order = $this->input->post("ord");
						//$dbbook = $this->load->database('buku',true);
						$cek_no_order = $this->db->get_where("tbl_h_pemesanan", array("no_order"=>$no_order) )->row_array();
						if($cek_no_order){ 
							$cek_konf = $this->mbackend->getdata('tbl_cek_konfirmasi', 'row_array', $cek_no_order['id'] );
							if($cek_konf){
								$this->nsmarty->assign( 'cek_order', "true"); 
								$this->nsmarty->assign( 'cek_konf', "false"); 
								$this->nsmarty->assign( 'datakonfirmasi', $cek_konf ); 
							}else{
								$this->nsmarty->assign( 'cek_order', "true" ); 
								$this->nsmarty->assign( 'cek_konf', "true");
							}
							
							$cek_no_order["grand_total"] = number_format($cek_no_order['grand_total'],0,",",".");
							$this->nsmarty->assign( 'data_order', $cek_no_order ); 
						}else{ 
							$this->nsmarty->assign( 'cek_order', "false" ); 
						}
						
						$this->nsmarty->assign( 'no_order', $no_order ); 
					}elseif($p2 == "upload"){
						$no_order = $this->input->post("ord");
						//$dbbook = $this->load->database('buku',true);
						$cek_no_order = $this->db->get_where("tbl_h_pemesanan", array("no_order"=>$no_order) )->row_array();
						if($cek_no_order){ 
							$cek_upl = $this->mbackend->getdata('tbl_cek_uploadfile', 'row_array', $cek_no_order['id'] );
							if($cek_upl){
								$this->nsmarty->assign( 'cek_upl', "false"); 
								$this->nsmarty->assign( 'cek_order', "true" ); 
								$this->nsmarty->assign( 'dataupload', $cek_upl ); 
							}else{
								$this->nsmarty->assign( 'cek_upl', "true"); 								
								$this->nsmarty->assign( 'cek_order', "true" ); 
							}
							
							$this->nsmarty->assign( 'data_order', $cek_no_order ); 
							
						}else{ 
							$this->nsmarty->assign( 'cek_order', "false" ); 
						}	
						
						$this->nsmarty->assign( 'no_order', $no_order ); 
					}
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
	
	function generatepdf($type, $p1="", $p2="", $p3=""){
		$this->load->library('mlpdf');	
		switch($type){
			case "bastnya":
				$this->load->helper('terbilang');
				$inv = $p1;
				if(!$inv){
					echo "<center><h1>No. Invoice Tidak Valid</h1></center>";
					exit;
				}
				$data_invoice = $this->mbackend->getdata('header_pesanan', 'row_array', $inv);
				if($data_invoice){
					$no_bast = $data_invoice['no_order']."/OLS-MKS/BAST/XII/".date('Y');
					$datacust = $this->mbackend->getdata('datacustomer', 'row_array', $data_invoice['tbl_registrasi_id'], '', 'cetak_bast');
					$datadetailpesanan = $this->mbackend->getdata('detail_pesanan', 'result_array', $data_invoice['idpesan']);
					$totqty = 0;
					$tottotal = 0;
					foreach($datadetailpesanan as $k => $v){
						$totqty += $v['qty'];
						$tottotal += $v['subtotal'];
						
						$datadetailpesanan[$k]['harga'] = number_format($v['harga'],0,",",".");
						$datadetailpesanan[$k]['subtotal'] = number_format($v['subtotal'],0,",",".");
						$datadetailpesanan[$k]['nama_group'] = strtoupper(substr($v['nama_group'], 0,1));
					}
					
					//$dbbook = $this->load->database('buku',true);
					$cekdatabast = $this->db->get_where('tbl_bast', array('tbl_h_pemesanan_id'=>$data_invoice['idpesan']) )->row_array();
					if(!$cekdatabast){
						$array_insert_bast = array(
							'tbl_h_pemesanan_id' => $data_invoice['idpesan'],
							'no_bast' => $no_bast,
							'create_date' => date('Y-m-d H:i:s')
						);
						$this->db->insert('tbl_bast', $array_insert_bast);
						
						$tgl = $this->lib->konversi_tgl(date('Y-m-d'));
						$time = $this->lib->konversi_jam(date('H:i:s'));
					}else{
						$tgl_create_1 = explode(" ",$cekdatabast["create_date"]);
						
						$tgl = $this->lib->konversi_tgl($tgl_create_1[0]);
						$time = $this->lib->konversi_jam($tgl_create_1[1]);
					}
										
					$this->nsmarty->assign('datainvoice', $data_invoice);
					$this->nsmarty->assign('datakonfirmasi', $datakonfirmasi);
					$this->nsmarty->assign('datacust', $datacust);
					$this->nsmarty->assign('datadetailpesanan', $datadetailpesanan);
					$this->nsmarty->assign('totqty', $totqty);
					$this->nsmarty->assign('tgl', $tgl);
					$this->nsmarty->assign('time', $time);
					$this->nsmarty->assign('no_bast', $no_bast);
					$this->nsmarty->assign('tottotal', number_format($tottotal,0,",","."));
				}else{
					echo "<center><h1>No. Invoice Tidak Valid</h1></center>";
					exit;
				}
				
				$filename = str_replace('/', '_', $no_bast);
				$htmlcontent = $this->nsmarty->fetch('backend/modul/transaksi/pdf_bast.html');
				
				$pdf = $this->mlpdf->load();
				$spdf = new mPDF('', 'A4', 0, '', 12.7, 12.7, 10, 10, 5, 2, 'P');
				$spdf->ignore_invalid_utf8 = true;
				$spdf->allow_charset_conversion = true;     // which is already true by default
				$spdf->charset_in = 'iso-8859-2';  // set content encoding to iso
				$spdf->SetDisplayMode('fullpage');		
				$spdf->SetProtection(array('print'));				
				$spdf->WriteHTML($htmlcontent); // write the HTML into the PDF
				//$spdf->Output($general_path.$subgroup."/".$io_number."/"."PARTIAL-".$partial_no."/LOA/".$filename.'.pdf', 'F'); // save to file because we can
				$spdf->Output($filename.'.pdf', 'I'); // view file
			break;
			case "kwitansinya":
				$this->load->helper('terbilang');
				$inv = $p1;
				if(!$inv){
					echo "<center><h1>No. Invoice Tidak Valid</h1></center>";
					exit;
				}				
				$data_invoice = $this->mbackend->getdata('header_pesanan', 'row_array', $inv);
				if($data_invoice){
					$no_kwitansi = $data_invoice['no_order']."/OLS-MKS/K/".date('Y');
					$datacust = $this->mbackend->getdata('datacustomer', 'row_array', $data_invoice['tbl_registrasi_id'], '', 'cetak_bast');
					$jumlah = number_to_words($data_invoice['grand_total']);
					
					//$dbbook = $this->load->database('buku',true);
					$cekdatakwitansi = $this->db->get_where('tbl_kwitansi', array('tbl_h_pemesanan_id'=>$data_invoice['idpesan']) )->row_array();
					if(!$cekdatakwitansi){
						$array_insert_kwitansi = array(
							'tbl_h_pemesanan_id' => $data_invoice['idpesan'],
							'no_kwitansi' => $no_kwitansi,
							'create_date' => date('Y-m-d H:i:s')
						);
						$this->db->insert('tbl_kwitansi', $array_insert_kwitansi);
						$tglgenerate = date('Y-m-d H:i:s');
					}else{
						$tglgenerate = $cekdatakwitansi["create_date"];
					}
					
					$this->nsmarty->assign('datainvoice', $data_invoice);
					$this->nsmarty->assign('datacust', $datacust);
					$this->nsmarty->assign('jumlah', $jumlah);
					$this->nsmarty->assign('tglgenerate', $tglgenerate);
					$this->nsmarty->assign('no_kwitansi', $no_kwitansi);
					$this->nsmarty->assign('grandtotal', number_format($data_invoice['grand_total'],0,",",".") );
				}else{
					echo "<center><h1>No. Invoice Tidak Valid</h1></center>";
					exit;
				}
				
				$filename = str_replace('/', '_', $no_kwitansi);
				$htmlcontent = $this->nsmarty->fetch('backend/modul/transaksi/pdf_kwitansi.html');
								
				$pdf = $this->mlpdf->load();
				$spdf = new mPDF('', 'A4', 0, '', 12.7, 12.7, 15, 15, 5, 2, 'L');
				//$spdf = new mPDF('', 'A5-L', 0, '', 5, 5, 5, 5, 0, 0);
				$spdf->ignore_invalid_utf8 = true;
				$spdf->allow_charset_conversion = true;     // which is already true by default
				$spdf->charset_in = 'iso-8859-2';  // set content encoding to iso
				$spdf->SetDisplayMode('fullpage');		
				$spdf->SetProtection(array('print'));				
				$spdf->WriteHTML($htmlcontent); // write the HTML into the PDF
				//$spdf->Output($general_path.$subgroup."/".$io_number."/"."PARTIAL-".$partial_no."/LOA/".$filename.'.pdf', 'F'); // save to file because we can
				$spdf->Output($filename.'.pdf', 'I'); // view file
			break;
			case "tandaterimanya":
				//$no_tanda_terima = $data_invoice['no_order']."/ASP/TT/".date('Y');
				$inv = $p1;
				if(!$inv){
					echo "<center><h1>No. Invoice Tidak Valid</h1></center>";
					exit;
				}	
				
				$data_invoice = $this->mbackend->getdata('header_pesanan', 'row_array', $inv);
				if($data_invoice){
					$datadetailpesanan = $this->mbackend->getdata('detail_pesanan', 'result_array', $data_invoice['idpesan']);
					foreach($datadetailpesanan as $k=>$v){
						$datadetailpesanan[$k]['harga'] = number_format($v['harga'],0,",",".");
						$datadetailpesanan[$k]['subtotal'] = number_format($v['subtotal'],0,",",".");
					}
					$this->nsmarty->assign('datadetailpesanan', $datadetailpesanan);
					$this->nsmarty->assign('data_invoice', $data_invoice);
					$this->nsmarty->assign('inv', $inv);
				}else{
					echo "<center><h1>No. Invoice Tidak Valid</h1></center>";
					exit;
				}
				
				$filename = str_replace('/', '_', $inv);
				$htmlcontent = $this->nsmarty->fetch('backend/modul/transaksi/pdf_tanda_terima.html');
				
				$pdf = $this->mlpdf->load();
				//$spdf = new mPDF('', 'A5', 0, '', 12.7, 12.7, 15, 20, 5, 2, 'L');
				$spdf = new mPDF('', 'A4', 0, '', 12.7, 12.7, 15, 20, 5, 2, 'P');				
				$spdf->ignore_invalid_utf8 = true;
				$spdf->allow_charset_conversion = true;     // which is already true by default
				$spdf->charset_in = 'iso-8859-2';  // set content encoding to iso
				$spdf->SetDisplayMode('fullpage');		
				$spdf->SetProtection(array('print'));				
				$spdf->WriteHTML($htmlcontent); // write the HTML into the PDF
				//$spdf->Output($general_path.$subgroup."/".$io_number."/"."PARTIAL-".$partial_no."/LOA/".$filename.'.pdf', 'F'); // save to file because we can
				$spdf->Output($filename.'.pdf', 'I'); // view file
			break;
			case "suratpesanannya":
				$inv = $p1;
				if(!$inv){
					echo "<center><h1>No. Invoice Tidak Valid</h1></center>";
					exit;
				}				
				$data_invoice = $this->mbackend->getdata('header_pesanan', 'row_array', $inv);
				if($data_invoice){
					$datadetailpesanan = $this->mbackend->getdata('detail_pesanan', 'result_array', $data_invoice['idpesan']);
					foreach($datadetailpesanan as $k=>$v){
						$datadetailpesanan[$k]['harga'] = number_format($v['harga'],0,",",".");
						$datadetailpesanan[$k]['subtotal'] = number_format($v['subtotal'],0,",",".");
					}
					$this->nsmarty->assign('datadetailpesanan', $datadetailpesanan);
					$this->nsmarty->assign('data_invoice', $data_invoice);
					$this->nsmarty->assign('inv', $inv);
				}else{
					echo "<center><h1>No. Invoice Tidak Valid</h1></center>";
					exit;
				}
				
				$filename = str_replace('/', '_', $inv);
				$htmlcontent = $this->nsmarty->fetch('backend/modul/transaksi/pdf_surat_pesanan.html');
				
				$pdf = $this->mlpdf->load();
				//$spdf = new mPDF('', 'A5', 0, '', 12.7, 12.7, 15, 20, 5, 2, 'L');
				$spdf = new mPDF('', 'A4', 0, '', 12.7, 12.7, 15, 20, 5, 2, 'P');				
				$spdf->ignore_invalid_utf8 = true;
				$spdf->allow_charset_conversion = true;     // which is already true by default
				$spdf->charset_in = 'iso-8859-2';  // set content encoding to iso
				$spdf->SetDisplayMode('fullpage');		
				$spdf->SetProtection(array('print'));				
				$spdf->WriteHTML($htmlcontent); // write the HTML into the PDF
				//$spdf->Output($general_path.$subgroup."/".$io_number."/"."PARTIAL-".$partial_no."/LOA/".$filename.'.pdf', 'F'); // save to file because we can
				$spdf->Output($filename.'.pdf', 'I'); // view file
			break;
			case "cetak_kartu":
				$email = $this->input->post('email');
				if(!$email){
					echo "tutup tab browser ini, dan generate kembali melalui tombol di web mks-store.id";
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
