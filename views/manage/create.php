<?php

/**
 * @var yii\web\View $this
 * @var yii\db\ActiveRecord $model
 * @var asdfstudio\admin\base\Admin $entity
 * @var \asdfstudio\admin\forms\Form $form
 */

$this->title = $entity->labels()[0];
$this->params['breadcrumbs'][] = ['label' => $entity->labels()[1], 'url' => ['index', 'entity' => $entity->id]];
$this->params['breadcrumbs'][] = Yii::t('admin', 'Creating');
?>
<div class="model-create">

    <?= $this->render($form->viewFile ? $form->viewFile : '_form', [
        'model' => $model,
        'entity' => $entity,
        'form' => $form,
    ]) ?>

</div>
