<?php

namespace sergmoro1\uploader\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

use sergmoro1\rukit\behaviors\TransliteratorBehavior;

/**
 * OneFile model class.
 * One model for all uploaded files of a project.
 *
 * @author Sergey Morozov <sergmoro1@ya.ru>
 */
class OneFile extends ActiveRecord
{
    /**
     * The followings are the available columns in table '{{onefile}}':
     * @property integer $id
     * @property string  $model
     * @property integer $parent_id
     * @property string  $name
     * @property string  $original
     * @property string  $subdir
     * @property string  $type
     * @property string  $size
     * @property text    $defs
     * @property integer $created_at
     * @property integer $updated_at
     */

    /** @var object $vars decoded from json array $model->defs */
    public $vars;

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return '{{%onefile}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            ['class' => TimestampBehavior::className()],
            [
                'class' => TransliteratorBehavior::className(),
                'from'  => 'original',
                'to'    => 'original',
            ],
         ];
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            [['model', 'parent_id', 'name'], 'required'],
            [['parent_id', 'size'], 'integer'],
            [['model', 'original', 'type'], 'string', 'max' => 256],
            ['name', 'string', 'max' => 32],
            [['subdir', 'defs', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * Is uploaded file an image?
     * 
     * @return boolean
     */
    public function isImage()
    {
        return substr($this->type, 0, 5) == 'image';
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        // decode additional vars
        $this->vars = json_decode($this->defs);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            if ($insert)
                $this->translit();
            // save additional vars
            $this->defs = json_encode($this->vars);
            return true;
        }
        else
            return false;
    }
}
