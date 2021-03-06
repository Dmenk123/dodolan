<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan_retur_keluar extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('dashboard_adm/Mod_dashboard_adm','m_dasbor');
		$this->load->model('Mod_lap_ret_keluar','m_retout');
		//cek sudah login apa tidak
		if ($this->session->userdata('logged_in') != true) {
			redirect('home/error_404');
		}
		//cek level user
		if ($this->session->userdata('id_level_user') == "2" || $this->session->userdata('id_level_user') == "4") {
			redirect('home/error_404');
		}

		//pesan stok minimum
		$produk = $this->m_dasbor->get_produk();
		$link_notif = site_url('laporan_stok');
		foreach ($produk as $val) {
			if ($val->stok_sisa <= $val->stok_minimum) {
				$this->session->set_flashdata('cek_stok', 'Terdapat Stok produk dibawah nilai minimum, Mohon di cek ulang <a href="'.$link_notif.'">disini</a>');
			}
		}
	}

	public function index()
	{
		$id_user = $this->session->userdata('id_user'); 
		$data_user = $this->m_dasbor->get_data_user($id_user);

		$jumlah_notif = $this->m_dasbor->email_notif_count($id_user);  //menghitung jumlah email masuk
		$notif = $this->m_dasbor->get_email_notif($id_user); //menampilkan isi email

		$data = array(
			'content'=>'view_lap_retur_keluar',
			'css'=>'cssLapReturKeluar',
			'js'=>'jsLapReturKeluar',
			'data_user' => $data_user,
			'qty_notif' => $jumlah_notif,
			'isi_notif' => $notif
		);
		$this->load->view('temp_adm',$data);
	}

	public function laporan_retur_keluar_detail()
	{
		$id_user = $this->session->userdata('id_user'); 
		$data_user = $this->m_dasbor->get_data_user($id_user);

		$jumlah_notif = $this->m_dasbor->email_notif_count($id_user);  //menghitung jumlah email masuk
		$notif = $this->m_dasbor->get_email_notif($id_user); //menampilkan isi email

		$tanggal_awal = date("Y-m-d", strtotime($this->input->post('tanggalLapAwal')));
		$tanggal_akhir = date("Y-m-d", strtotime($this->input->post('tanggalLapAkhir')));
		$query = $this->m_retout->get_detail_lap_retur_keluar($tanggal_awal, $tanggal_akhir);
		//echo var_dump($query_header);
		$data = array(
			'css'=>'cssLapReturKeluar',
			'js'=>'jsLapReturKeluar',
			'content' => 'view_lap_retur_keluar_detail',
			'hasil_data' => $query,
			'tanggal_awal' => $tanggal_awal,
			'tanggal_akhir' => $tanggal_akhir,
			'tanggal' => $tanggal_awal.' s/d '.$tanggal_akhir,
			'data_user' => $data_user,
			'qty_notif' => $jumlah_notif,
			'isi_notif' => $notif,
		);
		$this->load->view('temp_adm',$data);
	}

	public function cetak_laporan_retur_keluar($tglAwal= 0, $tglAkhir= 0)
	{
		$this->load->library('Pdf_gen');
		$id_user = $this->session->userdata('id_user');
		$query = $this->m_retout->get_detail_lap_retur_keluar($tglAwal, $tglAkhir);

		$data = array(
			'title' => 'Laporan Retur',
			'hasil_data' => $query,
			'tanggal_awal' => $tglAwal,
			'tanggal_akhir' => $tglAkhir
		);

	    $html = $this->load->view('view_lap_retur_keluar_cetak', $data, true);
	    
	    $filename = 'laporan_retur_'.time();
	    $this->pdf_gen->generate($html, $filename, true, 'A4', 'portrait');
	}

}
