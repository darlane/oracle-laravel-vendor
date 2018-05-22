<?php
/**
 * Created by PhpStorm.
 * User: darlane
 * Date: 22.05.18
 * https://github.com/darlane
 */
if (!function_exists('array_keys_rename')) {
    /**
     * Rename array keys.
     * @param array $array
     * @param array $keysMap
     * @return array
     */
    function array_keys_rename(array $array, array $keysMap)
    {
        $result = [];
        foreach ($array as $item) {
            foreach ($keysMap as $oldName => $newName) {
                if ($oldName === $newName) {
                    continue;
                }
                if ($newName) {
                    $item[$newName] = $item[$oldName];
                }

                unset($item[$oldName]);
            }
            $result[] = $item;
        }
        return $result;
    }
}


if (!function_exists('array_keys_set_type')) {
    /**
     * Set type to array_keys
     * @param array $array
     * @param array $keysMap
     * @return array
     */
    function array_keys_set_type(array $array, array $keysMap)
    {
        $result = [];
        foreach ($array as $item) {
            foreach ($keysMap as $key => $dataType) {
                if ($dataType === 'integer') {
                    $item[$key] = $item[$key] !== null ? (int)$item[$key] : null;
                } else if ($dataType === 'float') {
                    $item[$key] = (float)$item[$key];
                } else if ($dataType === 'boolean') {
                    $item[$key] = (boolean)$item[$key];
                } else if ($dataType === 'byte_to_mb') {
                    $item[$key] = number_format($item[$key] / 1024 / 1024, 2, '.', '');
                } else if ($dataType === 'date') {
                    $item[$key] = $item[$key] !== null ? date('d.m.Y', strtotime($item[$key])) : null;
                }
            }
            $result[] = $item;
        }
        return $result;
    }
}