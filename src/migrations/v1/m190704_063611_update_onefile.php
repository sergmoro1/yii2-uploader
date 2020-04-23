<?php

use yii\db\Migration;

/**
 * Class m190704_063611_update_onefile
 */
class m190704_063611_update_onefile extends Migration
{
    private const TABLE = '{{%onefile}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $this->execute('ALTER TABLE {{%onefile}} CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');
        }

        $this->alterColumn(static::TABLE, 'model',    $this->string());
        $this->alterColumn(static::TABLE, 'original', $this->string());
        $this->alterColumn(static::TABLE, 'subdir',   $this->string());
        
        $this->addColumn(static::TABLE, 'type',       $this->string()->defaultValue('image')->after('subdir'));
        $this->addColumn(static::TABLE, 'size',       $this->integer()->defaultValue(0)->after('type'));
        $this->addColumn(static::TABLE, 'updated_at', $this->integer()->defaultValue(time())->after('created_at'));

        $this->createIndex('idx-onefile-model-parent', static::TABLE, ['model', 'parent_id']);

        $this->addCommentOnColumn(static::TABLE, 'model',     'Full model class name');
        $this->addCommentOnColumn(static::TABLE, 'parent_id', 'Parent model ID');
        $this->addCommentOnColumn(static::TABLE, 'original',  'Translited file name');
        $this->addCommentOnColumn(static::TABLE, 'type',      'Mime type');
        $this->addCommentOnColumn(static::TABLE, 'size',      'Size');

        $this->addCommentOnColumn(static::TABLE, 'name',      'Generated unique file name');
        $this->addCommentOnColumn(static::TABLE, 'subdir',    'Subdirectory in a model directory, may be various from model to model or the same');
        $this->addCommentOnColumn(static::TABLE, 'defs',      'Additional variables linked with file are saved as json array');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(static::TABLE, 'type');
        $this->dropColumn(static::TABLE, 'size');
        $this->dropColumn(static::TABLE, 'updated_at');
    }
}
