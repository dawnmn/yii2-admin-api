<?php
return [
    'upload_static_url'=>'http://www.yourdomain.com/', // 静态文件网站
    'download_job_file_root'=>\Yii::getAlias('@data') . '/', // 下载文件缓存目录
    'aes_file_path'=>\Yii::getAlias('@data').'/aes/', // aes 存放目录 建议更换为一个安全性高的地方
    'api_limit_list'=>[ // 接口请求限制列表
        'admin/login'=>[
            'expire'=>10,
            'limit'=>3
        ]
    ],
];
