<?php

use yii\helpers\Html;
use yii\bootstrap\Modal;

/**
 * Input field for files uploading, cropping popup area,
 * results of uploading with buttons.
 * 
 * @author Sergey Morozov <sergmoro1@ya.ru>
 */
?>

<?php if(!$model->id): ?>
    <label class='control-label'>
        <?= $btns['choose']['label']; ?>
    </label>
    <p><?= \Yii::t('core', 'After saving you will be able to upload images or other files.') ?></p>
<?php else: ?>

    <?php Modal::begin([
        'options' => ['id' => 'preview'],
        'header' => Yii::t('core', 'View'),
        'size' => ($model->sizes['main']['width'] > 768 ? Modal::SIZE_LARGE : Modal::SIZE_DEFAULT),
        'toggleButton' => false,
        'footer' => (
            Html::tag('button', Yii::t('core', 'Crop'), ['class' => 'btn btn-success btn-crop', 'data-dismiss' => 'modal']) .
            Html::tag('button', Yii::t('core', 'Cancel'), ['class' => 'btn btn-default btn-cancel', 'data-dismiss' => 'modal'])
        ),
    ]); ?>
        <!-- Place for image -->
        <img id='cropBox' class='img-responsive center-block' />
        <?php if($cropAllowed): ?>
            <!-- Variables for saving of cropping coordinates - http://deepliquid.com/content/Jcrop.html -->
            <div id='coords' class='coords'>
                <input type='hidden' size='4' id='x'  name='x'>
                <input type='hidden' size='4' id='y'  name='y'>
                <input type='hidden' size='4' id='x2' name='x2'>
                <input type='hidden' size='4' id='y2' name='y2'>
                <input type='hidden' size='4' id='w'  name='w'>
                <input type='hidden' size='4' id='h'  name='h'>
            </div>
        <?php endif; ?>
    <?php Modal::end(); ?>

    <!-- Input button -->
    <div class='container'>
        <div class="row">
            <?php if(isset($btns['choose']['label'])): ?>
                <label class="control-label" for='<?= $fileinput ?>'>
                    <?= $btns['choose']['label']; ?>
                </label>
            <?php endif; ?>
            <div class='controls'>
                <!-- upload field -->
                <span class='<?= $btns['choose']['class']; ?> fileinput-button'>
                    <?= $btns['choose']['caption']; ?>
                    <input type='file' name='<?= $fileinput ?>' multiple>
                </span>
            </div>
        </div>
    </div>

    <!-- Uploaded files -->
    <div class='col-sm-12'>
        <div class='row'>
            <!-- Container for the uploaded files -->
            <div id='uploads'>
                <?php if($draggable): ?>
                    <div class='text-right'>
                        <?= Yii::t('core', 'Sort') ?>
                        <a href='#' title='<?= \Yii::t('core', 'Rows can be sorted - click, hold, drag.') ?>'>
                            <span class='glyphicon glyphicon-question-sign'></span>
                        </a>
                    </div>
                <?php endif; ?>
                <ul class='table' <?= $draggable ? 'id="sortable"' : '' ?>>
                <!-- table with files/images already uploaded at the time the form load -->
                <?php foreach($model->$files as $i => $file): ?>
                    <li id='<?php echo $file->id; ?>'>
                        <?php if($model->isImage($file->type)): ?>
                            <!-- image -->
                            <span class='block'>
                                <?= Html::img($model->getImage('thumb', $i), ['data-img' => $model->getImage('', $i)]); ?>
                                <div class='img-tool'>
                                    <?php if ($cropAllowed): ?>
                                        <?= Html::a($btns['crop']['caption'], false, [
                                            'id'    => ('btn-crop'), 
                                            'class' => $btns['crop']['class'],
                                            'title' => \Yii::t('core', 'Crop'),
                                        ]) ?>
                                    <?php endif; ?>
                                    <?= Html::a($btns['view']['caption'], false, [
                                        'id'    => ('btn-view'), 
                                        'class' => $btns['view']['class'],
                                        'title' => \Yii::t('core', 'View'),
                                    ]) ?>
                                </div>
                            </span>
                        <?php else: ?>
                            <!-- file extension -->
                            <span class='block'>
                                <span class='extension'>
                                    <?= substr($file->name, strrpos($file->name, '.')); ?>
                                </span>
                            </span>
                        <?php endif; ?>
                        <!-- additional fields -->
                        <?php if($appendixView)
                            echo $this->render($appendixView, [
                                'file' => $file,
                            ]); 
                        ?>
                        <!-- buttons -->
                        <span id='buttons'>
                            <?php if($appendixView): ?>
                                <?php foreach(['save' => 0, 'cancel' => 0, 'edit' => 1,'delete' => 1] as $btn => $visible): ?>
                                    <?= Html::a($btns[$btn]['caption'], false, [
                                        'id'    => ('btn-' . $btn), 
                                        'class' => ($btns[$btn]['class'] . ($visible ? '' : ' hidden')),
                                        'title' => \Yii::t('core', $btn),
                                    ]) ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </span>
                    </li> <!-- / row #id -->
                <?php endforeach;?>
                </ul>
            </div>  <!-- / uploads -->
        </div> <!-- / .row -->
    </div>
<?php endif; ?>
