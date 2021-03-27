## v3.0.0 (2021-03-27)

### Added

- Added `Sepiphy\Laravel\Acl\UserInterface` interface.
- Added `Sepiphy\Laravel\Acl\Http\Middleware\EnsureUserHasRole` and `Sepiphy\Laravel\Acl\Http\Middleware\EnsureUserHasPermission` middleware.
- Added `Sepiphy\Laravel\Acl\Facade` class and it's alias is "ACL".

### Changed
- Renamed namespace from `Sepiphy\Laravel\Acl\Eloquent` to `Sepiphy\Laravel\Acl\Models`.
- Renamed trait from `Sepiphy\Laravel\Acl\HasAcl` to `Sepiphy\Laravel\Acl\HasRolesPermissions`.
- Renamed class from `Sepiphy\Laravel\Acl\AclServiceProvider` to `Sepiphy\Laravel\Acl\ServiceProvider`.
- Renamed configuration key from "eloquent" to "model".
- Changed `CreateRolesTable` migration (added "hidden" column with boolean type).
