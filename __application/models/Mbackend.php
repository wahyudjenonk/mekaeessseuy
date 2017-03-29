<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

class Mbackend extends CI_Model{
	function __construct(){
		parent::__construct();
		//$this->auth = unserialize(base64_decode($this->session->userdata('44mpp3R4')));
		$this->db_remote='';
	}
	
	function getdata($type="", $balikan="", $p1="", $p2="",$p3="",$p4=""){
		$where = " WHERE 1=1 ";
		if($this->input->post('key')){
				$where .=" AND ".$this->input->post('kat')." like '%".$this->db->escape_str($this->input->post('key'))."%'";
		}
		if($this->input->post('db_flag')){
			$this->get_koneksi($this->input->post('db_flag'));
		}
		switch($type){
			case "report":
				$tgl_mulai=$this->input->post('start_date');
				$tgl_akhir=$this->input->post('end_date');
				$kat=$this->input->post('type_trans');
				switch($p1){
					case "report_inv_buku":
						$sql="SELECT A.*,B.nama_sekolah,B.nama_kepala_sekolah as pic,B.npsn,B.nama_lengkap,
							B.alamat_pengiriman,B.no_telp_sekolah,B.no_hp_kepsek,B.email,E.kab_kota,
							C.provinsi,F.jml_buku,G.total_pembayaran
							FROM tbl_h_pemesanan A
							LEFT JOIN tbl_registrasi B ON A.tbl_registrasi_id=B.id
							LEFT JOIN cl_provinsi C ON B.cl_provinsi_kode=C.kode_prov
							LEFT JOIN cl_kab_kota E ON B.cl_kab_kota_kode=E.kode_kab_kota
							LEFT JOIN (
								SELECT A.tbl_h_pemesanan_id,SUM(A.qty)as jml_buku 
								from tbl_d_pemesanan A 
								LEFT JOIN tbl_h_pemesanan B ON A.tbl_h_pemesanan_id=B.id
								LEFT JOIN tbl_registrasi C ON B.tbl_registrasi_id=C.id
								WHERE B.kode_marketing='".$this->auth['member_user']."' AND C.jenis_pembeli='".$kat."' 
								AND B.create_date BETWEEN '".$tgl_mulai."' AND '".$tgl_akhir." 23:59:00'
								GROUP BY A.tbl_h_pemesanan_id
							)AS F ON F.tbl_h_pemesanan_id=A.id
							LEFT JOIN tbl_konfirmasi G ON G.tbl_h_pemesanan_id=A.id
							WHERE A.kode_marketing='".$this->auth['member_user']."' AND B.jenis_pembeli='".$kat."' 
							AND A.create_date BETWEEN '".$tgl_mulai."' AND '".$tgl_akhir." 23:59:00'";
							//echo  $sql;
					break;
					case "report_inv_detil_buku":
						$sql="SELECT A.tbl_h_pemesanan_id,SUM(A.qty)as jml_buku,
							CONCAT(C.nama_sekolah,' [',C.npsn,']')as sekolah,C.nama_lengkap,
							B.no_order,B.sub_total,B.pajak,B.grand_total,B.id as id_header  
							from tbl_d_pemesanan A 
							LEFT JOIN tbl_h_pemesanan B ON A.tbl_h_pemesanan_id=B.id 
							LEFT JOIN tbl_registrasi C ON B.tbl_registrasi_id=C.id 
							WHERE B.kode_marketing='".$this->auth['member_user']."' AND C.jenis_pembeli='".$kat."' 
							AND B.tgl_order BETWEEN '".$tgl_mulai."' AND '".$tgl_akhir." 23:59:00'
							GROUP BY A.tbl_h_pemesanan_id ";
							
					$data=array();
					$res=$this->db_remote->query($sql)->result_array();
					if(count($res)>0){
						foreach($res as $x=>$v){
							$data[$x]=array();
							$data[$x]['no_order']=$v['no_order'];
							$data[$x]['sekolah']=$v['sekolah'];
							$data[$x]['nama_lengkap']=$v['nama_lengkap'];
							$data[$x]['sub_total']=$v['sub_total'];
							$data[$x]['pajak']=$v['pajak'];
							$data[$x]['grand_total']=$v['grand_total'];
							$data[$x]['jml_buku']=$v['jml_buku'];
							$sql="SELECT A.*,B.no_order,CONCAT(D.nama_sekolah,' (',D.npsn,')')as sekolah,D.nama_lengkap,C.judul_buku
									FROM tbl_d_pemesanan A
									LEFT JOIN tbl_h_pemesanan B ON A.tbl_h_pemesanan_id=B.id
									LEFT JOIN tbl_buku C ON A.tbl_buku_id=C.id
									LEFT JOIN tbl_registrasi D ON B.tbl_registrasi_id=D.id
									WHERE B.kode_marketing='".$this->auth['member_user']."' 
									AND D.jenis_pembeli='".$kat."' AND A.tbl_h_pemesanan_id=".$v['id_header'];
							$det=$this->db_remote->query($sql)->result_array();
							//print_r($det);exit;
							if(count($det)>0){
								$data[$x]['detil']=$det;
							}
							
						}
					}
					//echo "<pre>";print_r($data);exit;
					$this->db_remote->close();
					return $data;
					break;
					case "report_inv_media":
						$sql="SELECT A.*,B.nama_sekolah,B.nama_kepala_sekolah as pic,B.npsn,B.nama_lengkap,
							B.alamat_pengiriman,B.no_telp_sekolah,B.no_hp_kepsek,B.email,E.kab_kota,
							C.provinsi,F.jml_buku,G.total_pembayaran
							FROM tbl_h_pemesanan A
							LEFT JOIN tbl_registrasi B ON A.tbl_registrasi_id=B.id
							LEFT JOIN cl_provinsi C ON B.cl_provinsi_kode=C.kode_prov
							LEFT JOIN cl_kab_kota E ON B.cl_kab_kota_kode=E.kode_kab_kota
							LEFT JOIN (
								SELECT A.tbl_h_pemesanan_id,SUM(A.qty)as jml_buku 
								from tbl_d_pemesanan A 
								LEFT JOIN tbl_h_pemesanan B ON A.tbl_h_pemesanan_id=B.id
								LEFT JOIN tbl_registrasi C ON B.tbl_registrasi_id=C.id
								WHERE B.kode_marketing='".$this->auth['member_user']."' 
								AND C.jenis_pembeli='".$kat."' 
								AND B.create_date BETWEEN '".$tgl_mulai."' AND '".$tgl_akhir." 23:59:00'
								GROUP BY A.tbl_h_pemesanan_id
							)AS F ON F.tbl_h_pemesanan_id=A.id
							LEFT JOIN tbl_konfirmasi G ON G.tbl_h_pemesanan_id=A.id
							WHERE A.kode_marketing='".$this->auth['member_user']."' 
							AND B.jenis_pembeli='".$kat."' 
							AND A.create_date BETWEEN '".$tgl_mulai."' AND '".$tgl_akhir." 23:59:00'";
							//echo  $sql;
					break;
					case "report_inv_detil_media":
						$sql="SELECT A.tbl_h_pemesanan_id,SUM(A.qty)as jml_buku,
							CONCAT(C.nama_sekolah,' [',C.npsn,']')as sekolah,C.nama_lengkap,
							B.no_order,B.sub_total,B.pajak,B.grand_total,B.id as id_header  
							from tbl_d_pemesanan A 
							LEFT JOIN tbl_h_pemesanan B ON A.tbl_h_pemesanan_id=B.id 
							LEFT JOIN tbl_registrasi C ON B.tbl_registrasi_id=C.id 
							WHERE B.kode_marketing='".$this->auth['member_user']."' 
							AND C.jenis_pembeli='".$kat."' 
							AND B.tgl_order BETWEEN '".$tgl_mulai."' AND '".$tgl_akhir." 23:59:00'
							GROUP BY A.tbl_h_pemesanan_id ";
							
					$data=array();
					$res=$this->db_remote->query($sql)->result_array();
					if(count($res)>0){
						foreach($res as $x=>$v){
							$data[$x]=array();
							$data[$x]['no_order']=$v['no_order'];
							$data[$x]['sekolah']=$v['sekolah'];
							$data[$x]['nama_lengkap']=$v['nama_lengkap'];
							$data[$x]['sub_total']=$v['sub_total'];
							$data[$x]['pajak']=$v['pajak'];
							$data[$x]['grand_total']=$v['grand_total'];
							$data[$x]['jml_buku']=$v['jml_buku'];
							$sql="SELECT A.*,B.no_order,CONCAT(D.nama_sekolah,' (',D.npsn,')')as sekolah,D.nama_lengkap,C.judul_produk as judul_buku
									FROM tbl_d_pemesanan A
									LEFT JOIN tbl_h_pemesanan B ON A.tbl_h_pemesanan_id=B.id
									LEFT JOIN tbl_produk C ON A.tbl_produk_id=C.id
									LEFT JOIN tbl_registrasi D ON B.tbl_registrasi_id=D.id
									WHERE B.kode_marketing='".$this->auth['member_user']."' 
									AND D.jenis_pembeli='".$kat."' AND A.tbl_h_pemesanan_id=".$v['id_header'];
							$det=$this->db_remote->query($sql)->result_array();
							//print_r($det);exit;
							if(count($det)>0){
								$data[$x]['detil']=$det;
							}
							
						}
					}
					//echo "<pre>";print_r($data);exit;
					$this->db_remote->close();
					return $data;
					break;
				}
				
				
				
			break;
			case "dashboard":
				$data=array();
				$sql_na="SELECT A.no_order,A.grand_total 
						FROM tbl_h_pemesanan A
						LEFT JOIN tbl_registrasi B ON A.tbl_registrasi_id=B.id ";
				$order="ORDER BY A.tgl_order DESC
						LIMIT 0,5 ";
				$this->get_koneksi('B');
				$where =" WHERE A.kode_marketing='".$this->auth['member_user']."' AND B.jenis_pembeli='SEKOLAH' ";
				$sql=$sql_na.$where.$order;
				$data['trans_buku_sekolah']=$this->db_remote->query($sql)->result_array();
				$where =" WHERE A.kode_marketing='".$this->auth['member_user']."' AND B.jenis_pembeli='UMUM' ";
				$sql=$sql_na.$where.$order;
				$data['trans_buku_umum']=$this->db_remote->query($sql)->result_array();
				$this->db_remote->close();
				$this->get_koneksi('M');
				$where =" WHERE A.kode_marketing='".$this->auth['member_user']."' AND B.jenis_pembeli='SEKOLAH' ";
				$sql=$sql_na.$where.$order;
				$data['trans_media_sekolah']=$this->db_remote->query($sql)->result_array();
				$where =" WHERE A.kode_marketing='".$this->auth['member_user']."' AND B.jenis_pembeli='UMUM' ";
				$sql=$sql_na.$where.$order;
				$data['trans_media_umum']=$this->db_remote->query($sql)->result_array();
				$this->db_remote->close();
				return $data;
			break;
			case "monitoring_buku":
			case "monitoring_media":
				$sql="SELECT A.no_order,A.`status` as status_order,B.flag as status_konfirmasi,
						C.flag as status_gudang,D.`status` as status_kirim,D.no_resi
						FROM tbl_h_pemesanan A
						LEFT JOIN (
							SELECT A.tbl_h_pemesanan_id,A.flag,A.id FROM tbl_konfirmasi A
						)AS B ON B.tbl_h_pemesanan_id=A.id
						LEFT JOIN (
							SELECT A.tbl_h_pemesanan_id,A.tbl_konfirmasi_id,A.flag 
							FROM tbl_gudang A
						)AS C ON (C.tbl_h_pemesanan_id=A.id AND C.tbl_konfirmasi_id=B.id)
						LEFT JOIN (
							SELECT A.tbl_h_pemesanan_id,A.`status`,A.no_resi 
							FROM tbl_tracking_pengiriman A
						)AS D ON D.tbl_h_pemesanan_id=A.id ".$where." AND A.kode_marketing='".$this->auth['member_user']."'";
			break;
			case "get_pemesanan_buku":
				$data=array();
				$id=$this->input->post('id');
				if($id)$where .=" AND A.id=".$id;
				$sql="SELECT A.*,B.nama_sekolah,B.nama_lengkap,B.jenis_pembeli 
					  FROM tbl_h_pemesanan A 
					  LEFT JOIN tbl_registrasi B ON A.tbl_registrasi_id=B.id ".$where." AND A.kode_marketing='".$this->auth['member_user']."'";
				$data['header']=$this->db_remote->query($sql)->row_array();
				$sql="SELECT A.*,B.judul_buku,(A.qty*A.harga)as total
					  FROM tbl_d_pemesanan A 
					  LEFT JOin tbl_buku B ON A.tbl_buku_id=B.id
					  WHERE A.tbl_h_pemesanan_id=".$id;
				$data['detil']=$this->db_remote->query($sql)->result_array();
				return $data;
			break;
			case "get_pemesanan_media":
				$data=array();
				$id=$this->input->post('id');
				if($id)$where .=" AND A.id=".$id;
				$sql="SELECT A.*,B.nama_sekolah,B.nama_lengkap,B.jenis_pembeli 
					  FROM tbl_h_pemesanan A 
					  LEFT JOIN tbl_registrasi B ON A.tbl_registrasi_id=B.id ".$where." AND A.kode_marketing='".$this->auth['member_user']."'";
				$data['header']=$this->db_remote->query($sql)->row_array();
				$sql="SELECT A.*,B.judul_produk,(A.qty*A.harga)as total
					  FROM tbl_d_pemesanan A 
					  LEFT JOin tbl_produk B ON A.tbl_produk_id=B.id
					  WHERE A.tbl_h_pemesanan_id=".$id;
				$data['detil']=$this->db_remote->query($sql)->result_array();
				return $data;
			break;
			case "data_login":
				$sql = "
					SELECT A.member_user,A.email_address,A.flag AS status,A.pwd,B.*,
					B.nama_lengkap
					FROM tbl_member A
					LEFT JOIN tbl_registration B ON A.tbl_registration_id=B.id
					WHERE A.member_user = '".$p1."' OR A.email_address='".$p1."'
				";
			break;
			case "trans_buku_sekolah":
			case "trans_buku_umum":			
			case "trans_media_sekolah":			
			case "trans_media_umum":			
				if($type=='trans_buku_sekolah' || $type=='trans_media_sekolah')$where .=" AND B.jenis_pembeli='SEKOLAH'";
				if($type=='trans_buku_umum' || $type=='trans_media_umum')$where .=" AND B.jenis_pembeli='UMUM'";
				
				$sql="SELECT A.*,B.nama_sekolah,B.nama_lengkap 
					  FROM tbl_h_pemesanan A 
					  LEFT JOIN tbl_registrasi B ON A.tbl_registrasi_id=B.id 
					  ".$where." AND A.kode_marketing='".$this->auth['member_user']."' 
					  ORDER BY A.tgl_order DESC";
				//echo $sql;
			break;
			
			case "userprofile":
				$sql = "
					SELECT A.*, B.member_user, C.provinsi, D.kab_kota, E.kecamatan
					FROM tbl_registration A
					LEFT JOIN tbl_member B ON B.tbl_registration_id = A.id
					LEFT JOIN cl_provinsi C ON C.kode_prov = A.cl_provinsi_id
					LEFT JOIN cl_kab_kota D ON D.kode_kab_kota = A.cl_kab_kota_id
					LEFT JOIN cl_kecamatan E ON E.kode_kecamatan = A.cl_kecamatan_id
					WHERE A.email_address = '".$this->auth['email_address']."'
				";
			break;
			
			default:
				if($balikan=='get'){$where .=" AND A.id=".$this->input->post('id');}
				$sql="SELECT A.* FROM ".$type." A ".$where;
				if($balikan=='get')return $this->db->query($sql)->row_array();
			break;
		}
		
		if($balikan == 'json'){
			if($this->input->post('db_flag')){
				return $this->get_json_grid($sql,$this->input->post('db_flag'));
			}else{
				return $this->get_json_grid($sql,'lokal');
			}
			
		}elseif($balikan == 'row_array'){
			if($this->input->post('db_flag')){
				$data=$this->db_remote->query($sql)->row_array();
				$this->db_remote->close();
				return $data;
			}
			else{
				return $this->db->query($sql)->row_array();
			}
		}elseif($balikan == 'result'){
			if($this->input->post('db_flag')){
				$data=$this->db_remote->query($sql)->result();
				$this->db_remote->close();
				return $data;
			}else{
				return $this->db->query($sql)->result();
			}
		}elseif($balikan == 'result_array'){
			if($this->input->post('db_flag')){
				$data=$this->db_remote->query($sql)->result_array();
				$this->db_remote->close();
				return $data;
			}else{
				return $this->db->query($sql)->result_array();
			}
		}
		
	}
	function get_koneksi($flag){
		if($flag=='B')$this->db_remote=$this->load->database('buku',true);
		if($flag=='M')$this->db_remote=$this->load->database('media',true);
		$connected = $this->db_remote->initialize();
		if (!$connected) {
		   echo 'Koneksi Salah';exit;
		}
	}
	function get_json_grid($sql,$koneksi){
		$page = (integer) (($this->input->post('page')) ? $this->input->post('page') : "1");
		$limit = (integer) (($this->input->post('rows')) ? $this->input->post('rows') : "10");
		if($koneksi!='lokal'){
			$count = $this->db_remote->query($sql)->num_rows();
		}
		else{
			$count = $this->db->query($sql)->num_rows();
		}
		
		if( $count >0 ) { $total_pages = ceil($count/$limit); } else { $total_pages = 0; } 
		if ($page > $total_pages) $page=$total_pages; 
		$start = $limit*$page - $limit; // do not put $limit*($page - 1)
		if($start<0) $start=0;
		 		
		$sql = $sql . " LIMIT $start,$limit";
		if($koneksi!='lokal'){
			$data = $this->db_remote->query($sql)->result_array();
		}
		else{			
			$data = $this->db->query($sql)->result_array();  
		}
				
		if($data){
		   $responce = new stdClass();
		   $responce->rows= $data;
		   $responce->total =$count;
		   return json_encode($responce);
		}else{ 
		   $responce = new stdClass();
		   $responce->rows = 0;
		   $responce->total = 0;
		   return json_encode($responce);
		} 
	}
	function get_combo($type="", $p1="", $p2=""){
		switch($type){
			case "cl_provinsi":
				$sql = "
					SELECT kode_prov as id, provinsi as txt
					FROM cl_provinsi
				";
			break;
			case "cl_kab_kota":
				$provinsi = $this->input->post('v2');
				$sql = "
					SELECT kode_kab_kota as id, kab_kota as txt
					FROM cl_kab_kota
					WHERE cl_provinsi_kode = '".$provinsi."'
				";
			break;
			case "cl_kecamatan":
				$kab_kota = $this->input->post('v2');
				$sql = "
					SELECT kode_kecamatan as id, kecamatan as txt
					FROM cl_kecamatan
					WHERE cl_kab_kota_kode = '".$kab_kota."'
				";
			break;
		}
		
		return $this->db->query($sql)->result_array();
	}
	
	function simpandata($table,$data,$sts_crud){ //$sts_crud --> STATUS NYEE INSERT, UPDATE, DELETE
		$this->db->trans_begin();
		if(isset($data['id'])){
			$id = $data['id'];
			unset($data['id']);
		}
		
		switch($table){
			case "update_profile":
				$table = "tbl_registration";
				$arrayparam = array(
					"email_address" => $this->auth['email_address']
				);
				$data['nama_lengkap'] = $data['nlengkap'];
				$data['kode_pos'] = $data['kdpos'];
				$data['alamat'] = $data['alamatpalsu'];
				$data['no_ktp'] = $data['ktp'];
				$data['no_handphone'] = $data['hp'];
				
				unset($data['nlengkap']);
				unset($data['kdpos']);
				unset($data['alamatpalsu']);
				unset($data['ktp']);
				unset($data['hp']);
			break;
			case "ubah_password":
				$this->load->library('encrypt');
				$password_lama = $this->encrypt->decode($this->auth['pwd']);
				if($data['oldpass'] != $password_lama){
					echo 2;
					exit;
				}
				
				$table = "tbl_member";
				$arrayparam = array(
					"email_address" => $this->auth['email_address']
				);
				$data['pwd'] = $this->encrypt->encode($data['newpass']);
				
				unset($data['newpass']);
				unset($data['oldpass']);
			break;
		}
		
		switch ($sts_crud){
			case "add":
				$this->db->insert($table,$data);
			break;
			case "edit":
				$this->db->update($table, $data, $arrayparam);
			break;
			case "delete":
				$this->db->delete($table, $arrayparam);
			break;
		}
		
		if($this->db->trans_status() == false){
			$this->db->trans_rollback();
			return 'gagal';
		}else{
			 return $this->db->trans_commit();
		}
	}
	function set_flag($p1,$data){
		$this->db->trans_begin();
		$id=$data['id'];
		unset($data['id']);
		switch($p1){
			case "confirmation_pay":
				$table="tbl_payment_confirm";
				$sql="SELECT B.id as id_invoice
						FROM tbl_payment_confirm A
						LEFT JOIN tbl_header_transaction B ON A.tbl_transaction_id=B.id
						WHERE A.flag_transaction='I' AND A.id='".$id."'";
				$id_inv=$this->db->query($sql)->row_array();
				if(isset($id_inv['id_invoice'])){
					if($data['flag']=='C')$flag_inv='CP';
					else {
						$flag_inv='F';
						$data['confirm_kode']=$this->lib->uniq_id();
						$data['date_confirm']=date('Y-m-d H:i:s');
					}
					$sql="UPDATE tbl_header_transaction SET flag='".$flag_inv."' WHERE id=".$id_inv['id_invoice'];
					$this->db->query($sql);
				}
			break;
			case "confirmation_pay_pack":
				$table="tbl_payment_confirm";
				$sql="SELECT B.id as id_invoice
						FROM tbl_payment_confirm A
						LEFT JOIN tbl_transaction_package B ON A.tbl_transaction_id=B.id
						WHERE A.flag_transaction='P' AND A.id='".$id."'";
				$id_inv=$this->db->query($sql)->row_array();
				if(isset($id_inv['id_invoice'])){
					if($data['flag']=='C')$flag_inv='CP';
					else {
						$flag_inv='F';
						$data['confirm_kode']=$this->lib->uniq_id();
						$data['date_confirm']=date('Y-m-d H:i:s');
					}
					$sql="UPDATE tbl_transaction_package SET flag='".$flag_inv."' WHERE id=".$id_inv['id_invoice'];
					$this->db->query($sql);
				}
			break;
		}
		$this->db->update($table,$data,array('id'=>$id));
		
		if($this->db->trans_status() == false){
			$this->db->trans_rollback();
			return 'gagal';
		}else{
			 return $this->db->trans_commit();
		}
	}
	
}
