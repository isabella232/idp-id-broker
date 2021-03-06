<?php
namespace common\components;

use common\models\Mfa;
use common\models\User;
use yii\base\Component;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class MfaBackendTotp extends Component implements MfaBackendInterface
{
    /**
     * @var string
     */
    public $apiBaseUrl;

    /**
     * @var string
     */
    public $apiKey;

    /**
     * @var string
     */
    public $apiSecret;

    /**
     * @var string
     */
    public $issuer;

    /**
     * @var MfaApiClient
     */
    public $client;

    public function init()
    {
        $this->client = new MfaApiClient($this->apiBaseUrl, $this->apiKey, $this->apiSecret);
        parent::init();
    }

    /**
     * Initialize a new MFA backend registration
     * @param int $userId
     * @return array
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function regInit(int $userId): array
    {
        $user = User::findOne(['id' => $userId]);
        if ($user == null) {
            throw new NotFoundHttpException("User not found when trying to create new TOTP configuration");
        }

        return $this->client->createTotp($user->username, \Yii::$app->params['idpDisplayName']);
    }

    /**
     * Initialize authentication sequence
     * @param int $mfaId
     * @return array
     */
    public function authInit(int $mfaId): array
    {
        return [];
    }

    /**
     * Verify response from user is correct for the MFA backend device
     * @param int $mfaId The MFA ID
     * @param string $value Value provided by user, such as TOTP number or U2F challenge response
     * @return bool
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function verify(int $mfaId, $value): bool
    {
        $mfa = Mfa::findOne(['id' => $mfaId]);
        if ($mfa == null) {
            throw new NotFoundHttpException('MFA configuration not found');
        }

        if ($this->client->validateTotp($mfa->external_uuid, $value)) {
            if ($mfa->verified !== 1) {
                $mfa->verified = 1;
                if (! $mfa->save()) {
                    throw new ServerErrorHttpException();
                }
            }

            return true;
        }
        return false;
    }

    /**
     * Delete MFA backend configuration
     * @param int $mfaId
     * @return bool
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function delete(int $mfaId): bool
    {
        $mfa = Mfa::findOne(['id' => $mfaId]);
        if ($mfa == null) {
            throw new NotFoundHttpException('MFA configuration not found');
        }

        if (is_string($mfa->external_uuid)) {
            return $this->client->deleteTotp($mfa->external_uuid);
        }

        return true;
    }
}
