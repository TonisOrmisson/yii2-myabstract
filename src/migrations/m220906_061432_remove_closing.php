<?php

use yii\db\Migration;

/**
 * Class m220906_061432_remove_closing
 */
class m220906_061432_remove_closing extends Migration
{
    public function safeUp()
    {
        $this->dropTable('{{closing}}');
    }

    public function safeDown()
    {
        $this->createTable('{{closing}}', [
            'table_name'=>$this->string(128)->notNull()->comment('referred entity table name'),
            'last_closing_time'=>$this->dateTime(6)->notNull()->comment('Last closure time in that entity'),
        ]);
        $this->addPrimaryKey('pk_closing', '{{closing}}', 'table_name');
    }

}
