## v3.0.0 (Unreleased)

### Added

- Added `Sepiphy\Laravel\Acl\UserInterface` interface.
- Added `Sepiphy\Laravel\Acl\Http\Middleware\EnsureUserHasRole` and `Sepiphy\Laravel\Acl\Http\Middleware\EnsureUserHasPermission` middleware.
- Added `Sepiphy\Laravel\Acl\AclFacade` class and it's alias is "ACL".

### Changed
- Changed namespace from `Sepiphy\Laravel\Acl\Eloquent` to `Sepiphy\Laravel\Acl\Models`.
- Changed configuration key from "eloquent" to "model".
- Changed `CreateRolesTable` migration (added "hidden" column with boolean type).
