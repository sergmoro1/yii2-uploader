<?php
/**
 * @link https://github.com/sergmoro1/yii2-uploader
 * @copyright Copyright (c) 2020 Sergey Morozov
 * @license MIT
 */

namespace tests;

use tests\fixtures\OneFileKeeperTrait;
use tests\models\Photo;

class OneFileKeeperSmallImageTest extends \PHPUnit\Framework\TestCase
{
    use OneFileKeeperTrait;
    
    public $subdir = '';
    public $name = 'image_small.jpg';
    protected $keeper;
    protected $fileinput = 'fileinput';
    
    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $file = $this->name;
        $fileinput = $this->fileinput; 
        $_FILES[$fileinput]['name'] = $file;
        $_FILES[$fileinput]['tmp_name'] = (__DIR__ . '/data/tmp/' . $file);
        $_FILES[$fileinput]['type'] = 'image/jpg';
        $_FILES[$fileinput]['size'] = filesize(__DIR__ . '/data/tmp/' . $file);
        $_FILES[$fileinput]['error'] = UPLOAD_ERR_OK;

        $this->keeper = $this->makeKeeper();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $model = new Photo(['id' => 1, 'category' => 'Street']);
        $model->deleteFile($this);
    }

    /**
     * Trying to upload file with a small width and height.
     * 
     * @covers components\OneFileKeeper::proceed
     * @covers behaviors\HaveFileBehavior::getAbsoluteFilePath()
     * @covers behaviors\HaveFileBehavior::getFilePath()
     * @covers behaviors\HaveFileBehavior::setFilePath()
     * @covers behaviors\ImageTransformationBehavior::resizeSave */
    public function testUploadFileWithSmallWidthAndHeight()
    {
        $result = $this->keeper->proceed($this->fileinput);
        $this->assertSame($result['success'], false);
        $this->assertSame($result['message'], 'The width or height of the image, or both, is smaller than necessary [800, 600]px.');
    }
}
