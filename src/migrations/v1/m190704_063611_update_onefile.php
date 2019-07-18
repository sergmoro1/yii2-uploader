<?php

use yii\db\Migration;

/**
 * Class m190704_063611_update_onefile
 */
class m190704_063611_update_onefile extends Migration
{
    private const TABLE_ONEFILE = '{{%onefile}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $this->execute('ALTER TABLE {{%onefile}} CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');
        }

        $this->alterColumn(static::TABLE_ONEFILE, 'model',    $this->string(256));
        $this->alterColumn(static::TABLE_ONEFILE, 'original', $this->string(256));
        $this->alterColumn(static::TABLE_ONEFILE, 'subdir',   $this->string(256));
        
        $this->addColumn(static::TABLE_ONEFILE, 'type',       $this->string(256)->defaultValue('image')->after('subdir'));
        $this->addColumn(static::TABLE_ONEFILE, 'size',       $this->integer()->defaultValue(0)->after('type'));
        $this->addColumn(static::TABLE_ONEFILE, 'updated_at', $this->integer()->defaultValue(time())->after('created_at'));

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

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-model-parent', static::TABLE_ONEFILE);

        $this->dropColumn(static::TABLE_ONEFILE, 'type');
        $this->dropColumn(static::TABLE_ONEFILE, 'size');
        $this->dropColumn(static::TABLE_ONEFILE, 'updated_at');
    }
}
