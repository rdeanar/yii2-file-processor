<?php

use yii\db\Schema;

class m140516_113603_create_file_storage_table extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%fp_uploads}}', [
            'id' => 'pk',
            'timestamp' => Schema::TYPE_TIMESTAMP . ' NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'type' => Schema::TYPE_STRING . ' DEFAULT NULL',
            'type_id' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
            'hash' => Schema::TYPE_STRING . ' DEFAULT NULL',
            'ord' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT "0"',
            'filename' => Schema::TYPE_STRING . ' NOT NULL',
            'original' => Schema::TYPE_STRING . ' NOT NULL',
            'mime' => Schema::TYPE_STRING . ' NOT NULL DEFAULT ""',
            'size' => Schema::TYPE_INTEGER . ' NOT NULL',
            'width' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
            'height' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
        ]);

        $this->createIndex('type_type_id', '{{%fp_uploads}}', ['type', 'type_id']);
        $this->createIndex('type_hash', '{{%fp_uploads}}', ['type', 'hash']);
    }

    public function down()
    {
        $this->dropTable('fp_uploads');
    }
}
