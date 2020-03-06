<?php
/**
 * @link https://github.com/sergmoro1/yii2-uploader
 * @copyright Copyright (c) 2020 Sergey Morozov
 * @license MIT
 */

namespace tests;

use yii\imagine\Image;
use tests\mock\OneFileKeeperTrait;
use tests\mock\OneFileCropperTrait;
use tests\models\Photo;

class OneFileCropperTest extends \PHPUnit\Framework\TestCase
{
    use OneFileKeeperTrait;
    use OneFileCropperTrait;
    
    protected $subdir = '';
    protected $fileinput = 'fileinput';
    protected $file = 'image.jpg';
    
    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $file = $this->file;
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
        $model->deleteFile($this->subdir, $this->file);
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
        $this->file = $result['file']['name'];

        $cropper = $this->makeCropper($result['file']['name']);
        $result = $cropper->proceed();
        
        $main = Image::getImagine()->open($result['file']['path'] . $result['file']['name']);
        $mainSize = $main->getSize();

        $this->assertSame($mainSize->getWidth(), $this->scaleWidth);
        $this->assertSame($mainSize->getHeight(), $this->scaleHeight);
    }
}
