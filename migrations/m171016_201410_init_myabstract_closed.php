<?php

use yii\db\Migration;

class m171016_201410_init_myabstract_closed extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{closing}}',[
            'table_name'=>$this->string(128)->notNull()->comment('referred entity table name'),
            'last_closing_time'=>$this->dateTime(6)->notNull()->comment('Last closure time in that entity'),
        ]);
        $this->addPrimaryKey('pk_closing','{{closing}}','table_name');
    }

    public function safeDown()
    {
        $this->dropTable('{{closing}}');
    }

}
