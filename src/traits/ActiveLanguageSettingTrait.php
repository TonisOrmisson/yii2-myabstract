<?php

namespace andmemasin\myabstract\traits;


/**
 *
 * @property string $keyColumn
 * @property string $value
 * @property array $errors
 *
 * @package andmemasin\myabstract
 * @author Tonis Ormisson <tonis@andmemasin.eu>
 */
trait ActiveLanguageSettingTrait
{

    public function getKey(): mixed
    {
        return $this->{$this->keyColumn};
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setKey(mixed $key) : void{
        $this->{$this->keyColumn} = $key;
    }
    public function setValue(mixed $value) : void {
        /** @var bool|float|int|resource|string|null $value */
        $this->value = strval($value);
    }

    /**
     * @return array<mixed>
     */
    public function errors(): array
    {
        return $this->errors;
    }

}
