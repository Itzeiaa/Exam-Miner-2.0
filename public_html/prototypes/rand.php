<?php
// List of API keys

$GEMINI_KEYS = ["secret1", "secret2", "secret3","secret4"];

// Shuffle based on microtime for uniqueness
$seed = microtime(true) * 1000000;
mt_srand($seed);
shuffle($GEMINI_KEYS);

// Pick the first key from the shuffled list
$apiKey = $GEMINI_KEYS[array_rand($GEMINI_KEYS)];

/*
// File to store the last used key index
$file = "last_key.txt";

// Read the last used index if it exists
$lastIndex = file_exists($file) ? (int)trim(file_get_contents($file)) : -1;
$totalKeys = count($GEMINI_KEYS);

// Pick a new random index different from the last one
do {
    $newIndex = rand(0, $totalKeys - 1);
} while ($newIndex === $lastIndex && $totalKeys > 1);

// Save the new index for next call
file_put_contents($file, $newIndex);
*/
// Use the key
//$apiKey = $GEMINI_KEYS[array_rand($GEMINI_KEYS)];
// $GEMINI_KEYS[$newIndex];

// Return JSON (for fetch use)
header("Content-Type: application/json");
echo json_encode(["apiKey" => $apiKey]);
?>
