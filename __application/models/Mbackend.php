<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

class Mbackend extends CI_Model{
	function __construct(){
		parent::__construct();
		$this->auth = unserialize(base64_decode($this->session->userdata('mksmarketing')));
		$this->db_remote='';
	}
	
	function getdata($type="", $balikan="", $p1="", $p2="",$p3="",$p4=""){
		$where = " WHERE 1=1 ";
		$flag = $this->input->post('db_flag');
		if($this->input->post('key')){
				$where .=" AND ".$this->input->post('kat')." like '%".$this->db->escape_str($this->input->post('key'))."%'";
		}
		
		if($flag){
			$this->get_koneksi($this->input->post('db_flag'));
		}
		if($balikan=='get'){$where .=" AND A.id=".$this->input->post('id');}
		
		switch($type){
			case "getemail_cust":
				$flag = "B";
				$this->get_koneksi("B");
				$sql = "
					SELECT B.email
					FROM tbl_h_pemesanan A 
					LEFT JOIN tbl_registrasi B ON B.id = A.tbl_registrasi_id
					WHERE A.no_order = '".$p1."'
				";
			break;
			case "tbl_cek_konfirmasi":
				$flag = "B";
				$this->get_koneksi("B");
				$sql = "
					SELECT A.*,
						DATE_FORMAT(A.create_date,'%d %b %Y %h:%i %p') as tanggal_konfirmasi,
						DATE_FORMAT(A.tanggal_transfer,'%d %b %Y') as tgl_transfer
					FROM tbl_konfirmasi A
					WHERE A.tbl_h_pemesanan_id = '".$p1."'
				";
			break;
			case "tbl_cek_uploadfile":
				$flag = "B";
				$this->get_koneksi("B");
				$sql = "
					SELECT A.*,
						DATE_FORMAT(A.create_date,'%d %b %Y %h:%i %p') as tgl_upload
					FROM tbl_uploadfile A
					WHERE A.tbl_h_pemesanan_id = '".$p1."'
				";
			break;
			case "tracking_pesanan":
				$flag = "B";
				$this->get_koneksi("B");
				$sql = "
					SELECT A.no_resi,
						CASE A.verifikasi 
						WHEN 'P' THEN 'VERIFIKASI STOK BARANG'
						WHEN 'F' THEN 'SUDAH VERIFIKASI'
						END AS status_verifikasi_stok,
						CASE A.konfirmasi
						WHEN 'P' THEN 'VERIFIKASI PEMBAYARAN'
						WHEN 'P' THEN 'SUDAH BAYAR'
						END AS status_konfirmasi_pembayaran,
						CASE A.produksi
						WHEN 'P' THEN 'PROSES PRODUKSI'
						WHEN 'F' THEN 'PRODUKSI SELESAI'
						END AS status_produksi,
						CASE A.packing
						WHEN 'P' THEN 'PROSES PACKING'
						WHEN 'F' THEN 'PACKING SELESAI'
						END AS status_packing,
						CASE A.kirim
						WHEN 'P' THEN 'PROSES KIRIM'
						WHEN 'F' THEN 'BARANG TERKIRIM'
						END AS status_kirim
					FROM tbl_monitoring_order A
					WHERE A.tbl_h_pemesanan_id = '".$p1."'
				";
			break;
			case "datacustomer":
				$flag = "B";
				$this->get_koneksi("B");
				$where = " AND A.id = '".$p1."' ";
				$sql = "
					SELECT A.*,
						B.provinsi, C.kab_kota, D.kecamatan
					FROM tbl_registrasi A
					LEFT JOIN cl_provinsi B ON B.kode_prov = A.cl_provinsi_kode
					LEFT JOIN cl_kab_kota C ON C.kode_kab_kota = A.cl_kab_kota_kode
					LEFT JOIN cl_kecamatan D ON D.kode_kecamatan = A.cl_kecamatan_kode
					WHERE 1=1 $where
				";
			break;			
			case "header_pesanan":
				$flag = "B";
				$this->get_koneksi("B");
				$sql = "
					SELECT A.*, A.id as idpesan, A.status as sts_pesan,
						B.jasa_pengiriman, C.metode_pembayaran, D.*, E.provinsi, F.kab_kota, G.kecamatan
					FROM tbl_h_pemesanan A
					LEFT JOIN cl_jasa_pengiriman B ON B.id = A.cl_jasa_pengiriman_id
					LEFT JOIN cl_metode_pembayaran C ON C.id = A.cl_metode_pembayaran_id
					LEFT JOIN tbl_registrasi D ON D.id = A.tbl_registrasi_id
					LEFT JOIN cl_provinsi E ON E.kode_prov = D.cl_provinsi_kode
					LEFT JOIN cl_kab_kota F ON F.kode_kab_kota = D.cl_kab_kota_kode
					LEFT JOIN cl_kecamatan G ON G.kode_kecamatan = D.cl_kecamatan_kode					
					WHERE A.no_order = '".$p1."'
				";
			break;
			case "detail_pesanan":
				$flag = "B";
				$this->get_koneksi("B");
				$sql = "
					SELECT A.*,
						B.judul_buku, C.kelas, D.nama_group
					FROM tbl_d_pemesanan A
					LEFT JOIN tbl_buku B ON B.id = A.tbl_buku_id
					LEFT JOIN cl_kelas C ON C.id = B.cl_kelas_id
					LEFT JOIN cl_group_sekolah D ON D.id = B.cl_group_sekolah
					WHERE A.tbl_h_pemesanan_id = '".$p1."'
				";
			break;			
			
			case "detail_invoice":
				$this->get_koneksi("B");
				$data=array();
				$id = $this->input->post('id');
				if($id)$where .=" AND A.id=".$id;
				
				$sql = "
					SELECT A.*,B.nama_sekolah,B.nama_lengkap,B.jenis_pembeli,
						DATE_FORMAT(A.tgl_order,'%d %b %Y %h:%i %p') as tanggal_order
					FROM tbl_h_pemesanan A 
					LEFT JOIN tbl_registrasi B ON A.tbl_registrasi_id=B.id ".$where."	
				";
				$data['header']=$this->db_remote->query($sql)->row_array();
				
				$sql="SELECT A.*,B.judul_buku,(A.qty*A.harga)as total
					  FROM tbl_d_pemesanan A 
					  LEFT JOin tbl_buku B ON A.tbl_buku_id=B.id
					  WHERE A.tbl_h_pemesanan_id=".$id;
				$data['detil']=$this->db_remote->query($sql)->result_array();
				
				return $data;
				exit;
			break;			
			case "sales":
				$sql = "
					SELECT A.*, B.provinsi, C.kab_kota, D.kecamatan,
						DATE_FORMAT(A.registration_date,'%d %b %Y %h:%i %p') as tgl_daftar
					FROM tbl_registration A 
					LEFT JOIN cl_provinsi B ON B.kode_prov = A.cl_provinsi_id
					LEFT JOIN cl_kab_kota C ON C.kode_kab_kota = A.cl_kab_kota_id
					LEFT JOIN cl_kecamatan D ON D.kode_kecamatan = A.cl_kecamatan_id
					$where AND A.pic_id = ".$this->auth["id"]."
				";
			break;
			case "penjualan":
				if($this->auth["role"] == "PIC"){
					$sqlsales = "
						SELECT registration_code
						FROM tbl_registration
						WHERE pic_id = ".$this->auth["id"]."
					";
					$querysales = $this->db->query($sqlsales)->result_array();
					$arraysales = array();
					foreach($querysales as $k=>$v){
						$arraysales[$k] = $v['registration_code'];
					}
					if($arraysales){
						$join_array = join("','",$arraysales);
						$where .= "
							AND A.kode_marketing IN ('".$join_array."') 
						";
					}else{
						$where .= "
							AND A.kode_marketing = '".$this->auth['registration_code']."'
						";
					}
				}else{
					$where .= " AND A.kode_marketing='".$this->auth['registration_code']."' ";
				}
				
				$sql = "
					SELECT A.*, B.nama_sekolah, B.nama_lengkap,
						DATE_FORMAT(A.tgl_order,'%d %b %Y %h:%i %p') as tanggal_order
					FROM tbl_h_pemesanan A 
					LEFT JOIN tbl_registrasi B ON A.tbl_registrasi_id = B.id 
					".$where." AND B.jenis_pembeli = 'SEKOLAH'
					ORDER BY A.id DESC
				";
				
				//echo $sql;exit;
			break;
			case "data_login":
				$sql = "
					SELECT A.*
					FROM tbl_registration A
					WHERE username = '".$p1."'
				";
			break;

			
			// Kodingan Sing Lawas
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
				$where =" WHERE A.kode_marketing='".$this->auth['registration_code']."' AND B.jenis_pembeli='SEKOLAH' ";
				$sql=$sql_na.$where.$order;
				$data['trans_buku_sekolah']=$this->db_remote->query($sql)->result_array();
				$where =" WHERE A.kode_marketing='".$this->auth['registration_code']."' AND B.jenis_pembeli='UMUM' ";
				$sql=$sql_na.$where.$order;
				$data['trans_buku_umum']=$this->db_remote->query($sql)->result_array();
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
				$sql="SELECT A.* FROM ".$type." A ".$where;
			break;
		}
		
		if($balikan == 'json'){
			if($flag){
				return $this->get_json_grid($type,$sql,$flag);
			}else{
				return $this->get_json_grid($type,$sql,'lokal');
			}
			
		}elseif($balikan == 'row_array'){
			if($flag){
				$data=$this->db_remote->query($sql)->row_array();
				$this->db_remote->close();
				return $data;
			}
			else{
				return $this->db->query($sql)->row_array();
			}
		}elseif($balikan == 'result'){
			if($flag){
				$data=$this->db_remote->query($sql)->result();
				$this->db_remote->close();
				return $data;
			}else{
				return $this->db->query($sql)->result();
			}
		}elseif($balikan == 'get'){
			return $this->db->query($sql)->row_array();
		}elseif($balikan == 'result_array'){
			if($flag){
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
	function get_json_grid($type,$sql,$koneksi){
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
		
		if($type == "penjualan"){
			if($this->auth["role"] == "PIC"){
				foreach($data as $k => $v){
					$cek_sales = $this->db->get_where("tbl_registration", array("registration_code"=>$v["kode_marketing"]) )->row_array();
					$data[$k]["nama_sales"] = $cek_sales["nama_lengkap"];
				}
			}
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
				if(!$provinsi){
					$provinsi = $p2;
				}
				$sql = "
					SELECT kode_kab_kota as id, kab_kota as txt
					FROM cl_kab_kota
					WHERE cl_provinsi_kode = '".$provinsi."'
				";
			break;
			case "cl_kecamatan":
				$kab_kota = $this->input->post('v2');
				if(!$kab_kota){
					$kab_kota = $p2;
				}
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
			case "uploadfile_basttandaterima":
				$this->get_koneksi("B");
				$sts_crud = 'falseto';
				$array_cek_invoice = array( 'no_order' => $data['inv'] );
				$cek_invoice = $this->db_remote->get_where('tbl_h_pemesanan', $array_cek_invoice)->row_array();
				if($cek_invoice){
					$tbl_h_pemesanan_id = $cek_invoice['id'];
				}else{
					$tbl_h_pemesanan_id = null;
					echo 2; exit; //cek data invoice exist;
				}
				
				$cek_dir = "../mks/__repository/bast_tandaterima/";
				if(!is_dir($cek_dir)) {
					mkdir($cek_dir, 0777);         
				}			
				
				$upload_path = "../mks/__repository/bast_tandaterima/";
				if(!empty($_FILES['fl_bast']['name'])){					
					$file_bast = "BAST-".$cek_invoice['no_order'];
					$filename_bast =  $this->lib->uploadnong($upload_path, 'fl_bast', $file_bast); //$file.'.'.$extension;
				}else{
					$filename_bast = null;
				}
				
				if(!empty($_FILES['fl_tndterima']['name'])){					
					$file_tandaterima = "TANDATERIMA-".$cek_invoice['no_order'];
					$filename_tandaterima =  $this->lib->uploadnong($upload_path, 'fl_tndterima', $file_tandaterima); //$file.'.'.$extension;
				}else{
					$filename_tandaterima = null;
				}
				
				$data_upload = array(
					'tbl_h_pemesanan_id' => $cek_invoice['id'],
					'file_bast' => $filename_bast,
					'file_tanda_terima' => $filename_tandaterima,
					'create_date' => date('Y-m-d H:i:s'),
				);
				$this->db_remote->insert('tbl_uploadfile', $data_upload);
			break;
			case "konfirmasi_pembayaran":
				$this->get_koneksi("B");
				$sts_crud = 'falseto';
				$data_inv = $this->db_remote->get_where('tbl_h_pemesanan', array('no_order'=>$data['inv']) )->row_array();
				if($data_inv){	
					$sql_maxkonf = "
						SELECT MAX(no_konfirmasi) as konfirmasi_no
						FROM tbl_konfirmasi
					";
					$maxkonf = $this->db_remote->query($sql_maxkonf)->row_array();
					if($maxkonf['konfirmasi_no'] != null){
						$acak_no_konf = ($maxkonf['konfirmasi_no'] + 1); 
					}else{
						$acak_no_konf = 1;
					}
					
					$cek_dir = "../mks/__repository/konfirmasi/";
					if(!is_dir($cek_dir)) {
						mkdir($cek_dir, 0777);         
					}			
					
					if(!empty($_FILES['bkt_byr']['name'])){					
						$upload_path = "../mks/__repository/konfirmasi/";
						$file = "FILEBUKTIBAYAR-".$data_inv['no_order'];
						$filename =  $this->lib->uploadnong($upload_path, 'bkt_byr', $file); //$file.'.'.$extension;
					}else{
						$filename = null;
					}
					
					$total_pembayaran = trim($data['jml_trf']);
					$total_pembayaran = str_replace(".", "", $total_pembayaran);
					
					$array_insert = array(
						'no_konfirmasi' => $acak_no_konf,
						'tgl_konfirmasi' => date('Y-m-d'),
						'tbl_h_pemesanan_id' => $data_inv['id'],
						'total_pembayaran' => (int)$total_pembayaran,
						'nama_bank_pengirim' => $data['bank_pengirim'],
						'atas_nama_pengirim' => $data['atas_nama'],
						'tanggal_transfer' => $data['tgl_trf'],
						'nama_bank_penerima' => $data['bank_tujuan'],
						'flag' => 'P',
						'create_date' => date('Y-m-d H:i:s'),
						'create_by' => "SALES - ".$this->auth["nama_lengkap"],
						'file_bukti_bayar' => $filename,
					);
					$crud = $this->db_remote->insert('tbl_konfirmasi', $array_insert);
					if($crud){
						//$this->db->update('tbl_h_pemesanan', array('status'=>'F'), array('no_order'=>$data['inv']) );
						$email = $this->getdata('getemail_cust', 'row_array', $data['inv']);
						$this->lib->kirimemail('email_konfirmasi', $email['email'], $data['inv']);
					}
					
					
					/* Koding Lawas
					if($data_inv['status'] == 'P'){ // konfirmasi untuk pembeli umum
						$array_insert = array(
							'no_konfirmasi' => $acak_no_konf,
							'tgl_konfirmasi' => date('Y-m-d'),
							'tbl_h_pemesanan_id' => $data_inv['id'],
							'total_pembayaran' => (int)$total_pembayaran,
							'nama_bank_pengirim' => $data['bank_pengirim'],
							'atas_nama_pengirim' => $data['atas_nama'],
							'tanggal_transfer' => $data['tgl_trf'],
							'nama_bank_penerima' => $data['bank_tujuan'],
							'flag' => 'P',
							'create_date' => date('Y-m-d H:i:s'),
							'file_bukti_bayar' => $filename,
						);
						$crud = $this->db->insert('tbl_konfirmasi', $array_insert);
						if($crud){
							$this->db->update('tbl_h_pemesanan', array('status'=>'F'), array('no_order'=>$data['inv']) );
							$email = $this->getdata('getemail_cust', 'row_array', $data['inv']);
							$this->lib->kirimemail('email_konfirmasi', $email['email'], $data['inv']);
						}

					}elseif($data_inv['status'] == 'B'){ // konfirmasi untuk pembeli sekolah
						$array_update = array(
							'no_konfirmasi' => $acak_no_konf,
							'tgl_konfirmasi' => date('Y-m-d'),
							'total_pembayaran' => (int)$total_pembayaran,
							'nama_bank_pengirim' => $data['bank_pengirim'],
							'atas_nama_pengirim' => $data['atas_nama'],
							'tanggal_transfer' => $data['tgl_trf'],
							'nama_bank_penerima' => $data['bank_tujuan'],
							'flag' => 'P',
							'create_date' => date('Y-m-d H:i:s'),
							'file_bukti_bayar' => $filename,
						);
						$crud = $this->db->update('tbl_konfirmasi', $array_update, array('tbl_h_pemesanan_id' => $data_inv['id']) );
						if($crud){
							//$this->db->update('tbl_h_pemesanan', array('status'=>'F'), array('no_order'=>$data['inv']) );
							$email = $this->getdata('getemail_cust', 'row_array', $data['inv']);
							$this->lib->kirimemail('email_konfirmasi', $email['email'], $data['inv']);
						}
						
					}
					*/
					
				}else{
					echo 2;
					exit;
				}
			break;
			case "registrasi_sales":
				$table = "tbl_registration";
				$cek_email = $this->db->get_where("tbl_registration", array("email_address"=>$data['alamat_email']) )->row_array();
				if($cek_email){
					echo 2; //email sudah ada
					exit;
				}
				
				$this->load->library('encrypt');
				
				$pswd = strtolower($this->lib->randomString(6, 'angka'));
				$member_user = $this->lib->randomString(5, 'huruf');
				$cek_member_user = $this->db->get_where("tbl_registration", array("registration_code"=>strtoupper($member_user)) )->row_array();
				if($cek_member_user){
					$member_user = $this->lib->randomString(5, 'angkahuruf');
				}
				
				$data['registration_date'] = date("Y-m-d H:i:s");
				$data['nama_lengkap'] = $data['nlengkap'];
				$data['cl_provinsi_id'] = $data['prv'];
				$data['cl_kab_kota_id'] = $data['kab'];
				$data['cl_kecamatan_id'] = $data['kec'];
				$data['kode_pos'] = $data['kdpos'];
				$data['alamat'] = $data['alamatpalsu'];
				$data['no_ktp'] = $data['ktp'];
				$data['no_handphone'] = $data['hp'];
				$data['email_address'] = $data['alamat_email'];
				$data['role'] = "SALES";
				$data['registration_code'] = strtoupper($member_user);
				$data['pic_id'] = $this->auth["id"];
				$data['username'] = $data['alamat_email'];
				$data['password'] = $this->encrypt->encode($pswd);
				$data['status'] = 1;
				$data['create_by'] = $this->auth["nama_lengkap"];
				
				unset($data['nlengkap']);
				unset($data['prv']);
				unset($data['kab']);
				unset($data['kec']);
				unset($data['kdpos']);
				unset($data['alamatpalsu']);
				unset($data['alamat_email']);
				unset($data['ktp']);
				unset($data['hp']);
			break;
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
				unset($data['prv']);
				unset($data['kab']);
				unset($data['kec']);
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
				
				$table = "tbl_registration";
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
				if($table == "tbl_registration"){
					$this->lib->kirimemail('email_register_sales', $data['email_address'], $pswd, strtoupper($member_user), $data['nama_lengkap'] );
				}
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
