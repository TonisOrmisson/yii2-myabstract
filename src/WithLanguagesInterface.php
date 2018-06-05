<?php

namespace andmemasin\myabstract;

use andmemasin\language\models\Language;

/**
 * @property Language[] $languages
 * @package andmemasin\myabstract
 * @author Tonis Ormisson <tonis@andmemasin.eu>
 */
interface WithLanguagesInterface
{
    public function getLanguages();
}
