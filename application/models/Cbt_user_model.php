<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ZYA CBT
 * Achmad Lutfi
 * achmdlutfi@gmail.com
 * achmadlutfi.wordpress.com
 */
class Cbt_user_model extends CI_Model
{
    public $table = 'cbt_user';

    function __construct()
    {
        parent::__construct();
    }

    function save($data)
    {
        $this->db->insert($this->table, $data);
    }

    function delete($kolom, $isi)
    {
        $this->db->where($kolom, $isi)
            ->delete($this->table);
    }

    function update($kolom, $isi, $data)
    {
        $this->db->where($kolom, $isi)
            ->update($this->table, $data);
    }

    function count_by_kolom($kolom, $isi)
    {
        $this->db->select('COUNT(*) AS hasil')
            ->where($kolom, $isi)
            ->from($this->table);
        return $this->db->get();
    }

    function get_by_kolom($kolom, $isi)
    {
        $this->db->select('user_id,user_grup_id,user_name,user_password,user_email,user_firstname,user_detail,user_regdate')
            ->where($kolom, $isi)
            ->from($this->table);
        return $this->db->get();
    }

    function get_by_kolom_limit($kolom, $isi, $limit)
    {
        $this->db->select('user_id,user_grup_id,user_name,user_password,user_email,user_firstname,user_detail,user_regdate')
            ->where($kolom, $isi)
            ->from($this->table)
            ->limit($limit);
        return $this->db->get();
    }

    function count_by_username_password($username, $password)
    {
        $this->db->select('COUNT(*) AS hasil')
            ->where('(user_name="' . $username . '" AND user_password="' . $password . '")')
            ->from($this->table);
        return $this->db->get()->row()->hasil;
    }

    function get_by_username($username)
    {
        $this->db->select('*,
                    MIN(cbt_tes.tes_begin_time) AS max_start_tes,
                    MAX(cbt_tes.tes_end_time) AS min_end_tes');
        $this->db->join('cbt_user_grup', 'cbt_user.user_grup_id = cbt_user_grup.grup_id', 'left')
            ->join('cbt_tesgrup', 'cbt_user_grup.grup_id = cbt_tesgrup.tstgrp_grup_id', 'left')
            ->join('cbt_tes', 'cbt_tes.tes_id = cbt_tesgrup.tstgrp_tes_id', 'left')
            ->where('user_name', $username)
            ->where('DATE(cbt_tes.tes_begin_time)', 'CURDATE()', false)
            ->where('isactive', 'Y')
            ->limit(1);
        $query = $this->db->get($this->table);
        return ($query->num_rows() > 0) ? $query->row() : FALSE;
    }

    function get_datatable($start, $rows, $kolom, $isi, $group, $status)
    {
        $query = '';
        if ($group != 'semua') {
            $query = 'AND user_grup_id=' . $group;
        }

        if ($status == 1) {
            $status = 'Y';
        } else {
            $status = 'N';
        }
        $this->db->where('(' . $kolom . ' LIKE "%' . $isi . '%" ' . $query . ')')
            ->where('isactive', $status)
            ->from($this->table)
            ->order_by($kolom, 'ASC')
            ->limit($rows, $start);
        return $this->db->get();
    }

    function get_datatable_count($kolom, $isi, $group, $status)
    {
        $query = '';
        if ($group != 'semua') {
            $query = 'AND user_grup_id=' . $group;
        }

        if ($status == 1) {
            $status = 'Y';
        } else {
            $status = 'N';
        }

        $this->db->select('COUNT(*) AS hasil')
            ->where('(' . $kolom . ' LIKE "%' . $isi . '%" ' . $query . ')')
            ->where('isactive', $status)
            ->from($this->table);
        return $this->db->get();
    }

    /**
     * export data user yang belum mengerjakan
     */
    function get_by_tes_group_urut_tanggal($tes_id, $grup_id, $urutkan, $tanggal, $keterangan, $peserta)
    {
        if (!empty($tanggal)) {
            $this->db->where('tesuser_creation_time>="' . $tanggal[0] . '" AND tesuser_creation_time<="' . $tanggal[1] . '"');
        }

        if ($tes_id != 'semua') {
            $this->db->where('tes_id', $tes_id);
        }

        if ($grup_id != 'semua') {
            $this->db->where('user_grup_id', $grup_id);
        }

        if ($peserta != 'semua') {
            $this->db->where('tesuser_user_id', $peserta);
        }

        if (!empty($keterangan)) {
            $this->db->like('user_detail', $keterangan, 'match');
        }

        $order = '';
        if ($urutkan == 'nama') {
            $order = 'user_firstname ASC';
        } else if ($urutkan == 'waktu') {
            $order = 'tes_begin_time DESC';
        } else {
            $order = 'tes_id ASC';
        }

        $this->db->select('cbt_tes.*,cbt_user_grup.grup_nama, cbt_tes.*, cbt_user.*, "0" AS nilai, "Belum mengerjakan" AS tesuser_creation_time')
            ->from($this->table)
            ->join('cbt_user_grup', 'cbt_user.user_grup_id = cbt_user_grup.grup_id')
            ->join('cbt_tesgrup', 'cbt_tesgrup.tstgrp_grup_id = cbt_user_grup.grup_id')
            ->join('cbt_tes', 'cbt_tesgrup.tstgrp_tes_id = cbt_tes.tes_id')
            ->join('cbt_tes_user', '(cbt_tes_user.tesuser_tes_id = cbt_tes.tes_id) AND (cbt_tes_user.tesuser_user_id = cbt_user.user_id)', 'left')
            ->order_by($order);
        return $this->db->get();
    }

    /**
     * datatable untuk hasil tes yang belum mengerjakan
     *
     */
    function get_datatable_hasiltes($start, $rows, $tes_id, $grup_id, $urutkan, $tanggal, $keterangan, $peserta)
    {
        if (!empty($tanggal[0]) && !empty($tanggal[1])) {
            $this->db->where('tesuser_creation_time>="' . $tanggal[0] . '" AND tesuser_creation_time<="' . $tanggal[1] . '"');
        }

        if ($tes_id != 'semua') {
            $this->db->where('tes_id', $tes_id);
        }

        if ($grup_id != 'semua') {
            $this->db->where('user_grup_id', $grup_id);
        }

        if ($peserta != 'semua') {
            $this->db->where('tesuser_user_id', $peserta);
        }

        if (!empty($keterangan)) {
            $this->db->like('user_detail', $keterangan, 'match');
        }

        $order = '';
        if ($urutkan == 'nama') {
            $order = 'user_firstname ASC';
        } else if ($urutkan == 'waktu') {
            $order = 'tes_begin_time DESC';
        } else {
            $order = 'tes_id ASC';
        }

        $this->db->select('cbt_tes.*,cbt_user_grup.grup_nama, cbt_tes.*, cbt_user.*, "0" AS nilai')
            ->from($this->table)
            ->join('cbt_user_grup', 'cbt_user.user_grup_id = cbt_user_grup.grup_id')
            ->join('cbt_tesgrup', 'cbt_tesgrup.tstgrp_grup_id = cbt_user_grup.grup_id')
            ->join('cbt_tes', 'cbt_tesgrup.tstgrp_tes_id = cbt_tes.tes_id')
            ->join('cbt_tes_user', '(cbt_tes_user.tesuser_tes_id = cbt_tes.tes_id) AND (cbt_tes_user.tesuser_user_id = cbt_user.user_id)', 'left')
            ->order_by($order)
            ->limit($rows, $start);
        return $this->db->get();
    }

    function get_datatable_hasiltes_count($tes_id, $grup_id, $urutkan, $tanggal, $keterangan, $peserta)
    {
        if (!empty($tanggal[0]) && !empty($tanggal[1])) {
            $this->db->where('tesuser_creation_time>="' . $tanggal[0] . '" AND tesuser_creation_time<="' . $tanggal[1] . '"');
        }

        if ($tes_id != 'semua') {
            $this->db->where('tes_id', $tes_id);
        }

        if ($grup_id != 'semua') {
            $this->db->where('user_grup_id', $grup_id);
        }

        if ($peserta != 'semua') {
            $this->db->where('tesuser_user_id', $peserta);
        }

        if (!empty($keterangan)) {
            $this->db->like('user_detail', $keterangan, 'match');
        }

        $this->db->select('COUNT(*) AS hasil')
            ->join('cbt_user_grup', 'cbt_user.user_grup_id = cbt_user_grup.grup_id')
            ->join('cbt_tesgrup', 'cbt_tesgrup.tstgrp_grup_id = cbt_user_grup.grup_id')
            ->join('cbt_tes', 'cbt_tesgrup.tstgrp_tes_id = cbt_tes.tes_id')
            ->join('cbt_tes_user', '(cbt_tes_user.tesuser_tes_id = cbt_tes.tes_id) AND (cbt_tes_user.tesuser_user_id = cbt_user.user_id)', 'left')
            ->from($this->table);
        return $this->db->get();
    }

    function get_user()
    {
        $this->db->from($this->table)
            ->order_by('user_name', 'ASC');
        return $this->db->get();
    }
}
