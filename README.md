# File Processor (Yii2 Extension)

Upload and process files and images.

Based on jquery.fileapi [Link to github](https://github.com/RubaXa/jquery.fileapi)

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist deanar/yii2-file-processor:0.1.*
```

or add

```
"deanar/yii2-file-processor": "0.1.*"
```

to the require section of your `composer.json` file and update composer dependencies;

If installation fails, try to use minimum stability: dev.

Then run migrations

```bash
 ./yii migrate/up --migrationPath=@deanar/fileProcessor/migrations
```

Include module into your web config

```php
'modules' => [
    'fp' => [
        'class' => 'deanar\fileProcessor\Module',
        //'image_driver' => \deanar\fileProcessor\models\Uploads::IMAGE_DRIVER_GD,
        'variations_config' => require(__DIR__ . '/file_processor_variations.php'),
        //'root_path' => '@frontend/web', // default: @webroot
        //'root_url' => 'http://front.example.com', // default: current host (Yii::$app->request->getHostInfo()) 
        'upload_dir' => 'uploads',
        //'default_quality' => 95,
        //'default_resize_mod' => 'outbound',
        //'unlink_files' => true,
        //'debug' => true, // FileAPI debug. false by default
    ],
]
```

Attach behavior to your model

```php
public function behaviors()
{
    return [
        'fileSequence' => [
            'class' => \deanar\fileProcessor\behaviours\ConnectFileSequence::className(),
            'defaultType' => 'projects',
            'registeredTypes' => ['projects', 'files'], // or 'projects, files' as string
        ]
    ];
}
```

Create file `file_processor_variations.php` in config directory and configure image variations like:

```php
use deanar\fileProcessor\components\WatermarkFilter;

return [
    'projects' => [
        '_original' => false,
        'thumb' => [200, 150, 'inset'],
        'small' => [300, 200, 'outbound', 75],
        'big' => [
            'width' => 600,
            'height' => 350,
            'mode' => 'outbound',
            'quality' => 75,
            'watermark' => [
                'path' => 'watermark.png',
                'position' => WatermarkFilter::WM_POSITION_BOTTOM_RIGHT,
                'margin' => 10,
            ]
        ],
    ],
    'article_header' => [
        '_original' => true,
        'thumb' => [200, 150, 'inset'],
    ],
    'avatar_picture' => [
        '_original' => true,
        'preview' => [200, 200, 'outbound'],
        
        // For single file uploads. Automatically will be updated 'avatar' attribute in 'Project' model
        // with <id> of currently uploaded file
        '_insert' => ['app\models\Project' => 'avatar'],
        
        // variants of access control definitions          
        '_acl'       => '*', // * - all users, like without _acl
        '_acl'       => '@', // @ - authenticated users only
        '_acl'       => ['users' => ['admin', 'user1']], // defined list of users
        '_acl'       => ['app\models\Project' => 'user_id'], // if current user id equals to `user_id` attribute of model `app\models\Project`
        '_acl'       => function ($type_id, $user_id) { // callable check
            return \app\models\Project::findOne($type_id)->user_id == $user_id;
        },
          
    ],
    
    // Used if no variation with specified name found
    '_default' => [ ],
     
    // Mixin for all variations. Used by merging arrays.
    '_all' => [ ],
];
```

**NB!** Don't forget to disable php execution in your upload dir.
For example: If you use Apache web server, you can create `.htaccess` file in the root of upload directory with the following code inside:

```
RemoveHandler .php
AddType text/html .php
```

## Upgrade instruction

Run migrations

```bash
 ./yii migrate/up --migrationPath=@deanar/fileProcessor/migrations
```

In ConnectFileSequence behaviour replace `deleteTypes` property to `registeredTypes`. 

## Usage

Once the extension is installed, simply use it in your form by adding widget code to view:

Multi upload widget:

```php
<?= \deanar\fileProcessor\widgets\MultiUploadWidget::widget([
    'type' => 'projects',
    'type_id' => $model->id,

    'options' => [
        'autoUpload' => true,
        'multiple' => true,
        'accept' => 'image/*,application/zip',
        'duplicate' => false,
        'maxSize' => '2M', // you can use 'M', 'K', 'G' or simple size in bytes
        'maxFiles' => 3,
        'imageSize' => [
            'minWidth' => 150,
            'maxWidth' => 2000,
            'minHeight' => 150,
            'maxHeight' => 2000,
        ],
    ],
    
    'htmlOptions' => [
        'class'          => 'additional-class',
        'data-attribute' => 'value',
    ],

]) ?>
```

Single upload widget:

```php
<?= \deanar\fileProcessor\widgets\SingleUploadWidget::widget([
    'type' => 'projects',
    'type_id' => $model->id,

    'crop' => true,
    'preview' => true,
    'previewSize' => [200,200],

    'options' => [
        'accept' => 'image/*',
        'maxSize' => '2M', // you can use 'M', 'K', 'G' or simple size in bytes
        'imageSize' => [
            'minWidth' => 150,
            'maxWidth' => 2000,
            'minHeight' => 150,
            'maxHeight' => 2000,
        ],
    ],

    'htmlOptions' => [
        'class'          => 'additional-class',
        'data-attribute' => 'value',
    ],

]) ?>
```

If `preview` is set to `false`, `crop` automatically set to `false` and will be very simple upload widget.
If crop set to `true`, `accept` option automatically set to `'image/*'`.
For single upload without crop, `autoUpload` automatically set to `true`.

To setup size of window and minimum size of crop area use `previewSize` property. Default is `[200,200]`.  

`imageAutoOrientation` option is set to `false` by default

---

You can access your images\files by:

```php
$model = ExampleModel::findOne(1);
$uploads = $model->getFiles();

foreach($uploads as $u){
    echo $u->imgTag('thumb2', true,['style'=>'border:1px solid red;']);
    //or just url (for files/download links)
    echo \yii\helpers\Html::a($u->original, $u->getPublicFileUrl('original', true));
}
```

You can filter files like this:
```php
$uploads = $model->imagesOnly()->getFiles();
// or
$uploads = $model->filesOnly()->getFiles();
```

You can fetch first file in the row:
```php
$uploads = $model->getFirstFile();
```

You can display your images\files in the `GridView`.

Add in the column list:

```php
 [
     'class' => 'deanar\fileProcessor\components\ImageColumn',
     'header' => 'Image',   // optional
     'empty' => 'No Image', // optional
     'type' => 'projects',  // optional, default value goes from behavior options
     'variation' => '_thumb',
     'htmlOptions' => [], // optional
 ],
```

You can display list of your images\files anywhere else via `DisplayWidget`, e.g. in `DetailView` widget or just in the view.

Case with `DetailView`:

```php
'attributes' => [
    'id',
    'title',
    ...
    [
        'attribute'=>'Images',
        'value'=>\deanar\fileProcessor\widgets\DisplayWidget::widget(['type'=>'projects','type_id'=>$model->id,'variation'=>'_thumb']),
        'format'=>'raw',
    ],
    ...
    'text',
],
```

All properties of DisplayWidget are required.


## TODOs and progress

- Special widget for single file uploads [*****]
- Access control system [*****]
- Internationalization (EN + RU) [*****]
- More customization [**---]
- Crop and other features of jquery.fileapi [****-]
- API for upload files by url or by path [-----]
- Console commands for generating new image variations [-----]
- Admin interface for viewing and editing all uploaded files [-----]
- Mode for generating image variations on the fly (by request) [-----]
- Mode for generating image variations in background [-----]
- Advanced variation features: watermarks, cropping, rotation etc. [***--]
- Beautiful alerts (e.g. http://rubaxa.github.io/Ply/) [-----]
- Refactoring [*----]


## Changelog

=======
### 0.1.4 (2016-06-05)
  * Turkish language support (Thanks to https://github.com/fg)

### 0.1.3 (2016-05-19)
  * Possibility to save models in console

### 0.1.2 (2015-08-18)
  * Bug: Several single-upload widgets with crop
  * Bug: Removing image preview in single-upload widget on delete

### 0.1.1 (2015-07-16)
  * `imageAutoOrientation` option is set to `false` by default

### 0.1.0 (2015-03-10)
  * First tagged version.
