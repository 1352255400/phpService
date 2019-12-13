<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ImsCommonService\BaseModel;

// echo CommonService::d();die;

$BaseModel = new BaseModel();
echo $BaseModel->joinType;