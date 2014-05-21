<?php

namespace deanar\fileProcessor\controllers;

use deanar\fileProcessor\models\Uploads;
use \Yii;
use yii\helpers\VarDumper;

class FileAPI
{
    const OK = 200;
    const ERROR = 500;


    private static function rRestructuringFilesArray(&$arrayForFill, $currentKey, $currentMixedValue, $fileDescriptionParam)
    {
        if (is_array($currentMixedValue)) {
            foreach ($currentMixedValue as $nameKey => $mixedValue) {
                self::rRestructuringFilesArray($arrayForFill[$currentKey],
                    $nameKey,
                    $mixedValue,
                    $fileDescriptionParam);
            }
        } else {
            $arrayForFill[$currentKey][$fileDescriptionParam] = $currentMixedValue;
        }
    }


    private static function determineMimeType(&$file)
    {
        if (function_exists('mime_content_type')) {
            if (isset($file['tmp_name']) && is_string($file['tmp_name'])) {
                if ($file['type'] == 'application/octet-stream') {
                    $mime = mime_content_type($file['tmp_name']);
                    if (!empty($mime)) {
                        $file['type'] = $mime;
                    }
                }
            } else if (is_array($file)) {
                foreach ($file as &$entry) {
                    self::determineMimeType($entry);
                }
            }
        }
    }


    /**
     * Enable CORS -- http://enable-cors.org/
     * @param array [$options]
     */
    public static function enableCORS($options = null)
    {
        if (is_null($options)) {
            $options = array();
        }

        if (!isset($options['origin'])) {
            $options['origin'] = $_SERVER['HTTP_ORIGIN'];
        }

        if (!isset($options['methods'])) {
            $options['methods'] = 'POST, GET';
        }

        if (!isset($options['headers'])) {
            $options['headers'] = array();
        }

        header('Access-Control-Allow-Origin: ' . $options['origin']);
        header('Access-Control-Allow-Methods: ' . $options['methods']);
        header('Access-Control-Allow-Headers: ' . implode(', ', array_merge($options['headers'], array('X-Requested-With', 'Content-Range', 'Content-Disposition'))));

        if (!isset($options['cookie']) || $options['cookie']) {
            header('Access-Control-Allow-Credentials: true');
        }
    }


    /**
     * Request header
     * @return array
     */
    public static function getRequestHeaders()
    {
        $headers = array();

        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == 'HTTP_') {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }

        return $headers;
    }


    /**
     * Retrieve File List
     * @return array
     */
    public static function getFiles()
    {
        $files = array();

        // http://www.php.net/manual/ru/reserved.variables.files.php#106558
        foreach ($_FILES as $firstNameKey => $arFileDescriptions) {
            foreach ($arFileDescriptions as $fileDescriptionParam => $mixedValue) {
                self::rRestructuringFilesArray($files, $firstNameKey, $_FILES[$firstNameKey][$fileDescriptionParam], $fileDescriptionParam);
            }
        }

        self::determineMimeType($files);

        return $files;
    }


    /**
     * Make server response
     * @param array $res
     * @param string [$jsonp]
     */
    public static function makeResponse(array $res, $jsonp = null)
    {
        $body = $res['body'];
        $json = is_array($body) ? json_encode($body) : $body;

        $httpStatus = isset($res['status']) ? $res['status'] : self::OK;
        $httpStatusText = addslashes(isset($res['statusText']) ? $res['statusText'] : 'OK');
        $httpHeaders = isset($res['headers']) ? $res['headers'] : array();

        if (empty($jsonp)) {
            header("HTTP/1.1 $httpStatus $httpStatusText");
            $httpHeaders['Content-Type'] = 'application/json';
            foreach ($httpHeaders as $header => $value) {
                header("$header: $value");
            }
            echo $json;
        } else {
            $json = addslashes($json);

            echo <<<END
					<script>
					(function (ctx, jsonp){
						'use strict';
						var status = $httpStatus, statusText = "$httpStatusText", response = "$json";
						try {
							ctx[jsonp](status, statusText, response);
						} catch (e){
							var data = "{\"id\":\"$jsonp\",\"status\":"+status+",\"statusText\":\""+statusText+"\",\"response\":\""+response.replace(/\"/g, '\\\\\"')+"\"}";
							try {
								ctx.postMessage(data, document.referrer);
							} catch (e){}
						}
					})(window.parent, '$jsonp');
					</script>
END;
        }
    }

}


class BaseController extends \yii\web\Controller
{
    //public $enableCsrfValidation = false;

    public function actionIndex()
    {
        return $this->render('index');
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
            $file_name = $files['tmp_name'];
            $file_real_name = $files['name'];
            list($mime) = explode(';', @mime_content_type($file_name));

            if (strpos($mime, 'image') !== false) {
                $file_size = getimagesize($file_name);
            } else {
                $file_size = [null, null];
            }

            // content
            $file_content = file_get_contents($file_name);

            // insert into db
            $model = new Uploads();
            $model->type = $type;
            $model->type_id = $type_id;
            $model->hash = $hash;
            $model->ord = Uploads::getMaxOrderValue($type, $type_id) + 1; // TODO append to end
            $model->filename = basename($file_real_name); //TODO transliterate plus remove spaces
            $model->original = basename($file_real_name);
            $model->mime = $mime;
            $model->size = filesize($file_name);
            $model->width = $file_size[0];
            $model->height = $file_size[1];

            // save model, save file and fill response array
            if ($model->save()) {

                $path = Yii::getAlias('@app/web/uploads/' . basename($file_real_name));
                file_put_contents($path, $file_content);

                $base64 = base64_encode($file_content);

                $images[$name] = [
                    'width' => $model->width,
                    'height' => $model->height,
                    'mime' => $model->mime,
                    'size' => $model->size,
                    'dataURL' => 'data:' . $model->mime . ';base64,' . $base64,
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
