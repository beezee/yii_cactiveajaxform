<?php

class CActiveAjaxForm extends CActiveForm
{
    
    public $assetsBase = false;
    public $assetsUrl = false;
    
    public function __construct($owner=null)
    {
        parent::__construct($owner);
        $this->enableAjaxValidation = true;
    }
    
    public function init()
    {
        if (!$this->assetsBase)
            $this->assetsBase = dirname(__FILE__).DIRECTORY_SEPARATOR.'assets';
        if (!$this->assetsUrl)
            $this->assetsUrl = Yii::App()->assetManager->publish($this->assetsBase);
        parent::init();
    }
    
    public function run()
    {
        if(is_array($this->focus))
                $this->focus="#".CHtml::activeId($this->focus[0],$this->focus[1]);
        echo CHtml::endForm();
        $cs=Yii::app()->clientScript;
        $options=$this->clientOptions;
        if(isset($this->clientOptions['validationUrl']) && is_array($this->clientOptions['validationUrl']))
                $options['validationUrl']=CHtml::normalizeUrl($this->clientOptions['validationUrl']);
        $options['validateOnSubmit'] = true;
        
        $options['attributes']=array_values($this->attributes);
        $afterValidateCB = isset($options['afterValidate'])
            ? preg_replace('/^js\:/', '', $options['afterValidate'], 1)
            : 'function() {return;}';
        $afterSaveCB = isset($options['afterSave'])
            ? preg_replace('/^js\:/', '', $options['afterSave'], 1)
            : 'function(r) { return; }';
        $onSubmitCB = isset($options['onSubmit'])
            ? preg_replace('/^js\:/', '', $options['onSubmit'], 1)
            : 'function(){ return; }';
        $filterPostDataCB = isset($options['filterPostData'])
            ? preg_replace('/^js\:/', '', $options['filterPostData'], 1)
            : 'function(data) { return data; }';
        $overrideSubmit = isset($options['overrideSubmit'])
            ? preg_replace('/^js\:/', '', $options['overrideSubmit'], 1) : 'false';
        $options['afterValidate']
            = "js:function(form, data, hasError){
                    var vCb = $afterValidateCB;
                    var sCb = $afterSaveCB;
                    var fPdCb = $filterPostDataCB;
                    var overrideSubmit = $overrideSubmit;
                    if (hasError)
                        return vCb(form, data, hasError);
                    var postData = fPdCb(form.serializeObject());
                    if (typeof overrideSubmit == 'function')
                        return overrideSubmit(postData);
                    $.post('{$this->action}', postData, function(r){
                        sCb(r, form, data);
                    });
                }";

        if($this->summaryID!==null)
                $options['summaryID']=$this->summaryID;

        if($this->focus!==null)
                $options['focus']=$this->focus;

        $options=CJavaScript::encode($options);
        $cs->registerScriptFile(
            $this->assetsUrl
            .DIRECTORY_SEPARATOR.'js'
            .DIRECTORY_SEPARATOR.'jquery.yiiactiveajaxform.js',
            CClientScript::POS_END
        );
        $id=$this->id;
        $cs->registerScript(__CLASS__.'#'.$id,"\$('#$id').yiiactiveajaxform($options);");
        $cs->registerScript(__CLASS__.'#'.$id.'#submit',
                "$('#$id').submit(function(e) {
                    var osCb = $onSubmitCB;
                    e.preventDefault();
                    osCb();
                });");
    }
}