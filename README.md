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

This package keeps timestamp values as `DATETIME(6)` / `Y-m-d H:i:s.u` values. Laravel's migration helpers commonly create SQL `TIMESTAMP` columns for `created_at`, `updated_at`, and `deleted_at`, but that is not a UNIX integer timestamp. Both `DATETIME(6)` and `TIMESTAMP(6)` are read by Laravel/Eloquent as date objects.

The practical difference is timezone handling: MySQL `DATETIME` stores the exact calendar value written, while MySQL `TIMESTAMP` can be converted according to the DB session timezone. Keeping `DATETIME(6)` avoids timezone surprises during the Yii-to-Laravel transition. A future schema cleanup may convert these columns to `TIMESTAMP(6)` if strict Laravel migration-helper compatibility becomes more important than preserving current DB behavior.

Apps that still use the previous columns must override the column-name properties on their models until their own migrations are complete:

```php
public string $userCreatedCol = 'user_created';
public string $userUpdatedCol = 'user_updated';
public string $userClosedCol = 'user_closed';
public string $timeCreatedCol = 'time_created';
public string $timeUpdatedCol = 'time_updated';
public string $timeClosedCol = 'time_closed';
```

Overrides only change column names. They do not preserve the old end-of-time active-row convention; active rows must still have the configured deleted timestamp column set to `NULL`.

## Migrating an app module to 9.x

Migrate one app module or table group at a time.

1. Update the module package constraint to allow `andmemasin/yii2-myabstract:^9`.
2. Add a DB migration that renames the columns:

```php
$this->renameColumn('{{table_name}}', 'time_created', 'created_at');
$this->renameColumn('{{table_name}}', 'time_updated', 'updated_at');
$this->renameColumn('{{table_name}}', 'time_closed', 'deleted_at');
$this->renameColumn('{{table_name}}', 'user_created', 'created_by');
$this->renameColumn('{{table_name}}', 'user_updated', 'updated_by');
$this->renameColumn('{{table_name}}', 'user_closed', 'deleted_by');
```

3. Convert active rows to the new Laravel-style soft-delete state:

```php
$this->alterColumn('{{table_name}}', 'deleted_at', $this->dateTime(6)->null());
$this->alterColumn('{{table_name}}', 'deleted_by', $this->integer()->null());
$this->update('{{table_name}}', ['deleted_at' => null, 'deleted_by' => null], ['deleted_by' => [0, null]]);
```

Use `dateTime(6)` for this migration phase unless the module explicitly decides to also take on timezone semantics changes. Do not convert to UNIX integer timestamps.

4. Replace indexes that include `user_closed` with equivalent indexes using `deleted_at`.
5. Remove old column-name overrides from migrated models.
6. Keep explicit old-name overrides only on models whose tables are not migrated yet.
7. Search the module for direct references to old names and update queries/search models/tests:

```bash
rg -n "time_created|time_updated|time_closed|user_created|user_updated|user_closed" modules/andmemasin/<module>
```

8. Run the module tests and PHPStan before release:

```bash
vendor/bin/codecept run modules/andmemasin/<module>/tests
php vendor/bin/phpstan analyze -c modules/andmemasin/<module>/phpstan-dev.neon
```

Do not dual-write old and new columns. If a module cannot migrate its tables yet, keep it on the old major version or add explicit old-name overrides until that module gets its own migration.

## Optional audit schema helper

MyAbstract provides `andmemasin\myabstract\schema\AuditSchemaHelper` for explicit schema maintenance in migrations or project-owned maintenance code.

It is not a console command and does not run automatically.

```php
use andmemasin\myabstract\schema\AuditSchemaHelper;

$helper = new AuditSchemaHelper(Yii::$app->db, [
    'survey',
    'sample',
]);
$helper->ensure();
```

The helper only creates the current audit columns: `created_by`, `updated_by`, `deleted_by`, `created_at`, `updated_at`, and `deleted_at`.

It never creates legacy columns: `user_created`, `user_updated`, `user_closed`, `time_created`, `time_updated`, or `time_closed`.

The `user` table and auth/runtime tables are skipped by default. Old-column renames are still the owning module's migration responsibility.
