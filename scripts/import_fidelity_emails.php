<?php

// Configuration
$email = 'your-yahoo-email@yahoo.com';
$password = 'your-app-password'; // Generate an App Password in Yahoo Security settings
$apiEndpoint = 'http://localhost:8000/api/v1/bank-webhook'; // Update if running on Hugging Face
$mailbox = '{imap.mail.yahoo.com:993/imap/ssl}N Fid B Info';

// Increase execution time for large imports initally
set_time_limit(300);

// 1. Connect to Yahoo Mail
echo "Connecting to Yahoo Mail...\n";
$mbox = imap_open($mailbox, $email, $password) or die('Cannot connect to Yahoo Mail: ' . imap_last_error());

// 2. Search for emails
// fetching emails from Jan 1, 2026, with "Debit" in subject
echo "Searching for emails...\n";
$searchCriteria = 'SINCE "1-Jan-2026" SUBJECT "Debit"';
$emails = imap_search($mbox, $searchCriteria);

if (!$emails) {
    die("No emails found matching criteria.\n");
}

// 3. Process each email in batches
$totalEmails = count($emails);
rsort($emails); // Process newest first

$batchSize = 20; // Process 20 emails at a time
$batches = array_chunk($emails, $batchSize);
$totalBatches = count($batches);

echo "Found $totalEmails emails to process in $totalBatches batches.\n";

foreach ($batches as $index => $batch) {
    echo "\nProcessing Batch " . ($index + 1) . " of $totalBatches (Size: " . count($batch) . ")\n";

    foreach ($batch as $emailNumber) {
        // Reset timeout counter for each email to ensure continuous processing
        set_time_limit(30);

        // Fetch the raw body (using section 1 usually gets the text/plain part if it exists)
        // We are NOT decoding because the regex expects Quoted-Printable artifacts like =E2=82=A6
        $message = imap_fetchbody($mbox, $emailNumber, 1);

        // Skip if empty
        if (empty($message)) {
            continue;
        }

        // Apply Regex Rules provided
        $amountMatch = [];
        $narrationMatch = [];
        $transactionIdMatch = [];
        $dateMatch = [];

        // Amount: Looks for Naira symbol (=E2=82=A6), space, amount, space, DR
        preg_match('/=E2=82=A6\s([\d,]+\.\d{2})\sDR/', $message, $amountMatch);

        // Narration: Looks for "Narration=0D" followed by content
        preg_match('/Narration=0D\s*(.*?)(?=(?:=0D|=|\s*$))/s', $message, $narrationMatch);

        // Transaction Reference
        preg_match('/Transaction Reference=0D\s*(.*?)(?=(?:=0D|\s*$))/s', $message, $transactionIdMatch);

        // Date/Time
        preg_match('/Date\/Time=0D\s*(.*?)(?=(?:=0D|\s*$))/s', $message, $dateMatch);

        // Check if we found the critical data
        if (!empty($amountMatch[1])) {

            // Clean up data
            $amount = str_replace(',', '', $amountMatch[1]);
            $narration = isset($narrationMatch[1]) ? trim($narrationMatch[1]) : 'Fidelity Bank Debit';
            $transactionDate = isset($dateMatch[1]) ? trim($dateMatch[1]) : date('Y-m-d H:i:s');

            // Attempt to parse the date format usually sent by banks (often d/m/Y H:i or similar)
            $parsedDate = strtotime($transactionDate);
            if ($parsedDate) {
                $formattedDate = date('Y-m-d H:i:s', $parsedDate);
            } else {
                $formattedDate = date('Y-m-d H:i:s'); // Fallback
            }

            // Prepare JSON Payload
            $payload = [
                'transaction_date' => $formattedDate,
                'description' => $narration,
                'amount' => (float) $amount,
                'type' => 'debit',
                'bank_source' => 'Fidelity Email Alerts',
            ];

            // Send to API
            $ch = curl_init($apiEndpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $decodedResponse = json_decode($response, true);
            curl_close($ch);

            $statusMsg = isset($decodedResponse['message']) ? $decodedResponse['message'] : 'Unknown';
            echo "Processed Email #$emailNumber: Amount: $amount, Date: $formattedDate -> $statusMsg ($httpCode)\n";

        } else {
            echo "Skipped Email #$emailNumber: Could not match amount pattern.\n";
        }
    }

    // Optional sleep to be nice to the servers
    echo "Batch " . ($index + 1) . " completed. Sleeping 1s...\n";
    sleep(1);
}

// 4. Close connection
imap_close($mbox);
echo "Done.\n";
