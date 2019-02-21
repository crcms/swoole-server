<?php

namespace CrCms\Server;

use BadFunctionCallException;

/**
 * set_process_name
 *
 * @param string $name
 * @return bool
 */
function set_process_name(string $name): bool
{
    if (function_exists('cli_set_process_title')) {
        return cli_set_process_title($name);
    } elseif (function_exists('swoole_set_process_name')) {
        swoole_set_process_name($name);
        return true;
    } else {
        throw new BadFunctionCallException("No available functions found");
    }
}

/**
 * clearOpcache
 *
 * @return void
 */
function clear_opcache(): void
{
    if (extension_loaded('apc')) {
        apc_clear_cache();
    }

    if (extension_loaded('Zend OPcache')) {
        opcache_reset();
    }
}