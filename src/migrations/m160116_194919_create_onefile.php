<?php
namespace sergmoro1\uploader\migrations;

use yii\db\Schema;
use yii\db\Migration;

class m160116_194919_create_onefile extends Migration
{
    private const TABLE_ONEFILE = '{{%onefile}}';
    
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(static::TABLE_ONEFILE, [
            'id'            => $this->primaryKey(),
            'model'         => $this->string(256)->notNull(),
            'parent_id'     => $this->integer()->notNull(),
            'original'      => $this->string(256)->notNull(),
            'name'          => $this->string(32)->notNull(),
            'subdir'        => $this->string(256)->notNull(),
            'type'          => $this->string(256)->notNull(),
            'size'          => $this->integer()->notNull(),
            'defs'          => $this->text(),
            
            'created_at'    => $this->integer(),
            'updated_at'    => $this->integer(),
        ], $tableOptions);

        $this->createIndex('idx-model-parent', static::TABLE_ONEFILE, ['model', 'parent_id']);

        $this->addCommentOnColumn(static::TABLE_ONEFILE, 'model',     'Model namespace');
        $this->addCommentOnColumn(static::TABLE_ONEFILE, 'parent_id', 'Model ID');
        $this->addCommentOnColumn(static::TABLE_ONEFILE, 'original',  'Translited file name');
        $this->addCommentOnColumn(static::TABLE_ONEFILE, 'type',      'Mime type');
        $this->addCommentOnColumn(static::TABLE_ONEFILE, 'size',      'Size');

        $this->addCommentOnColumn(static::TABLE_ONEFILE, 'name',      'Generated unique file name');
        $this->addCommentOnColumn(static::TABLE_ONEFILE, 'subdir',    'Subdirectory in a model directory, may be various from model to model or the same');
        $this->addCommentOnColumn(static::TABLE_ONEFILE, 'defs',      'Additional variables linked with file are saved as json array');
    }

    public function down()
    {
        $this->dropTable((self::TABLE_ONEFILE);
    }
}
