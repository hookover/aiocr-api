<?php

if (!function_exists('trans')) {
    /**
     * Translate the given message.
     *
     * @param string $id
     * @param array $parameters
     * @param string $domain
     * @param string $locale
     *
     * @return string
     */
    function trans($id = null, $parameters = [], $domain = 'messages', $locale = null)
    {
        if (is_null($id)) {
            return app('translator');
        }

        return app('translator')->trans($id, $parameters, $domain, $locale);
    }
}

if (!function_exists('__')) {
    /**
     * Translate the given message.
     *
     * @param  string $key
     * @param  array $replace
     * @param  string $locale
     *
     * @return string
     */
    function __($key, $replace = [], $locale = null)
    {
        return app('translator')->getFromJson($key, $replace, $locale);
    }
}

if (!function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param  string $path
     *
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}


if (!function_exists('public_path')) {
    /**
     * Get the configuration path.
     *
     * @param  string $path
     *
     * @return string
     */
    function public_path($path = '')
    {
        return app()->basePath() . '/public' . ($path ? '/' . $path : $path);
    }
}

if (!function_exists('store_real_path')) {
    /**
     * Get the configuration path.
     *
     * @param  string $path
     *
     * @return string
     */
    function store_real_path($path_file = '')
    {
        return public_path(env('DEFAULT_UPLOAD_PATH')) . $path_file;
    }
}


if (!function_exists('store_url')) {
    /**
     * Get the configuration path.
     *
     * @param  string $path
     *
     * @return string
     */
    function store_url($path_file = '')
    {
        if(!env('API_IMAGE_DOMAIN')) {
            throw new \Exception('请配置API IMAGE DOMAIN');
        }
        return 'http://' . env('API_IMAGE_DOMAIN') . '/' . env('DEFAULT_UPLOAD_PATH') . $path_file;
    }
}
