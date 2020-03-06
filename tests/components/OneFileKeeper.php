<?php

namespace tests\components;

use tests\models\OneFile;

/**
 * Class for testing \sergmoro1\uploader\components\OneFileKeeper.
 *
 * @author Sergey Morozov <sergmoro1@ya.ru>
 */
class OneFileKeeper extends \sergmoro1\uploader\components\OneFileKeeper {
    /**
     * @inheritdoc
     */
    public function move($source, $dest)
    {
        return copy($source, $dest);
    }

    /**
     * @inheritdoc
     */
    public function sculpt($config)
    {
        return new OneFile($config);
    }
}
