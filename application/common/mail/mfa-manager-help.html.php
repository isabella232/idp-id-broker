<?php
use yii\helpers\Html as yHtml;

/**
 * @var string $displayName     Name of user. Provided by user profile.
 * @var string $managerEmail    Email address of user's manager. Provided by user profile.
 * @var string $idpDisplayName  Display name of IDP instance. Provided by environment variable IDP_DISPLAY_NAME.
 * @var string $supportName     Help center name.  Provided by environment variable SUPPORT_NAME.
 * @var string $supportEmail    Help center email address.  Provided by environment variable SUPPORT_EMAIL.
 * @var string $emailSignature  Email signature. Provided by environment variable EMAIL_SIGNATURE.
 */
?>
<p>Dear <?= yHtml::encode($displayName) ?>,</p>

<p>
    You have requested assistance in accessing your <?= yHtml::encode($idpDisplayName) ?> account. An email
    containing an access code has been sent to your manager at <?= yHtml::encode($managerEmail) ?>.
    This access code can be used in place of your other 2-Factor Authentication options. Please contact
    your manager to obtain this access code from them.
</p>
<p>
    If you did not request this code, it could be a sign someone else has compromised your account. Please
    contact <?= yHtml::encode($supportName) ?> at <?= yHtml::encode($supportEmail) ?>
    as soon as possible to report the incident.
</p>
<p>
    Thanks,
</p>
<p>
    <i><?= yHtml::encode($emailSignature); ?></i>
</p>