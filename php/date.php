<?php
function twoDigits($number)
{
    return $number < 10 ? "0" . $number : $number;
}
function timeStampToFullDate($time)
{
    $DayNames = ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"];
    $MonthNames = ["janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"];
    $date = getDate($time);
    $dateString = $DayNames[$date["wday"]] . " le ";
    $dateString .= $date["mday"] . " ";
    $dateString .= $MonthNames[(int) $date["mon"] - 1] . " " . $date["year"] . " @ ";
    $dateString .= twoDigits($date["hours"]) . ":" . twoDigits($date["minutes"]) . ":" . twoDigits($date["seconds"]);
    return $dateString;
}