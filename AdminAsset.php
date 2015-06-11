<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace asdfstudio\admin;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AdminAsset extends AssetBundle
{
    public $sourcePath = '@vendor/sgdot/yii2-admin-module/assets';
    public $css = [
        'css/sb-admin.css',
    ];
    public $js = [
        'js/admin.js',
        'js/form.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'rmrevin\yii\fontawesome\AssetBundle',
	    'cakebake\bootstrap\select\BootstrapSelectAsset',
    ];
}
