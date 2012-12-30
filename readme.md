##CActiveAjaxForm

Using CHtml::ajaxSubmitButton with a CActiveForm doesn't really mesh well with ajax validation.
Validation is not performed before submission, and clicking submit straight from a form field
can trip a seperate, simultaneous call to validate alongside submission, creating a race condition
(ex: unique validations will fail if record is saved before validation request is processed.)

The issue is discussed here - http://code.google.com/p/yii/issues/detail?id=2008

Qiang Xue suggests an alternative to CACtiveForm to handle these scenarios, which is what this class does.

Drop it in your components folder and use according to the example below:

    <div class="add_tag pull-left">
        <?php $form=$this->beginWidget('CActiveAjaxForm', array(
            'id'=>'new-tag-form',
            'clientOptions' => array(
                'afterSave' => 'js:function(result, form) {
                                    form.each(function() { this.reset(); });}',
                'onSubmit' => 'js:function() { //some extra stuff to do when form is submitted }',
            ),
            'action' => CHtml::normalizeURL(array('tag/create')),
            'htmlOptions' => array('class' => 'form-inline'),
        )); ?>
            <div class="control-group">
                <?php
      
                    echo $form->labelEx($newTag, 'name',
                                    array('class' => 'control-label', 'label' => 'New Tag: '));
                    echo $form->textField($newTag, 'name');
                    echo $form->error($newTag, 'name');
                    echo CHtml::submitButton('Add Tag');
                ?>
    
            </div>
        <?php $this->endWidget(); ?>
    </div>
