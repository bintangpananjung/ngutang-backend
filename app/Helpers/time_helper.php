<?php
function getSinceTimeMessage($unix_time1, $unix_time2)
{
    $insecond = floor($unix_time2 - $unix_time1);
    $inminute = floor($insecond / 60);
    if ($inminute == 0) {
        return $insecond . " detik";
    }
    $inhour = floor($inminute / 60);
    if ($inhour == 0) {
        return $inminute . " menit";
    }
    $inday = floor($inhour / 24);
    if ($inday == 0) {
        return $inhour . " jam";
    }
    $inmonth = floor($inday / 30);
    if ($inmonth == 0) {
        return $inday . " hari";
    }
    $inyear = floor($inmonth / 12);
    if ($inyear == 0) {
        return $inmonth . " bulan";
    }
    return $inyear . " tahun";
}
