<?php

namespace asdfstudio\admin;

use yii\web\AssetBundle;

class AdminAsset extends AssetBundle {
    public $sourcePath = '@vendor/tecnoready/yii2-admin-module/tpl';
    public $css        = [
//        'css/bootstrap.min.css',
        'font-awesome/css/font-awesome.css',
        'css/animate.css',
        'css/style.css',
    ];
    public $js         = [
        'js/jquery-2.1.1.js',
//        'js/bootstrap.min.js',
        'js/plugins/metisMenu/jquery.metisMenu.js',
        'js/plugins/slimscroll/jquery.slimscroll.min.js',
        
        'js/inspinia.js',
        'js/plugins/pace/pace.min.js',
    ];
    public $depends    = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'rmrevin\yii\fontawesome\AssetBundle',
        'cakebake\bootstrap\select\BootstrapSelectAsset',
    ];
}
