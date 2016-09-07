<?php

namespace asdfstudio\admin;

use yii\web\AssetBundle;

class AdminAsset extends AssetBundle {
    public $sourcePath = '@vendor/tecnoready/yii2-admin-module/assets';
    public $css        = [
        'css/sb-admin.css',
    ];
    public $js         = [
        'js/admin.js',
        'js/form.js',
    ];
    public $depends    = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'rmrevin\yii\fontawesome\AssetBundle',
        'cakebake\bootstrap\select\BootstrapSelectAsset',
    ];
}
