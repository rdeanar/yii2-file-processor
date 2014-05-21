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


Usage
-----

Once the extension is installed, simply use it in your form by  :

```php
<?= \deanar\fileProcessor\UploadWidget::widget(); ?>```


