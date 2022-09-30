<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Tes_rekap_hasil extends Member_Controller
{
    private $kode_menu = 'tes-rekap';
    private $kelompok = 'tes';
    private $url = 'manager/tes_rekap_hasil';

    function __construct()
    {
        parent::__construct();
        $this->load->model('cbt_user_model');
        $this->load->model('cbt_user_grup_model');
        $this->load->model('cbt_tes_model');
        $this->load->model('cbt_tes_token_model');
        $this->load->model('cbt_tes_topik_set_model');
        $this->load->model('cbt_tes_user_model');
        $this->load->model('cbt_tesgrup_model');
        $this->load->model('cbt_soal_model');
        $this->load->model('cbt_jawaban_model');
        $this->load->model('cbt_tes_soal_model');
        $this->load->model('cbt_tes_soal_jawaban_model');

        parent::cek_akses($this->kode_menu);
    }

    public function index()
    {
        $data['kode_menu'] = $this->kode_menu;
        $data['url'] = $this->url;

        $username = $this->access->get_username();
        $user_id = $this->users_model->get_login_info($username)->id;

        $tanggal_awal = date('Y-m-d', strtotime('- 1 days'));
        $tanggal_akhir = date('Y-m-d');

        $data['rentang_waktu'] = $tanggal_awal . ' - ' . $tanggal_akhir;

        $query_group = $this->cbt_user_grup_model->get_group();
        $select = '';
        if ($query_group->num_rows() > 0) {
            $query_group = $query_group->result();
            foreach ($query_group as $temp) {
                $select = $select . '<option value="' . $temp->grup_id . '">' . $temp->grup_nama . '</option>';
            }
        } else {
            $select = '<option value="0">Tidak Ada Group</option>';
        }
        $data['select_group'] = $select;

        $this->template->display_admin($this->kelompok . '/tes_rekap_hasil_tes_view', 'Rekapitulasi Hasil Tes', $data);
    }

    public function export()
    {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('pilih-grup', 'Grup', 'required|strip_tags');
        $this->form_validation->set_rules('nama-grup', 'Grup', 'required|strip_tags');
        $this->form_validation->set_rules('pilih-rentang-waktu', 'Rentang Waktu', 'required|strip_tags');

        $this->load->library('excel');
        $this->load->library('tools');

        $inputFileName = './public/form/form-data-rekap-hasil-tes.xlsx';
        $excel = PHPExcel_IOFactory::load($inputFileName);
        $worksheet = $excel->getSheet(0);

        if ($this->form_validation->run() == TRUE) {
            $rentang_waktu = $this->input->post('pilih-rentang-waktu', true);
            $tanggal = explode(" - ", $rentang_waktu);
            $grup = $this->input->post('pilih-grup', true);
            $nama_grup = $this->input->post('nama-grup', true);

            // Mengambil Data Peserta berdasarkan grup tes
            $query_user = $this->cbt_tes_user_model->get_by_tes_user_group_tanggal($tanggal[0], $tanggal[1], $grup, 'user_id');
            // Mengambil data Tes dalam rentang. Data tes diambil dari data daftar Tes
            $query_tes = $this->cbt_tes_user_model->get_by_tes_user_group_tanggal($tanggal[0], $tanggal[1], $grup, 'tes_id');

            $worksheet->setCellValueByColumnAndRow(2, 3, $nama_grup);
            $worksheet->setCellValueByColumnAndRow(2, 4, $this->tools->indonesian_date($tanggal[0], 'j F Y', '') . ' - ' . $this->tools->indonesian_date($tanggal[1], 'j F Y', ''));
            $worksheet->setCellValueByColumnAndRow(2, 5, $query_tes->num_rows() . ' Tes');

            if ($query_user->num_rows() > 0 && $query_tes->num_rows() > 0) {
                $query_tes = $query_tes->result();
                $query_user = $query_user->result();

                $kolom = 6;
                foreach ($query_tes as $tes) {
                    $worksheet->setCellValueByColumnAndRow($kolom, 8, $tes->tes_nama);
                    // Otomatis tinggi kolom
                    $worksheet->getRowDimension(8)->setRowHeight(-1);
                    $kolom++;
                }

                $row = 9;
                foreach ($query_user as $user) {
                    $worksheet->setCellValueByColumnAndRow(0, $row, ($row - 8));
                    $worksheet->setCellValueByColumnAndRow(1, $row, $user->user_name);
                    $worksheet->setCellValueByColumnAndRow(2, $row, $user->user_firstname);
                    $worksheet->setCellValueByColumnAndRow(3, $row, $nama_grup);
                    $worksheet->setCellValueByColumnAndRow(4, $row, $user->user_detail);
                    $worksheet->setCellValueByColumnAndRow(5, $row, $user->total);

                    $worksheet->getStyle('E9:E' . $worksheet->getHighestRow())
                        ->getAlignment()
                        ->setWrapText(true);
                    $worksheet->getStyle('B9:B' . $worksheet->getHighestRow())
                        ->getAlignment()
                        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $worksheet->getStyle('C9:C' . $worksheet->getHighestRow())
                        ->getAlignment()
                        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $worksheet->getStyle('D9:D' . $worksheet->getHighestRow())
                        ->getAlignment()
                        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                    $kolom = 6;
                    foreach ($query_tes as $tes) {
                        // Mendapatkan nilai tiap Tes untuk setiap siswa
                        $query_nilai = $this->cbt_tes_user_model->get_nilai_by_tes_user($tes->tes_id, $user->user_id);
                        if ($query_nilai->num_rows() > 0) {
                            $query_nilai = $query_nilai->row();
                            $worksheet->setCellValueByColumnAndRow($kolom, $row, $query_nilai->nilai);
                        } else {
                            $worksheet->setCellValueByColumnAndRow($kolom, $row, 'N/A');
                        }

                        $kolom++;
                    }

                    $row++;
                }
            }
        }

        $filename = 'Data Rekap Hasil Tes' . '_' . $rentang_waktu . ' (' . $nama_grup . ').xlsx'; //save our workbook as this file name
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); //mime type
        header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache

        //save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
        //if you want to save it as .XLSX Excel 2007 format
        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        //force user to download the Excel file without writing it to server's HD
        $objWriter->save('php://output');
    }
}
