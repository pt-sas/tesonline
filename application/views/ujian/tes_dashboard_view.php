<div class="container">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Selamat Datang, <?php if (!empty($nama)) {
                                echo $nama;
                            }
                            if (!empty($group)) {
                                echo ' (' . $group . ')';
                            } ?>
            <small>Rekrutmen Online PT. Sahabat Abadi Sejahtera</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">dashboard</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="callout callout-info">
            <h4>Informasi</h4>
            <p>Silahkan pilih Tes yang diikuti dari daftar tes yang tersedia dibawah ini. Apabila tes tidak muncul, silahkan menghubungi Team HRD kami.</p>
        </div>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Daftar Tes</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-primary btn-sm" onclick="reloadTable()"><i class="fa fa-refresh"> Refresh</i></button>
                </div>
            </div><!-- /.box-header -->
            <div class="box-body">
                <table id="table-tes" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th class="all">Tes</th>
                            <th>Waktu Mulai Tes</th>
                            <th>Waktu Selesai Tes</th>
                            <th>Status</th>
                            <th class="all">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                        </tr>
                    </tbody>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </section><!-- /.content -->
</div><!-- /.container -->

<script type="text/javascript">
    $(function() {
        $('#table-tes').DataTable({
            "paging": true,
            "iDisplayLength": 10,
            "bProcessing": true,
            "bServerSide": true,
            'language': {
                'processing': '<i class="fa fa-spinner fa-spin fa-1x fa-fw"></i><span> Processing...</span>'
            },
            "searching": false,
            "aoColumns": [{
                    "bSearchable": false,
                    "bSortable": false,
                    "sWidth": "20px"
                },
                {
                    "bSearchable": false,
                    "bSortable": false
                },
                {
                    "bSearchable": false,
                    "bSortable": false,
                    "sWidth": "150px"
                },
                {
                    "bSearchable": false,
                    "bSortable": false,
                    "sWidth": "150px"
                },
                {
                    "bSearchable": false,
                    "bSortable": false,
                    "sWidth": "100px"
                },
                {
                    "bSearchable": false,
                    "bSortable": false,
                    "sWidth": "30px"
                }
            ],
            "sAjaxSource": "<?php echo site_url() . '/' . $url; ?>/get_datatable/",
            "autoWidth": false,
            "responsive": true
        });
    });

    function reloadTable() {
        $('#table-tes').dataTable().fnReloadAjax();
    }
</script>