<?php
/**
 * @link https://github.com/sergmoro1/yii2-uploader
 * @copyright Copyright (c) 2020 Sergey Morozov
 * @license MIT
 */

namespace tests;

use tests\fixtures\OneFileKeeperTrait;
use tests\models\Photo;

class OneFileKeeperTest extends \PHPUnit\Framework\TestCase
{
    use OneFileKeeperTrait;
    
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
     * 
     * @covers components\OneFileKeeper::proceed
     * @covers behaviors\HaveFileBehavior::getAbsoluteFilePath()
     * @covers behaviors\HaveFileBehavior::getFilePath()
     * @covers behaviors\HaveFileBehavior::setFilePath()
     * @covers behaviors\HaveFileBehavior::deleteFile()
     * @covers behaviors\ImageTransformationBehavior::resizeSave
     */
    public function testUploadImageAndResize()
    {
        $result = $this->keeper->proceed($this->fileinput);
        $this->assertSame($result['success'], true);
        $this->file = $result['file']['name'];
    }

    /**
     * Trying to upload more files then expected.
     * 
     * @covers components\OneFileKeeper::proceed
     */
    public function testUploadMoreFileThenAllowed()
    {
        $this->keeper->alreadyUploaded = 2;
        $result = $this->keeper->proceed($this->fileinput);
        $this->assertSame($result['success'], false);
        $this->assertSame($result['message'], 'Too many files uploaded. Allowed 2.');
        $this->keeper->alreadyUploaded = 0;
    }

    /**
     * Trying to upload file with a small size.
     * 
     * @covers components\OneFileKeeper::proceed
     */
    public function testUploadFileWithSmallSize()
    {
        $this->keeper->minFileSize = 204800;
        $result = $this->keeper->proceed($this->fileinput);
        $this->assertSame($result['success'], false);
        $this->assertSame($result['message'], 'File size 183694 is too small or big. Min 204800, max 1048576 bytes allowed.');
        $this->keeper->minFileSize = 10240;
    }
}
