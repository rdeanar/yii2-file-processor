<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace deanar\fileProcessor;

use \Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use deanar\fileProcessor\models;
use deanar\fileProcessor\assets\UploadAssets;
use yii\helpers\Url;
use yii\web\JsExpression;


class UploadWidget extends \yii\base\Widget
{
    public $type;
    public $type_id;
    public $hash;

    public $identifier = 'file-processor-item';
    public $uploadUrl = null;
    public $removeUrl = null;
    public $sortUrl = null;

    public $multiple = true;

    public $options = [];

    private $options_allowed = ['autoUpload', 'multiple', 'accept', 'duplicate', 'maxSize', 'maxFiles', 'imageSize'];

    public function init()
    {
        parent::init();
        $this->hash        = rand(111111, 999999);
        $this->uploadUrl   = Url::toRoute('fp/base/upload', true);
        $this->removeUrl   = Url::toRoute('fp/base/remove', true);
        $this->sortUrl     = Url::toRoute('fp/base/sort', true);
        $this->identifier .= '-' . $this->hash;
    }

    private function generateOptionsString(){
        if (empty($this->options)) return '';
        $return = '';

        if(!isset($this->options['multiple'])) $this->options['multiple'] = true;
        if($this->options['multiple'] === false){
            $this->options['maxFiles'] = 1;
            $this->options['multiple'] = true; // hack, because if false you can add many files, but uploaded will be only the last one.
        }
        $this->multiple = $this->options['multiple'];

        foreach($this->options as $option_name => $option_value){
            if( !in_array($option_name, $this->options_allowed)) continue;

            if($option_name == 'maxSize'){
                $option_value = models\Uploads::sizeToBytes($option_value);
            }

            $return .= $option_name . ' : ' . json_encode($option_value) . ',' . PHP_EOL;
        }
        return $return;
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        $asset = UploadAssets::register($this->getView());

        $additionalData = Json::encode(array(
            'type' => $this->type,
            'type_id' => $this->type_id,
            'hash' => $this->hash,
            Yii::$app->request->csrfParam => Yii::$app->request->getCsrfToken(),
        ));

        $alreadyUploadedFiles = Json::encode(models\Uploads::getUploadsStack($this->type, $this->type_id));


        $fileApiInitSettings = <<<EOF
        var FileAPI = {
            debug: true, media: true, staticPath: '$asset->baseUrl', 'url' : '$this->uploadUrl'
        };
EOF;


        $fileApiRun = <<<EOF

        var fp_helper = {
            showErrors: function(file){
                var errors = file.errors;

                if(errors === undefined) return true;

                //console.log('error list:', errors);

                // count and size
                if(errors.maxFiles)  this.raiseError('Can not add file "' + file.name + '". Too much files.');
                if(errors.maxSize)   this.raiseError('Can not add file "' + file.name + '". File bigger that need by ' + this.bytesToSize(errors.maxSize) + '.');

                // min dimension
                if(errors.minWidth)  this.raiseError('Can not add file "' + file.name + '". File thinner than need by ' + errors.minWidth  + ' pixels.');
                if(errors.minHeight) this.raiseError('Can not add file "' + file.name + '". File lower than need by ' + errors.minHeight + ' pixels.');

                // max dimension
                if(errors.maxWidth)  this.raiseError('Can not add file "' + file.name + '". File wider than need by  ' + errors.maxWidth + ' pixels.');
                if(errors.maxHeight) this.raiseError('Can not add file "' + file.name + '". File higher than need by ' + errors.maxHeight + ' pixels.');
            },

            raiseError: function(msg){
                //console.log(msg);
                alert(msg);
            },

            // used decimal, not binary
            bytesToSize: function(bytes) {
               if(bytes == 0) return '0 Byte';
               var k = 1000;
               var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
               var i = Math.floor(Math.log(bytes) / Math.log(k));
               return (bytes / Math.pow(k, i)).toPrecision(3) + ' ' + sizes[i];
            }
        }

        var uploadContainer = $('#$this->identifier');

        uploadContainer.fileapi({

            url : '$this->uploadUrl',
            data: $additionalData,
            {$this->generateOptionsString()}

            // Restores the list of files uploaded earlier.
            files: $alreadyUploadedFiles,

            // Events
            onSelect: function (evt, data){
                var to = setTimeout(function(){
                    $(data.all).each(function(i, file){
                        if( file.\$el === undefined ){
                            fp_helper.showErrors(file);
                        }else{
                            file.\$el.removeClass('js-sort');
                        }
                    });
                }, 300);
            },

            // Remove a file from the upload queue
            onFileRemove: function (evt, file){
                if( !confirm("Are you sure?") ){
                    // Cancel remove
                    evt.preventDefault();
                }
            },

            onFileComplete: function (evt, uiEvt){
                var file = uiEvt.file;
                var images = uiEvt.result.images;

                file.\$el.addClass('js-sort');

                if(images === undefined){
                    alert('Error uploading');
                    uploadContainer.fileapi("remove", file);
                }else{
                    var json = images.filedata;

                    file.data = {
                        id: json.id,
                        type: json.type,
                        type_id:  json.type_id
                    };
                }
            },


            onFileRemoveCompleted: function (evt, file){
                evt.preventDefault();

                file.\$el
                    .attr('disabled', true)
                    .addClass('my_disabled')
                ;

                if( confirm('Delete "'+file.name+'"?') ){
                    $.post('$this->removeUrl', file.data);

                    uploadContainer.fileapi("remove", file);
                    // or so
                    evt.widget.remove(file);
                }else{
                    file.\$el
                        .attr('disabled', false)
                        .removeClass('my_disabled')
                    ;
                }
            },

            elements: {
                ctrl: { upload: '.js-upload' },
                empty: { show: '.b-upload__hint' },
                emptyQueue: { hide: '.js-upload' },
                list: '.js-files',
                file: {
                    tpl: '.js-file-tpl',
                    preview: {
                        el: '.b-thumb__preview',
                        width: 80,
                        height: 80
                    },
                    upload: { show: '.progress-upload', hide: '.b-thumb__rotate' },
                    complete: { hide: '.progress-upload', show: '.b-thumb__del' },
                    progress: '.progress-upload .bar'
                },
                dnd: {
                    el: '.b-upload__dnd',
                    hover: 'b-upload__dnd_hover',
                    fallback: '.b-upload__dnd-not-supported'
                }
            }

        });

        var container = uploadContainer.find('ul').get(0);
        var sort = new Sortable(container, {
          handle: ".b-thumb__preview", // Restricts sort start click/touch to the specified element
          draggable: ".js-sort", // Specifies which items inside the element should be sortable
          ghostClass: "sortable-ghost",
          onUpdate: function (evt){
            var sort = [];

            $('#$this->identifier').find('li').each(function(i,el){
                var filedata_id = $(el).data('id');
                var file_data = $('#$this->identifier').fileapi('_getFile', filedata_id).data;
                if(file_data !== undefined){
                    sort.push(file_data.id);
                }
            });

            $.ajax({
                url: "$this->sortUrl",
                data: { sort: sort },
                type: "POST",
                error: function (data, status, e) {
                    alert("Error while saving order.");
                }
            });

            //var item = evt.item; // the current dragged HTMLElement
            }
        });
EOF;

        $this->getView()->registerJs($fileApiInitSettings);
        $this->getView()->registerJs($fileApiRun);

        return $this->render('upload_widget', array(
            'hash' => $this->hash,

            'identifier' => $this->identifier,
            'uploadUrl' => $this->uploadUrl,
            'multiple' => $this->multiple,
        ));
    }

}
