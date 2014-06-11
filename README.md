File Processor
==============
Upload and process files and images

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run (not ready yet)

```
php composer.phar require --prefer-dist deanar/yii2-file-processor "*"
```

or

1) add

```
"deanar/yii2-file-processor": "*"
```

to the require section of your `composer.json` file;

2) add repository location in the repositories section of your `composer.json` file, like this:


```
    "repositories": [
        {
            "url": "git@github.com:rdeanar/yii2-file-processor.git",
            "type": "git"
        }
    ],
```

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
        'small' => [300, 200, 'outbound'],
        'big' => [600, 350, 'outbound'],
    ],
    'article_header' => [
        '_original' => true,
        'thumb' => [200, 150, 'inset'],
    ],
];
```

Usage
-----

Once the extension is installed, simply use it in your form by adding this code to view:

```php
    <?= \deanar\fileProcessor\UploadWidget::widget([
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
        }
```
or just url (for files/download links)

```php
echo $u->getPublicFileUrl('thumb2', true);
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
                'value'=>\deanar\fileProcessor\DisplayWidget::widget(['type'=>'projects','type_id'=>$model->id,'variation'=>'_thumb']),
                'format'=>'raw',
            ],
            ...
            'text',
        ],
```

All properties of DisplayWidget are required.



