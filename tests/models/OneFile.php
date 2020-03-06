<?php

namespace tests\models;

use yii\base\Model;

/**
 * Mock class for sergmoro1\uploader\models\OneFile.
 *
 * @author Sergey Morozov <sergmoro1@ya.ru>
 */
class OneFile extends Model
{
    public $id;
    public $model;
    public $parent_id;
    public $name;
    public $original;
    public $subdir;
    public $type;
    public $size;
    public $defs;
    public $vars = null;
    public $created_at;
    public $updated_at;

    public function init()
    {
        parent::init();
        $this->vars = json_decode($this->defs);
    }
	
    public function save()
	{
		return true;
	}
}
