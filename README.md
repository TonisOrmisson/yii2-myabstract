# Yii2-Myabstracts

![build](https://github.com/TonisOrmisson/yii2-myabstract/actions/workflows/php.yml/badge.svg)

## Soft delete schema

This major version uses Laravel-style soft-delete timestamp semantics by default.

Default lifecycle columns:

- `created_at`
- `updated_at`
- `deleted_at`

Default audit columns:

- `created_by`
- `updated_by`
- `deleted_by`

Active records have `deleted_at IS NULL`. Deleted records have `deleted_at` set to the deletion timestamp.

Apps that still use the previous columns must override the column-name properties on their models until their own migrations are complete:

```php
public string $userCreatedCol = 'user_created';
public string $userUpdatedCol = 'user_updated';
public string $userClosedCol = 'user_closed';
public string $timeCreatedCol = 'time_created';
public string $timeUpdatedCol = 'time_updated';
public string $timeClosedCol = 'time_closed';
```
