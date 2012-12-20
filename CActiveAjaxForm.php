<?php

class CActiveAjaxForm extends CActiveForm
{
    
    public function __construct($owner=null)
    {
        parent::__construct($owner);
        $this->enableAjaxValidation = true;
    }
    
    public function run()
    {
        if(is_array($this->focus))
                $this->focus="#".CHtml::activeId($this->focus[0],$this->focus[1]);
        echo CHtml::endForm();
        $cs=Yii::app()->clientScript;
        if(!$this->enableAjaxValidation && !$this->enableClientValidation || empty($this->attributes))
        {
                if($this->focus!==null)
                {
                        $cs->registerCoreScript('jquery');
                        $cs->registerScript('CActiveForm#focus',"
                                if(!window.location.hash)
                                        $('".$this->focus."').focus();
                        ");
                }
                return;
        }

        $options=$this->clientOptions;
        if(isset($this->clientOptions['validationUrl']) && is_array($this->clientOptions['validationUrl']))
                $options['validationUrl']=CHtml::normalizeUrl($this->clientOptions['validationUrl']);
        $options['validateOnSubmit'] = true;
        
        $options['attributes']=array_values($this->attributes);
        $afterValidateCB = isset($options['afterValidate'])
            ? preg_replace('/^js\:/', '', $options['afterValidate'], 1) : 'function() {return;}';
        $afterSaveCB = isset($options['afterSave'])
            ? preg_replace('/^js\:/', '', $options['afterSave'], 1) : 'function(r) { return; }';
        $options['afterValidate']
            = "js:function(form, data, hasError){
                    var vCb = $afterValidateCB;
                    var sCb = $afterSaveCB;
                    if (hasError)
                        return vCb(form, data, hasError);
                    console.log(data);
                    $.post('{$this->action}', form.serialize(), function(r){
                        sCb(r, form, data);
                    });
                    }";

        if($this->summaryID!==null)
                $options['summaryID']=$this->summaryID;

        if($this->focus!==null)
                $options['focus']=$this->focus;

        $options=CJavaScript::encode($options);
        $cs->registerCoreScript('yiiactiveform');
        $id=$this->id;
        $cs->registerScript(__CLASS__.'#'.$id,"\$('#$id').yiiactiveform($options);");
        $cs->registerScript(__CLASS__.'#'.$id.'#submit',
                "$('#$id').submit(function(e) { e.preventDefault(); });");
    }
}