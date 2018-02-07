<?php
/**
 * @author - Sergey Morozov <sergmoro1@ya.ru>
 * @license - GPL
 
 * @var $file - oneFile model record
 * 
 */
?>

<span id='when'>
    <?= isset($file->vars->when) ? ($file->vars->when ? 'after' : 'before') : 'before' ?>
</span>

<span id='description'>
    <?= isset($file->vars->description) ? $file->vars->description : '' ?>
</span>
