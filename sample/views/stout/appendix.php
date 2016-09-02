<?php
/**
 * @author - Sergey Morozov <sergmoro1@ya.ru>
 * @license - GPL
 
 * @var $file - oneFile model record
 * 
 */
?>

<td id='when'>
	<?= isset($file->vars->when) ? ($file->vars->when ? 'after' : 'before') : 'before' ?>
</td>

<td id='description'>
	<?= isset($file->vars->description) ? $file->vars->description : '' ?>
</td>
