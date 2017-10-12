<?php
namespace common\models;

use common\components\MfaBackendInterface;
use common\helpers\MySqlDateTime;
use yii\helpers\ArrayHelper;

/**
 * Class Mfa
 * @package common\models
 * @method Mfa self::findOne()
 */
class Mfa extends MfaBase
{
    const TYPE_TOTP = 'totp';
    const TYPE_U2F = 'u2f';
    const TYPE_BACKUPCODE = 'backupcode';

    public function rules(): array
    {
        return ArrayHelper::merge([
            [
                'created_utc', 'default', 'value' => MySqlDateTime::now(),
            ],
            [
                'type', 'in', 'range' => [self::TYPE_TOTP, self::TYPE_U2F, self::TYPE_BACKUPCODE]
            ],
            [
                'external_uuid', 'required', 'when' => function($model) {
                    return ($model->type === self::TYPE_TOTP || $model->type === self::TYPE_U2F);
                }
            ],
            [
                'verified', 'default', 'value' => 0,
            ],
        ], parent::rules());
    }

    public function fields(): array
    {
        return [
            'id',
            'type',
            'data' => function($model) {
                /** Mfa $model */
                if ($model->user->scenario === User::SCENARIO_AUTHENTICATE && $model->verified === 1) {
                    $backend = self::getBackendForType($model->type);
                    return $backend->authInit($model->id);
                }
                return [];
            }
        ];
    }

    /**
     * Before deleting, if this is a TYPE_BACKUPCODE record, delete all the backup codes first
     * @return bool
     */
    public function beforeDelete()
    {
        if ($this->type === self::TYPE_BACKUPCODE){
            MfaBackupcode::deleteCodesForMfaId($this->id);
        }

        return parent::beforeDelete(); // TODO: Change the autogenerated stub
    }

    /**
     * Check if given type is a v
     * @param string $type
     * @return bool
     */
    public static function isValidType(string $type): bool
    {
        if (in_array($type, [self::TYPE_BACKUPCODE, self::TYPE_U2F, self::TYPE_TOTP])) {
            return true;
        }
        return false;
    }

    /**
     * @param string $type
     * @return MfaBackendInterface
     */
    static function getBackendForType(string $type): MfaBackendInterface
    {
        switch ($type) {
            case self::TYPE_BACKUPCODE:
                return \Yii::$app->backupcode;
            case self::TYPE_TOTP:
                return \Yii::$app->totp;
            case self::TYPE_U2F:
                return \Yii::$app->u2f;
        }
    }
}