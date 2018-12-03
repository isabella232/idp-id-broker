<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $uuid
 * @property string $employee_id
 * @property string $first_name
 * @property string $last_name
 * @property string $display_name
 * @property string $username
 * @property string $email
 * @property int $current_password_id
 * @property string $active
 * @property string $locked
 * @property string $last_changed_utc
 * @property string $last_synced_utc
 * @property string $require_mfa
 * @property string $nag_for_mfa_after
 * @property string $last_login_utc
 * @property string $manager_email
 * @property string $spouse_email
 * @property string $nag_for_method_after
 * @property int $do_not_disclose
 *
 * @property EmailLog[] $emailLogs
 * @property Method[] $methods
 * @property Mfa[] $mfas
 * @property Password $currentPassword
 */
class UserBase extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uuid', 'employee_id', 'first_name', 'last_name', 'username', 'email', 'active', 'locked', 'last_changed_utc', 'last_synced_utc', 'nag_for_mfa_after', 'nag_for_method_after'], 'required'],
            [['current_password_id', 'do_not_disclose'], 'integer'],
            [['active', 'locked', 'require_mfa'], 'string'],
            [['last_changed_utc', 'last_synced_utc', 'nag_for_mfa_after', 'last_login_utc', 'nag_for_method_after'], 'safe'],
            [['uuid'], 'string', 'max' => 64],
            [['employee_id', 'first_name', 'last_name', 'display_name', 'username', 'email', 'manager_email', 'spouse_email'], 'string', 'max' => 255],
            [['employee_id'], 'unique'],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['current_password_id'], 'exist', 'skipOnError' => true, 'targetClass' => Password::className(), 'targetAttribute' => ['current_password_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'uuid' => Yii::t('app', 'Uuid'),
            'employee_id' => Yii::t('app', 'Employee ID'),
            'first_name' => Yii::t('app', 'First Name'),
            'last_name' => Yii::t('app', 'Last Name'),
            'display_name' => Yii::t('app', 'Display Name'),
            'username' => Yii::t('app', 'Username'),
            'email' => Yii::t('app', 'Email'),
            'current_password_id' => Yii::t('app', 'Current Password ID'),
            'active' => Yii::t('app', 'Active'),
            'locked' => Yii::t('app', 'Locked'),
            'last_changed_utc' => Yii::t('app', 'Last Changed Utc'),
            'last_synced_utc' => Yii::t('app', 'Last Synced Utc'),
            'require_mfa' => Yii::t('app', 'Require Mfa'),
            'nag_for_mfa_after' => Yii::t('app', 'Nag For Mfa After'),
            'last_login_utc' => Yii::t('app', 'Last Login Utc'),
            'manager_email' => Yii::t('app', 'Manager Email'),
            'spouse_email' => Yii::t('app', 'Spouse Email'),
            'nag_for_method_after' => Yii::t('app', 'Nag For Method After'),
            'do_not_disclose' => Yii::t('app', 'Do Not Disclose'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmailLogs()
    {
        return $this->hasMany(EmailLog::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMethods()
    {
        return $this->hasMany(Method::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMfas()
    {
        return $this->hasMany(Mfa::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrentPassword()
    {
        return $this->hasOne(Password::className(), ['id' => 'current_password_id']);
    }
}
