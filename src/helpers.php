<?php

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