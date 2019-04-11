# Change Log
All notable changes to this project will (in theory) be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [4.0.0] - 2019-04-11
### Added
- Added recovery method and MFA review flags to user resource returned from
  `/authentication` and `/user` endpoints
- Added recovery methods, as previously defined in idp-pw-api.
- Added "Hide" feature for users with increased privacy concerns
- Added `PUT /mfa/{mfaId}` endpoint to update MFA labels.
- Added `invite` property on `/authentication` for new user invite authentication
- Added ability to restart a password recovery method verification
- New 'manager' MFA type -- on request, send a backup code to user's manager.
- Added 'groups' and 'personal_email' fields to User object and database table.
- Automatically creates a recovery method using `personal_email`, only for new users.
- Added `profile_review` property on user response, to trigger a review at login.
- Added new user onboarding flow for users without a primary email address.
- Added `PUT /user/{employeeID}/password/assess` endpoint to pre-assess a new password.

### Changed
- Changed password reuse error HTTP status code from 422 to 409
- Unverified recovery methods are now included in listing from `GET /user/{id}/method`
- Updated Welcome email to remove Insite obsolescense notification
- /method/{uid}/verify no longer requires `employee_id`
- Changed dates in API to use ISO-8601 format (e.g. 2019-01-08T12:54:00Z)
- Default MFA labels are now set according to the type of MFA (e.g. "Smartphone")
- `/mfa/{id}/verify` returns the mfa object

### Removed
- Removed spouse_email from user table, model, and API
- Removed `mfa.nag` property from user response, replaced by `profile_review`

## [3.5.0] - 2018-07-17
### Added
- Add spouse and manager email fields

## [3.4.2] - 2018-06-12
### Changed
- Receive and use the Mfa to decide about sending emails

## [3.4.1] - 2018-01-18
### Changed
- Include MFA rate limit email content

## [3.4.0] - 2018-01-17
### Added
- Automated MFA related email updates

## [3.3.2] - 2017-12-21
### Changed
- Updated email content and fixing require_mfa updates

## [3.3.1] - 2017-12-18
### Changed
- Minor GA fix

## [3.3.0] - 2017-12-08
### Added
- Added Google Analytics

## [3.2.1] - 2017-12-06
### Changed
- Changed Welcome Email content

## [3.2.0] - 2017-11-30
### Added
- New Welcome Email

## [3.1.0] - 2017-11-28
### Added
- 2-Step Verification

## [3.0.0] - 2017-08-31
### Added
- Introduces ability to have emails sent under certain circumstances, e.g., "New 
  account created"

## [2.2.0] - 2017-06-16
### Added
- Allow consumers to provide email or username during authentication.

## [2.1.0] - 2017-06-16
### Added
- Ability to search for users by username and/or email.

## [2.0.0] - 2017-06-14
### Added
- (No description)

## [1.0.0] - 2017-06-01
### Added
- Initial version of ID Broker.

[Unreleased]: https://github.com/silinternational/idp-id-broker/compare/4.0.0...HEAD
[4.0.0]: https://github.com/silinternational/idp-id-broker/compare/3.5.0...4.0.0
[3.5.0]: https://github.com/silinternational/idp-id-broker/compare/3.4.2...3.5.0
[3.4.2]: https://github.com/silinternational/idp-id-broker/compare/3.4.1...3.4.2
[3.4.1]: https://github.com/silinternational/idp-id-broker/compare/3.4.0...3.4.1
[3.4.0]: https://github.com/silinternational/idp-id-broker/compare/3.3.2...3.4.0
[3.3.2]: https://github.com/silinternational/idp-id-broker/compare/3.3.1...3.3.2
[3.3.1]: https://github.com/silinternational/idp-id-broker/compare/3.3.0...3.3.1
[3.3.0]: https://github.com/silinternational/idp-id-broker/compare/3.2.1...3.3.0
[3.2.1]: https://github.com/silinternational/idp-id-broker/compare/3.2.0...3.2.1
[3.2.0]: https://github.com/silinternational/idp-id-broker/compare/3.1.0...3.2.0
[3.1.0]: https://github.com/silinternational/idp-id-broker/compare/3.0.0...3.1.0
[3.0.0]: https://github.com/silinternational/idp-id-broker/compare/2.2.0...3.0.0
[2.2.0]: https://github.com/silinternational/idp-id-broker/compare/2.1.0...2.2.0
[2.1.0]: https://github.com/silinternational/idp-id-broker/compare/2.0.0...2.1.0
[2.0.0]: https://github.com/silinternational/idp-id-broker/compare/1.0.0...2.0.0
[1.0.0]: https://github.com/silinternational/idp-id-broker/commit/06c28b8ad18545cd2bdec4d09d2f9f146394409c