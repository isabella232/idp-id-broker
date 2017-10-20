<?php
namespace common\models;

use common\components\MfaBackendInterface;
use common\helpers\MySqlDateTime;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

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
//            [
//                'external_uuid', 'required', 'when' => function($model) {
//                    return ($model->type === self::TYPE_TOTP || $model->type === self::TYPE_U2F);
//                }
//            ],
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
            'created_utc',
            'last_used_utc',
            'data' => function($model) {
                /** @var Mfa $model */
                if ($model->verified === 1 && $model->scenario === User::SCENARIO_AUTHENTICATE) {
                    return $model->authInit();
                }
                return [];
            }
        ];
    }

    /**
     * Before deleting, delete backend record too
     * @return bool
     */
    public function beforeDelete()
    {
        $backend = self::getBackendForType($this->type);
        return $backend->delete($this->id);
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

    /**
     * @return array
     */
    public function authInit()
    {
        $backend = self::getBackendForType($this->type);
        return $backend->authInit($this->id);

    }

    /**
     * @param string|array $value
     * @return bool
     */
    public function verify($value): bool
    {
        $backend = self::getBackendForType($this->type);
        if ($backend->verify($this->id, $value) === true) {
            $this->last_used_utc = MySqlDateTime::now();
            if ( ! $this->save()) {
                \Yii::error([
                    'action' => 'update last_used_utc on mfa after verification',
                    'status' => 'error',
                    'user' => $this->user->email,
                    'mfa_id' => $this->id,
                    'error' => $this->getFirstErrors(),
                ]);
            }
            return true;
        }

        return false;
    }

    /**
     * @param int $userId
     * @param string $type
     * @return array
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     */
    public static function create(int $userId, string $type): array
    {
        /*
         * Make sure $type is valid
         */
        if ( ! self::isValidType($type)) {
            throw new BadRequestHttpException('Invalid MFA type');
        }

        /*
         * Make sure user exists
         */
        $user = User::findOne(['id' => $userId]);
        if ($user == null) {
            throw new BadRequestHttpException("User not found");
        }

        $mfa = new Mfa();

        /*
         * User can only have one 'backupcode' type, so if already exists, use existing
         */
        if ($type == self::TYPE_BACKUPCODE) {
            $existing = self::findOne(['user_id' => $userId, 'type' => self::TYPE_BACKUPCODE]);
            if ($existing instanceof Mfa) {
                $mfa = $existing;
            }
        }

        $mfa->user_id = $userId;
        $mfa->type = $type;
        $mfa->verified = ($type == self::TYPE_BACKUPCODE) ? 1 : 0;

        if ( ! $mfa->save()) {
            \Yii::error([
                'action' => 'create mfa',
                'type' => $type,
                'user' => $user->email,
                'status' => 'error',
                'error' => $mfa->getFirstErrors(),
            ]);
            throw new ServerErrorHttpException("Unable to save new MFA record", 1507904193);
        }

        $backend = self::getBackendForType($type);
        $results = $backend->regInit($userId);

        if (isset($results['uuid'])) {
            $mfa->external_uuid = $results['uuid'];
            unset($results['uuid']);
            if ( ! $mfa->save()) {
                \Yii::error([
                    'action' => 'update mfa',
                    'type' => $type,
                    'user' => $user->email,
                    'status' => 'error',
                    'error' => $mfa->getFirstErrors(),
                ]);
                throw new ServerErrorHttpException("Unable to update MFA record", 1507904194);
            }
        }

        return [
            'id' => $mfa->id,
            'data' => $results,
        ];

    }

    /**
     * Remove records that were not verified within the given time frame
     * @param int $maxAgeHours
     */
    public static function removeOldUnverifiedRecords($maxAgeHours = 2)
    {
        $removeOlderThan = MySqlDateTime::relative('-' . $maxAgeHours . ' hours');
        $mfas = self::find()->where(['verified' => 0])
            ->andWhere(['<', 'created_utc', $removeOlderThan])->all();

        foreach ($mfas as $mfa) {
            $mfa->delete();
        }
    }
}