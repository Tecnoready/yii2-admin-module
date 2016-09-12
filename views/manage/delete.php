<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/**
 * @var yii\web\View $this
 * @var yii\db\ActiveRecord $model
 * @var asdfstudio\admin\base\Admin $entity
 */

$this->title = $entity->labels()[0];
$this->params['breadcrumbs'][] = ['label' => $entity->labels()[1], 'url' => ['index', 'item' => $entity->id]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('admin', 'View'), 'url' => ['view', 'entity' => $entity->id, 'id' => $model->primaryKey]];
$this->params['breadcrumbs'][] = Yii::t('admin', 'Deleting');
?>
<div class="model-delete">

    <div class="driver-form">

        <?php $form = ActiveForm::begin(); ?>

        <h3><?php echo Yii::t('admin', 'Are you sure you want to delete this item?')?></h3>

        <?php echo Html::submitButton(Yii::t('admin', 'Delete'), [
            'class' => 'btn btn-danger'
        ])?>
        <?php echo Html::a(Yii::t('admin', 'Cancel'), [
            'view',
            'entity' => $entity->id,
            'id' => $model->primaryKey,
        ], [
            'class' => 'btn btn-primary'
        ])?>

        <?php ActiveForm::end(); ?>

    </div>

</div>
