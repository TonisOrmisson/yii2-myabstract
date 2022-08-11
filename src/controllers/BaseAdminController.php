<?php

namespace andmemasin\myabstract\controllers;


use andmemasin\myabstract\traits\ConsoleAwareTrait;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class BaseAdminController extends Controller
{
    use ConsoleAwareTrait;

    /** @var  string name of admin permission */
    public string $adminPermission = 'admin';

    /**
     * {@inheritdoc}
     * @return array<string, array<string, mixed>>
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [$this->adminPermission],
                    ],
                ],
            ],

            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

}