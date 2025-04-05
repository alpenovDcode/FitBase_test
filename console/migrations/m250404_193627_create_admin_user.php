<?php

use yii\db\Migration;
use common\models\User;

class m250404_193627_create_admin_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $user = new User();
        $user->username = 'admin';
        $user->email = 'admin@example.com';
        $user->status = User::STATUS_ACTIVE;
        $user->setPassword('admin123');
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();
        
        return $user->save();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%user}}', ['username' => 'admin']);
        
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250404_193627_create_admin_user cannot be reverted.\n";

        return false;
    }
    */
}
