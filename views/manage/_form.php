<?php

use yii\bootstrap\ActiveForm;

/**
 * @var yii\web\View $this
 * @var yii\db\ActiveRecord $model
 * @var asdfstudio\admin\forms\Form $form
 * @var asdfstudio\admin\base\Admin $entity
 * @var ActiveForm $htmlForm
 */

$fields = $form->fields();
$actions = $form->actions();
?>

<div class="model-form row">
    <?php $htmlForm = ActiveForm::begin([
        'options' => $form->options,
    ])?>
    <div class="col-md-10">
        <!-- Render fields -->
        <?php foreach($fields as $attribute => $field):?>
            <?php
            if (!$field || isset($field['visible']) && !$field['visible']) {
                continue;
            }
            $options = [];
            if (isset($field['fieldOptions'])) {
                $options = $field['fieldOptions'];
                unset($field['fieldOptions']);
            }
            ?>
            <?php echo $htmlForm->field($model, $attribute, $options)->widget($field['class'], $field)?>
        <?php endforeach?>
    </div>
    <div class="col-md-2">
        <!-- Render actions -->
        <?php foreach($actions as $name => $action):?>
            <?php if (isset($action['visible']) && !$action['visible']) continue?>
            <?php echo $action['class']::widget(array_merge($action, ['name' => $name]))?>
        <?php endforeach?>
    </div>
    <?php ActiveForm::end()?>
</div>
