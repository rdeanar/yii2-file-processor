<?php

namespace deanar\fileProcessor\controllers;


use deanar\fileProcessor\models\Uploads;
use \Yii;
use yii\helpers\VarDumper;
use dosamigos\transliterator\TransliteratorHelper;
use deanar\fileProcessor\Module;
use deanar\fileProcessor\vendor\FileAPI;

// only for tests
use app\models\Project;


class BaseController extends \yii\web\Controller
{
    //public $enableCsrfValidation = false;

    public function actionIndex()
    {

        $model = Project::findOne(11);
        $uploads = $model->getFiles();

        foreach($uploads as $u){
            /**
             * @var $u Uploads
             */
            echo $u->id.$u->imgTag('preview', true,['style'=>'border:1px solid red;']);
//            echo $u->getPublicFileUrl('thumb2', true);
        }

        //echo $model->id;

        //return VarDumper::dumpAsString($model);
        //return $model->id.'--';
/*

        $model->setProp2('SSSSS');

        return $model->foo();
        return $model->getProp2();
*/
        /*
        $op = new MimeTypeExtensions();
        //var_dump($op->get_type_by_ext("txt"));
        var_dump($op->get_ext_by_type("application/octet-stream"));


        //return uniqid('sdasdasdas',false);
        return VarDumper::dumpAsString( Uploads::loadVariationsConfig('projects') );
        //return VarDumper::dumpAsString($this->module->variations_config);

        return $this->render('index');
        */
    }

    public function actionRemove(){

        // TODO check for POST request

        $id = Yii::$app->request->post('id');
        $type = Yii::$app->request->post('type');
        $type_id = Yii::$app->request->post('type_id');

        $success = Uploads::staticRemoveFile($id, compact('type','type_id'));

        if($success){
            return 'File with id: '.$id.' removed successfully';
        }else{
            return 'Fail to remove file with id: '.$id;
        }
    }

    public function actionUpload()
    {
        //Yii::$app->getRequest()->enableCsrfValidation = false;

        if (!empty($_SERVER['HTTP_ORIGIN'])) {
            // Enable CORS
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Range, Content-Disposition, Content-Type');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit;
        }

        if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
            $files = FileAPI::getFiles(); // Retrieve File List
            $images = array();

            // Fetch all image-info from files list
            $this->fetchFiles($files, $images);

            // JSONP callback name
            $jsonp = isset($_REQUEST['callback']) ? trim($_REQUEST['callback']) : null;

            // JSON-data for server response
            $json = array(
                'images' => $images
            , 'data' => array('_REQUEST' => $_REQUEST, '_FILES' => $files)
            );

            // Server response: "HTTP/1.1 200 OK"
            FileAPI::makeResponse(array(
                'status' => FileAPI::OK
            , 'statusText' => 'OK'
            , 'body' => $json
            ), $jsonp);
            exit;
        }

    } // end of actionUpload

    private function fetchFiles($files, &$images, $name = 'file')
    {

        if (isset($files['tmp_name'])) {
            // system info
            $type = Yii::$app->request->post('type');
            $type_id = Yii::$app->request->post('type_id');
            $hash = Yii::$app->request->post('hash');

            // file info
            $file_temp_name = $files['tmp_name'];
            $file_real_name = basename($files['name']);
            $file_name = str_replace(' ', $this->module->space_replacement, TransliteratorHelper::process($file_real_name, '', 'en'));

            list($mime) = explode(';', @mime_content_type($file_temp_name));

            if (strpos($mime, 'image') !== false) {
                $file_dimensions = getimagesize($file_temp_name);
            } else {
                $file_dimensions = [null, null];
            }

            // content
            $file_content = file_get_contents($file_temp_name);

            // insert into db
            $model = new Uploads();
            $model->type = $type;
            $model->type_id = $type_id;
            $model->hash = $hash;

            // load configuration
            $config = Uploads::loadVariationsConfig($model->type);


            $model->ord = Uploads::getMaxOrderValue($type, $type_id) + 1; // TODO append to end
            $model->filename = Uploads::generateBaseFileName($file_name); //$file_name;
            $model->original = $file_real_name;
            $model->mime = $mime;
            $model->size = filesize($file_temp_name);
            $model->width = $file_dimensions[0];
            $model->height = $file_dimensions[1];

            // save model, save file and fill response array
            if ($model->save()) {

                // upload and process variations
                $model->process($file_temp_name, $config);


                //$base64 = base64_encode($file_content);

                $images[$name] = [
                    'width' => $model->width,
                    'height' => $model->height,
                    'mime' => $model->mime,
                    'size' => $model->size,
                    //'dataURL' => 'data:' . $model->mime . ';base64,' . $base64,
                    'id' => $model->id,
                    'type' => $model->type,
                    'type_id' => $model->type_id,
                    'hash' => $model->hash,
                    'errors' => null,
                ];

            }else{
                VarDumper::dumpAsString($model->getErrors());
                Yii::$app->end();
            }

        } else {
            foreach ($files as $name => $file) {
                $this->fetchFiles($file, $images, $name);
            }
        }

    }
}
