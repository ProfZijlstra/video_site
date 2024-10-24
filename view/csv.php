<?php

header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename={$course}_{$block}_{$type}.csv");

echo $header."\n";

foreach ($data as $row) {
    for ($i = 0; $i < $colCount; $i++) {
        echo "\"{$row[$i]}\",";
    }
    echo "\n";
}

