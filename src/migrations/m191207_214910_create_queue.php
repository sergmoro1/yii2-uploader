<?php
namespace sergmoro1\uploader\migrations;

use yii\db\Schema;
use yii\db\Migration;

class m191207_214910_create_queue extends Migration
{
    const TABLE_QUEUE = '{{%queue}}';
    
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(self::TABLE_QUEUE, [
            'id'            => $this->primaryKey(),
            'channel'       => $this->string(256)->notNull(),
            'job'           => $this->binary(),
            'ttr'           => $this->integer()->notNull(),
            'delay'         => $this->integer()->defaultValue(0),
            'priority'      => $this->bigint()->notNull()->defaultValue(1024),
            'attempt'       => $this->integer()->defaultValue(NULL),
            
            'pushed_at'     => $this->integer(),
            'reserved_at'   => $this->integer()->defaultValue(NULL),
            'done_at'       => $this->integer()->defaultValue(NULL),
        ], $tableOptions);

        $this->createIndex('idx-channel', self::TABLE_QUEUE, 'channel');
        $this->createIndex('idx-reserved_at', self::TABLE_QUEUE, 'reserved_at');
        $this->createIndex('idx-priority', self::TABLE_QUEUE, 'priority');
    }

    public function down()
    {
        $this->dropTable(self::TABLE_QUEUE);
    }
}
