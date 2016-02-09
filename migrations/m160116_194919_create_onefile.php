<?php

use yii\db\Schema;
use yii\db\Migration;

class m160116_194919_create_onefile extends Migration
{
    public function up()
	{
		$this->createTable('onefile', [
			'id' => $this->primaryKey(),
			'model' => $this->string(128)->notNull(),
			'parent_id' => $this->integer(),
			'original' => $this->string(128)->notNull(),
			'name' => $this->string(32)->notNull(),
			'subdir' => $this->string(64)->notNull(),
			'defs' => $this->text(),
			'created_at' => $this->integer(),
		]);
	}

    public function down()
    {
        $this->dropTable('onefile');
    }
}
