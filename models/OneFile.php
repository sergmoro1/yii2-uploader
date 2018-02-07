<?php
/**
 * This is the model class for table "{{onefile}}".
 * One model for all uploaded Files of a project.
 *
 * The followings are the available columns in table '{{onefile}}':
 * @property integer $id
 * @property string $model
 * @property integer $parent_id
 * @property string $name
 * @property string $chid
 * @property text $vars
 * @property integer $created
 *
 * All files belong to some parent model. For example User.
 */

namespace sergmoro1\uploader\models;

use Yii;
use yii\db\ActiveRecord;

class OneFile extends ActiveRecord
{
    public $vars;
    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return '{{%onefile}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            [['model', 'parent_id', 'name'], 'required'],
            ['parent_id', 'integer'],
            [['model', 'original'], 'string', 'max' => 128],
            ['name', 'string', 'max' => 32],
            [['defs', 'created_at'], 'safe'],
        ];
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->vars = json_decode($this->defs);
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            if($this->isNewRecord)
                $this->created_at = time();
            $this->defs = json_encode($this->vars);
            return true;
        }
        else
            return false;
    }
}
