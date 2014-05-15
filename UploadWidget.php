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


class UploadWidget extends \yii\base\Widget
{
    public $identifier = 'file-processor-item';
    public $uploadUrl = 'http://loqa.dev/rubaxa/ctrl.php';

    public function init()
    {
        parent::init();
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        $asset = UploadAssets::register($this->getView());

        $fileApiInitSettings = <<<EOF
        var FileAPI = {
            debug: true, media: true, staticPath: '$asset->baseUrl', 'url' : '$this->uploadUrl'
        };
EOF;

        $fileApiRun = <<<EOF

        var uploadContainer = $('#$this->identifier');

        uploadContainer.fileapi({

            'url' : '$this->uploadUrl',

            // Restores the list of files uploaded earlier.
            files: [{
                src: "http://loqa.dev/rubaxa/valuetest1.jpg",
                type: "image/jpeg",
                name: "valuetest1.jpg",
                size: 31409,
                data: {
                    id: 999,
                    type: "projects"
                }
            }],

            // Remove a file from the upload queue
            onFileRemove: function (evt, file){
                if( !confirm("Are you sure?") ){  //   + file.data.id + ' ' + file.data.type
                    // Cancel remove
                    evt.preventDefault();
                }
            },

            onFileComplete: function (evt, uiEvt){
                //console.log(evt, uiEvt);

                var file = uiEvt.file;
                var json = uiEvt.result.images.filedata;

                file.data = {
                    id: json.id,
                    type: json.type
                };
            },

            onFileRemoveCompleted: function (evt, file){
                evt.preventDefault();

                file.\$el
                    .attr('disabled', true)
                    .addClass('my_disabled')
                ;

                if( confirm('Delete "'+file.name+'"?' + file.data.id + ' ' + file.data.type) ){
                    $.post('/api/remove', file.data);

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


            data: {
                lala: 'testlalalala'
            },

            multiple: true,
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

EOF;


        $this->getView()->registerJs($fileApiInitSettings);
        $this->getView()->registerJs($fileApiRun);


        return $this->render('upload_widget', array(
            'identifier' => $this->identifier,
            'uploadUrl' => $this->uploadUrl,
        ));
    }

}
