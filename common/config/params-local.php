<?php
return [
    'upload_static_url'=>'http://www.yourdomain.com/', // 静态文件网站
    'download_job_file_root'=>\Yii::getAlias('@data') . '/', // 下载文件缓存目录
    'request_limit_list'=>[ // 接口请求限制列表
        'test/index'=>[
            'expire'=>10,
            'limit'=>2
        ]
    ],
];
