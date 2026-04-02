<?php
session_start();
require_once 'login.php';

// ── 1. Validate input ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('location: search.php');
    exit;
}

$protein_sel = isset($_POST['protein_select']) ? trim($_POST['protein_select']) : '';
$taxon_sel   = isset($_POST['taxon_select'])   ? trim($_POST['taxon_select'])   : '';
$max_seq     = isset($_POST['max_sequences'])  ? intval($_POST['max_sequences']): 20;

if ($protein_sel === 'other') {
    $protein_sel = isset($_POST['custom_protein']) ? trim($_POST['custom_protein']) : '';
}
if ($taxon_sel === 'other') {
    $taxon_sel = isset($_POST['custom_taxon']) ? trim($_POST['custom_taxon']) : '';
}

$protein_sel = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $protein_sel);
$taxon_sel   = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $taxon_sel);
$max_seq     = max(5, min(200, $max_seq));

if ($protein_sel === '' || $taxon_sel === '') {
    $_SESSION['search_error'] = 'Protein family and taxonomic group cannot be empty.';
    header('location: search.php');
    exit;
}

// ── 2. Create run record in database via PDO ─────────────────────
$pdo = get_pdo();

$session_token = bin2hex(random_bytes(16));

$stmt = $pdo->prepare(
    "INSERT INTO Runs (session_token, protein_family, taxon, status)
     VALUES (:token, :protein, :taxon, 'running')"
);
$stmt->execute([
    ':token'   => $session_token,
    ':protein' => $protein_sel,
    ':taxon'   => $taxon_sel
]);

$run_id = $pdo->lastInsertId();

$_SESSION['current_run_id']    = $run_id;
$_SESSION['current_run_token'] = $session_token;

if (!isset($_SESSION['run_history'])) {
    $_SESSION['run_history'] = [];
}
$_SESSION['run_history'][] = [
    'run_id'  => $run_id,
    'token'   => $session_token,
    'protein' => $protein_sel,
    'taxon'   => $taxon_sel
];

// ── 3. Call Python script to fetch sequences ─────────────────────
$script  = escapeshellarg(__DIR__ . '/scripts/fetch_sequences.py');
$protein = escapeshellarg($protein_sel);
$taxon   = escapeshellarg($taxon_sel);
$max     = escapeshellarg($max_seq);
$rid     = escapeshellarg($run_id);

$command = "python3 $script $protein $taxon $max $rid 2>&1";
$output  = shell_exec($command);

// ── 4. Handle output ─────────────────────────────────────────────
if ($output !== null && strpos(trim($output), 'SUCCESS:') === 0) {

    $num_sequences = intval(str_replace('SUCCESS:', '', trim($output)));

    // Update status immediately — before anything else
    $stmt_update = $pdo->prepare(
        "UPDATE Runs SET status='complete', num_sequences=:n WHERE id=:id"
    );
    $stmt_update->execute([':n' => $num_sequences, ':id' => $run_id]);

    // ── 5. Read FASTA file and insert sequences via PDO ──────────
    $fasta_path = __DIR__ . "/results/run_{$run_id}_sequences.fasta";

    if (file_exists($fasta_path)) {
        $fasta_content = file_get_contents($fasta_path);
        $entries       = explode('>', $fasta_content);

        $stmt_seq = $pdo->prepare(
            "INSERT INTO Sequences
             (run_id, accession, species, sequence, seq_length)
             VALUES (:run_id, :accession, :species, :sequence, :seq_length)"
        );

        foreach ($entries as $entry) {
            $entry = trim($entry);
            if ($entry === '') continue;

            $lines      = explode("\n", $entry);
            $header     = array_shift($lines);
            $sequence   = implode('', $lines);
            $seq_length = strlen($sequence);

            $parts     = explode(' ', $header);
            $accession = $parts[0];

            $species = 'Unknown';
            if (preg_match('/\[([^\]]+)\]/', $header, $matches)) {
                $species = $matches[1];
            }

            if ($sequence !== '') {
                $stmt_seq->execute([
                    ':run_id'     => $run_id,
                    ':accession'  => $accession,
                    ':species'    => $species,
                    ':sequence'   => $sequence,
                    ':seq_length' => $seq_length
                ]);
            }
        }

        // Store FASTA path in Results table
        $stmt_res = $pdo->prepare(
            "INSERT INTO Results (run_id, result_type, file_path, summary)
             VALUES (:run_id, 'fasta', :path, :summary)"
        );
        $stmt_res->execute([
            ':run_id'  => $run_id,
            ':path'    => "results/run_{$run_id}_sequences.fasta",
            ':summary' => "$num_sequences sequences fetched for $protein_sel in $taxon_sel"
        ]);
    }

    $_SESSION['current_num_sequences'] = $num_sequences;
    header('location: results.php');
    exit;

} else {
    // Mark run as failed
    $stmt_fail = $pdo->prepare("UPDATE Runs SET status='failed' WHERE id=:id");
    $stmt_fail->execute([':id' => $run_id]);

    $raw_error = trim($output);
    // Strip the ERROR: prefix for display
    $display_error = str_replace('ERROR:', '', $raw_error);
    $display_error = htmlspecialchars($display_error);
    $_SESSION['search_error'] = $display_error;

    // Mark run as failed
    $stmt_fail = $pdo->prepare("UPDATE Runs SET status='failed' WHERE id=:id");
    $stmt_fail->execute([':id' => $run_id]);

    header('location: search.php');
    exit;
}
?>
