<?php

use yii\db\Schema;

class m150111_173823_add_index extends \yii\db\Migration
{
    public function up()
    {
        $this->createIndex('type_type_id', '{{%fp_uploads}}', ['type', 'type_id']);
        $this->createIndex('type_hash', '{{%fp_uploads}}', ['type', 'hash']);
    }

    public function down()
    {
        $this->dropIndex('type_type_id', '{{%fp_uploads}}');
        $this->dropIndex('type_hash', '{{%fp_uploads}}');
    }
}
