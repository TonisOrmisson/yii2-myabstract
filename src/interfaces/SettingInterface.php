<?php

namespace andmemasin\myabstract\interfaces;

/**
 * Interface SettingInterface
 * @package andmemasin\myabstract\src\interfaces
 * @author Tõnis Ormisson <tonis@andmemasin.eu>
 */
interface SettingInterface
{
    public function findOneByKey(string $key) : ?SettingInterface;
    public function getKey() : mixed;
    public function getValue() : mixed;
    public function setKey(mixed $key) : void;
    public function setValue(mixed $value) : void;

    /**
     * @param string|int $key
     * @param mixed $value
     * @param array<mixed> $data
     * @return SettingInterface
     */
    public function createNew(string|int $key, mixed $value, array $data = []) : SettingInterface;
    public function valueAsArray() : array;

    /**
     * @param null|string[] $attributeNames
     * @param bool $clearErrors
     * @return bool
     */
    public function validate($attributeNames = null, $clearErrors = true);

    /**
     * @return SettingInterface[]
     */
    public function getSettings() : array;
    public function settingByIndex(string|int $index) : ?SettingInterface;

    /**
     * @param bool $runValidation
     * @param ?array $attributeNames
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = null);

    /**
     * @return array<mixed>
     */
    public function errors() : array;

}