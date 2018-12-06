<?php
namespace frontend\controllers;

use common\helpers\MySqlDateTime;
use common\models\User;
use frontend\components\BaseRestController;
use Yii;

class UserController extends BaseRestController
{
    public function actionIndex() // GET /user
    {
        return User::search(Yii::$app->request->queryParams);
    }

    public function actionView(string $employeeId) // GET /user/abc123
    {
        $user = User::findOne(['employee_id' => $employeeId]);

        if (isset($user)) {
            return $user;
        }

        Yii::$app->response->statusCode = 204;

        return null;
    }

    public function actionCreate(): User
    {
        $user = new User();

        $user->scenario = User::SCENARIO_NEW_USER;

        $user->attributes = Yii::$app->request->getBodyParams();

        $this->save($user);

        /*
         * Refresh user model to retrieve database default values
         */
        $user->refresh();

        return $user;
    }

    public function actionUpdate(string $employeeId)
    {
        $user = User::findOne(['employee_id' => $employeeId]);

        if ($user === null) {
            Yii::$app->response->statusCode = 204;

            return null;
        }

        $user->scenario = User::SCENARIO_UPDATE_USER;

        $user->attributes = Yii::$app->request->getBodyParams();

        $this->save($user);

        return $user;
    }

    public function actionUpdatePassword(string $employeeId)
    {
        $user = User::findOne(['employee_id' => $employeeId]);

        if ($user === null) {
            Yii::$app->response->statusCode = 204;

            return null;
        }

        $user->scenario = User::SCENARIO_UPDATE_PASSWORD;

        $user->attributes = Yii::$app->request->getBodyParams();

        $this->save($user);

        return $user;
    }

    public function actionExpiring(): array
    {
        return User::getExpiringUsers(Yii::$app->request->queryParams);
    }

    public function actionFirstPassword(): array
    {
        $createdOn = Yii::$app->request->queryParams['created_on'] ?? MySqlDateTime::today();

        return User::getUsersWithFirstPasswords($createdOn);
    }
}
