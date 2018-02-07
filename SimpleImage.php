<?php
/* 
 * Created by Khanh Nam
 * Modified: resize, resizeSave, crop by Sergey Morozov <sergmoro1@ya.ru>
 */

namespace sergmoro1\uploader;

class SimpleImage {
   
    var $image;
    var $image_type;
    var $image_info;
    
    function __construct($filename) 
    {
        $this->image_info = $image_info = getimagesize($filename);
        $this->image_type = $image_info[2];
        if( $this->image_type == IMAGETYPE_JPEG ) 
        {
            $this->image = imagecreatefromjpeg($filename);
        } elseif( $this->image_type == IMAGETYPE_GIF ) 
        {
            $this->image = imagecreatefromgif($filename);
        } elseif( $this->image_type == IMAGETYPE_PNG ) 
        {
         $this->image = imagecreatefrompng($filename);
        }
    }
   
    function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) 
    {
        if( $image_type == IMAGETYPE_JPEG ) 
        {
            imagejpeg($this->image,$filename,$compression);
        } elseif( $image_type == IMAGETYPE_GIF ) 
        {
            imagegif($this->image,$filename);         
        } elseif( $image_type == IMAGETYPE_PNG ) 
        {
            imagepng($this->image,$filename);
        }   
        if($permissions != null) 
        {
            chmod($filename,$permissions);
        }
    }
    
    function resizeSave($path, $file, $params=array(
        'main'=>array('width'=>480, 'height'=>320, 'catalog'=>''),
        'thumb'=>array('width'=>120, 'height'=>80, 'catalog'=>'thumb'),
    ))
    {
        $wGTh = $this->getWidth() - $this->getHeight();
        
        foreach($params as $place => $param)
        {
            $square = $param['width'] == $param['height'];
            if($param['width']>0 && $param['height']>0)
            {
                if($wGTh>0)
                {
                    if($square)
                        $this->resizeToHeight($param['height'], true);
                    else
                        $this->resizeToWidth($param['width']);
                } else {
                    if($square)
                        $this->resizeToWidth($param['width'], true);
                    else
                        $this->resizeToHeight($param['height']);
                }
            }
            if($param['catalog'] && !is_dir($path . $param['catalog']))
                mkdir($path . $param['catalog'], 0777);
            $this->save($path . ($param['catalog'] ? $param['catalog'] . '/' : '') . $file);
        }
    }
    
    function output($image_type=IMAGETYPE_JPEG) 
    {
        if( $image_type == IMAGETYPE_JPEG ) 
        {
            imagejpeg($this->image);
        } elseif( $image_type == IMAGETYPE_GIF ) 
        {
            imagegif($this->image);         
        } elseif( $image_type == IMAGETYPE_PNG ) 
        {
            imagepng($this->image);
        }   
    }
   
    function getWidth() 
    {
        return imagesx($this->image);
    }
   
    function getHeight() 
    {
        return imagesy($this->image);
    }
    
    function resizeToHeight($height, $square = false) 
    {
        if($height<$this->getHeight())
        {
            $ratio = $height / $this->getHeight();
            $width = $this->getWidth() * $ratio;
            if($square)
                $this->resize($height, $height, 0, 0, floor(($this->getWidth() - $this->getHeight())/2), 0);
            else
                $this->resize($width, $height);
        }
    }
   
    function resizeToWidth($width, $square = false) 
    {
        if($width<$this->getWidth())
        {
            $ratio = $width / $this->getWidth();
            $height = $this->getheight() * $ratio;
            if($square)
                $this->resize($width, $width, 0, 0, 0, floor(($this->getHeight() - $this->getWidth())/2));
            else
                $this->resize($width, $height);
        }
    }
   
    function scale($scale) 
    {
        $width = $this->getWidth() * $scale/100;
        $height = $this->getheight() * $scale/100; 
        $this->resize($width,$height);
    }
   
    function resize($width , $height, $dst_x = 0, $dst_y = 0, $src_x = 0, $src_y = 0) 
    {
        $new_image = imagecreatetruecolor($width, $height);
        imagecopyresampled($new_image, $this->image, 
            $dst_x, $dst_y, $src_x, $src_y, 
            $width, $height, 
            $this->getWidth() - ($src_x * 2), $this->getHeight() - ($src_y * 2));
        $this->image = $new_image;   
    }

    function crop($width ,$height, $src_x, $src_y) 
    {
        $new_image = imagecreatetruecolor($width, $height);
        imagecopyresampled($new_image, $this->image, 0, 0, $src_x, $src_y, $width, $height, $width, $height);
        $this->image = $new_image;   
    }
}
?>
