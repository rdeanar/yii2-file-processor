File Processor (Yii2 Extension)
==============
Upload and process files and images.

Based on jquery.fileapi [Link to github](https://github.com/RubaXa/jquery.fileapi)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run (not ready yet)

```
php composer.phar require --prefer-dist deanar/yii2-file-processor "*"
```

or add

```
"deanar/yii2-file-processor": "*"
```

to the require section of your `composer.json` file;


Then run migrations

```bash
 ./yii migrate/up --migrationPath=@deanar/fileProcessor/migrations
```

Include module into your web config

```php
'modules' => [
    'fp' => [
        'class' => 'deanar\fileProcessor\Module',
        'space_replacement' => '-',
        'variations_config' => require(__DIR__ . '/file_processor_variations.php'),
        'upload_dir' => 'uploads',
        'default_quality' => 95,
        //'default_resize_mod' => 'outbound',
        //'unlink_files' => true,
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

Create file file_processor_variations.php in config directory and configure image variations like:

```php
return [
    'projects' => [
        '_original' => false,
        'thumb' => [200, 150, 'inset'],
        'small' => [300, 200, 'outbound', 75],
        'big' => [
            'width' => 600,
            'height' => 350,
            'mode' => 'outbound',
            'quality' => 75
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
        '_insert' => ['app\models\Project' => 'avatar']  
    ],
    
];
```

Usage
-----

Once the extension is installed, simply use it in your form by adding this code to view:

```php
<?= \deanar\fileProcessor\widgets\UploadWidget::widget([
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

]) ?>
```

And you can access your images\files by:

```php
$model = Project::findOne(6);
$uploads = $model->getFiles();

foreach($uploads as $u){
    echo $u->imgTag('thumb2', true,['style'=>'border:1px solid red;']);
    //or just url (for files/download links)
    echo $u->getPublicFileUrl('thumb2', true);
}
```

You can filter files:
```php
$uploads = $model->imagesOnly()->getFiles();
// or
$uploads = $model->filesOnly()->getFiles();
```

You can fetch first file in the row:
```php
$uploads = $model->getFirstFile();
```

You can display your images\files in the GridView.

Add in the column list:

```php
 [
     'class' => 'deanar\fileProcessor\components\ImageColumn',
     'header' => 'Image',   // optional
     'empty' => 'No Image', // optional
     'type' => 'projects',  // optional, default value goes from behavior options
     'variation' => '_thumb',
     'htmlOptions' => [] // optional
 ],
```

You can display list of your images\files anywhere else via DisplayWidget, e.g. in DetailView widget or just in the view.

Case with DetailView:

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


TODOs
-----
- Special widget for single file uploads;
- Access control system
- Internationalization
- Crop and other features of jquery.fileapi
- API for upload files by url or by path
- Refactoring
