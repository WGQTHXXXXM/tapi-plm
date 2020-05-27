<?php

//DateTime 转换成毫秒级时间戳
if (!function_exists('dateTimeToMs')) {

    function dateTimeToMs(DateTime $date)
    {
        $time = $date->format('U.u');
        $ms = $time * 1000;
        return (int)$ms;
    }
}




//获取当前毫秒时间戳
if (!function_exists('nowTimeMs')) {
    function nowTimeMs()
    {
        $date = new DateTime();

        return dateTimeToMs($date);
    }
}


