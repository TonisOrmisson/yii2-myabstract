<?php

declare(strict_types=1);

namespace andmemasin\myabstract;

use andmemasin\myabstract\test\InvokeProtectedTrait;
use Yii;
use yii\caching\ArrayCache;
use yii\web\IdentityInterface;

/** The audit user, the audit column rules and when a record's queries are cached. */
final class RecordCacheAndUserTest extends \Codeception\Test\Unit
{
    use InvokeProtectedTrait;

    private mixed $cache;

    protected function _before(): void
    {
        $this->cache = Yii::$app->get('cache', false);
    }

    protected function _after(): void
    {
        Yii::$app->set('cache', $this->cache);
        Yii::$app->db->enableQueryCache = true;
        Yii::$app->user->setIdentity(null);
    }

    public function testTheAuditUserIsTheLoggedInUserElseOne(): void
    {
        $record = new MyActiveTraitTestRecord();
        $this->assertSame(1, $this->invokeMethod($record, 'userId'), 'nobody logged in');

        Yii::$app->user->setIdentity(new IdIdentity(7));
        $this->assertSame(7, $this->invokeMethod($record, 'userId'));

        Yii::$app->user->setIdentity(new IdIdentity(0));
        $this->assertSame(1, $this->invokeMethod($record, 'userId'), 'an empty id');
    }

    public function testRulesCoverTheAuditColumns(): void
    {
        $this->assertSame([
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
        ], (new MyActiveTraitTestRecord())->rules());
    }

    public function testQueriesUseTheCacheOnlyWithACacheAndTheQueryCache(): void
    {
        Yii::$app->set('cache', new ArrayCache());
        $this->assertTrue(CachedAllRecord::usesCache());
        $this->assertSame(0, CachedAllRecord::find()->queryCacheDuration, 'cached for good');
        $this->assertNull(MyActiveTraitTestRecord::find()->queryCacheDuration, 'not a cache-all record');

        Yii::$app->db->enableQueryCache = false;
        $this->assertFalse(CachedAllRecord::usesCache());
        $this->assertNull(CachedAllRecord::find()->queryCacheDuration);

        Yii::$app->db->enableQueryCache = true;
        Yii::$app->set('cache', null);
        $this->assertFalse(CachedAllRecord::usesCache());
        $this->assertNull(CachedAllRecord::find()->queryCacheDuration);
    }
}

final class CachedAllRecord extends MyActiveRecord
{
    public static bool $cacheAll = true;

    public static function tableName(): string
    {
        return 'cached_all_record';
    }
}

final class IdIdentity implements IdentityInterface
{
    public function __construct(private readonly int $id)
    {
    }

    public static function findIdentity($id): ?self
    {
        return null;
    }

    public static function findIdentityByAccessToken($token, $type = null): ?self
    {
        return null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAuthKey(): string
    {
        return '';
    }

    public function validateAuthKey($authKey): bool
    {
        return false;
    }
}
