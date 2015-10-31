<?php

$prefix = $_SERVER['DOCUMENT_ROOT'];

/**
 * ���������� ������ ����� � ����������
 * @param string $dir � ����� ���������� ������
 * @return array
 */
function find_all_folders($dir){
    global $prefix;
    $folders = array();
    foreach(scandir($prefix . $dir) as $value){
        if(strpos($value, '.') === 0) {continue;}
        if(!is_file("$prefix$dir/$value"))
            $folders[] = $value;
    }
    return $folders;
}

/**
 * ���������� ������ ������ � ����������
 * @param string $dir � ����� ���������� ������
 * @return array
 */
function list_dir($dir){
    global $prefix;
    $files = array();
    foreach(scandir($prefix . $dir) as $value)
        if((strpos($value, '.') !== 0) && (is_file("$prefix$dir/$value")))
            $files[] = str_replace('../', '', $dir) . $value;
    return $files;
}