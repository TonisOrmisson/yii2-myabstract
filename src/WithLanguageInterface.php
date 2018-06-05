<?php

namespace andmemasin\myabstract;

use andmemasin\language\models\Language;

/**
 * @property Language $language
 * @package andmemasin\myabstract
 * @author Tonis Ormisson <tonis@andmemasin.eu>
 */
interface WithLanguageInterface
{
    public function getLanguage();
}
