<?php
/**
 * @link https://github.com/sergmoro1/yii2-uploader
 * @copyright Copyright (c) 2020 Sergey Morozov
 * @license MIT
 */

namespace tests;

use tests\models\Photo;

class HaveFileBehaviorTest extends \PHPUnit\Framework\TestCase
{
    protected $subdir = '';
    protected $model;
    
    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->model = new Photo(['id' => 1, 'category' => 'Street']);
    }

    /**
     * @covers behaviours\HaveFileBehavior::getFileCount
     */
    public function testCheckFileCount()
    {
        $this->assertSame($this->model->getFileCount(), 2);
    }

    /**
     * @covers behaviours\HaveFileBehavior::getImage
     */
    public function testGetImage()
    {
        $this->assertSame($this->model->getImage(), $this->model->getFilePath($this->subdir) . '123.jpg');
    }

    /**
     * @covers behaviours\HaveFileBehavior::getDoc
     */
    public function testGetDoc()
    {
        $this->assertSame($this->model->getDoc(1), $this->model->getFilePath($this->subdir) . '234.docx');
    }

    /**
     * @covers behaviours\HaveFileBehavior::getFileDescription
     * @covers behaviours\HaveFileBehavior::getNextImage
     */
    public function testGetDescription()
    {
        $image = $this->model->getImage();
        $this->assertSame($this->model->getFileDescription(), 'park');
        $image = $this->model->getNextImage();
        $this->assertSame($this->model->getFileDescription(), 'document');
    }

    /**
     * @covers behaviours\HaveFileBehavior::getMin
     */
    public function testGetMinWidth()
    {
        $this->assertSame($this->model->getMin(), 600);
    }

    /**
     * @covers behaviours\HaveFileBehavior::getAspectRatio
     */
    public function testGetAspectRation()
    {
        $this->assertSame(round($this->model->getAspectRatio(), 2), 1.33);
    }

    /**
     * @covers behaviours\HaveFileBehavior::prepareSlider
     */
    public function testPrepareSlider()
    {
        $slider = $this->model->prepareSlider(false, [], [], true);
        $this->assertSame(count($slider), 1);
        $this->assertSame($slider[0]['content'], '<img src="/data/files/photo/123.jpg" alt="">');
        $this->assertSame($slider[0]['caption'], 'park');
    }
}
