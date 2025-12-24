<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AWS Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AWS services (S3, STS). The SDK automatically picks up
    | AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY from the environment, so they
    | don't need to be explicitly configured here.
    |
    */

    // General
    'region' => env('AWS_REGION', 'us-east-1'),

    // S3 Configuration
    's3_profile' => env('AWS_S3_PROFILE'),
    's3_endpoint' => env('AWS_ENDPOINT_URL_S3'),
    'use_path_style_endpoint' => (bool) env('AWS_USE_PATH_STYLE_ENDPOINT', false),

    // IAM Role Assumption (for restricted environments like VPC endpoints)
    'role_arn' => env('AWS_ROLE_ARN'),
    'role_session_name' => env('AWS_ROLE_SESSION_NAME', 'databasement'),
    'sts_profile' => env('AWS_STS_PROFILE'),
    'sts_endpoint' => env('AWS_ENDPOINT_URL_STS'),

];
