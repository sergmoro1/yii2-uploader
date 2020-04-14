<?php
/**
 * @link https://github.com/sergmoro1/yii2-uploader
 * @copyright Copyright (c) 2020 Sergey Morozov
 * @license MIT
 */

namespace tests;

use yii\imagine\Image;
use tests\fixtures\OneFileKeeperTrait;
use tests\fixtures\OneFileCropperTrait;
use tests\models\Photo;

class OneFileCropperTest extends \PHPUnit\Framework\TestCase
{
    use OneFileKeeperTrait;
    use OneFileCropperTrait;
    
    public $subdir = '';
    public $name = 'image.jpg';
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
        rmdir(__DIR__ . '/data/files/photo/original');
        rmdir(__DIR__ . '/data/files/photo/thumb');
    }
    
    /**
     * Get image, save and resize it in a folder according with the model sizes.
     * Crop it then.
     * 
     * @covers components\OneFileKeeper::proceed
     * @covers components\OneFileCropper::proceed
     * @covers behaviors\HaveFileBehavior::getAbsoluteFilePath()
     * @covers behaviors\HaveFileBehavior::getFilePath()
     * @covers behaviors\HaveFileBehavior::setFilePath()
     * @covers behaviors\HaveFileBehavior::deleteFile()
     * @covers behaviors\ImageTransformationBehavior::resizeSave
     */
    public function testUploadImageAndCrop()
    {
        $result = $this->keeper->proceed($this->fileinput);
        $this->assertSame($result['success'], true);
        $this->name = $result['file']['name'];

        $cropper = $this->makeCropper($result['file']['name']);
        $result = $cropper->proceed();
        
        $main = Image::getImagine()->open($result['file']['path'] . $result['file']['name']);
        $mainSize = $main->getSize();

        $this->assertSame($mainSize->getWidth(), $this->scaleWidth);
        $this->assertSame($mainSize->getHeight(), $this->scaleHeight);
    }
}
