<?php

declare(strict_types=1);

$file = $argv[1] ?? 'coverage.xml';
$xml = simplexml_load_file($file);
if ($xml === false) {
    fwrite(STDERR, "Unable to read coverage file.\n");
    exit(1);
}
$metrics = $xml->project->metrics;
$covered = (int) $metrics['coveredstatements'];
$total = (int) $metrics['statements'];
$percent = $total === 0 ? 0.0 : ($covered / $total) * 100;
if ($percent < 85.0) {
    fwrite(STDERR, sprintf("Coverage %.2f%% is below 85%%.\n", $percent));
    exit(1);
}
fwrite(STDOUT, sprintf("Coverage %.2f%% meets 85%%.\n", $percent));
