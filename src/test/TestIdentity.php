<?php

namespace andmemasin\myabstract\test;

use yii\web\IdentityInterface;

final class TestIdentity implements IdentityInterface
{
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
        return 1;
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
