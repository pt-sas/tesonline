<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_column_cbt_user extends CI_Migration
{
    public function up()
    {
        $fields = array(
            'datelastlogin' => array(
                'type' => 'DATETIME',
                'null' => FALSE,
                'after' => 'user_detail'
            ),
            'islogin' => array(
                'type' => 'CHAR',
                'constraint' => '1',
                'default' => 'N',
                'null' => FALSE
            )
        );

        // 13042021_1045 add column
        $this->dbforge->add_column('cbt_user', $fields);
    }

    public function down()
    {
        $fields = array(
            'datelastlogin',
            'islogin'
        );

        $this->dbforge->drop_column('cbt_user', $fields);
    }
}
