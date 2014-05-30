File Processor
==============
Upload and process files and images

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist deanar/yii2-file-processor "*"
```

or add

```
"deanar/yii2-file-processor": "*"
```

to the require section of your `composer.json` file.

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
    ]) ?>
```


