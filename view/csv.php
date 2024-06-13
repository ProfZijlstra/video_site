<?php
header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename={$course}_{$block}_quiz_results.csv");

print $header . "\n";

foreach ($data as $row) {
    for ($i = 0; $i < $colCount; $i++) {
        print("\"{$row[$i]}\",");
    }
    print("\n");
}

?>