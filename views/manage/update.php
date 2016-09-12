<?php

/**
 * @var yii\web\View $this
 * @var yii\db\ActiveRecord $model
 * @var asdfstudio\admin\base\Admin $entity
 * @var \asdfstudio\admin\forms\Form $form
 */

$this->title = $entity->labels()[0];
$this->params['breadcrumbs'][] = ['label' => $entity->labels()[1], 'url' => ['index', 'entity' => $entity->id]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('admin', 'View'), 'url' => ['view', 'entity' => $entity->id, 'id' => $model->primaryKey]];
$this->params['breadcrumbs'][] = Yii::t('admin', 'Editing');
?>
<div class="model-update">

    <?= $this->render($form->viewFile ? $form->viewFile : '_form', [
        'model' => $model,
        'entity' => $entity,
        'form' => $form,
    ]) ?>

</div>
