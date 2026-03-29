<?php
session_start();
require_once 'login.php';

// ── Guard ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('location: search.php');
    exit;
}

$run_id       = intval($_POST['run_id']);
$scan_scope   = isset($_POST['scan_scope'])   ? trim($_POST['scan_scope'])   : '';
$result_format= isset($_POST['result_format'])? trim($_POST['result_format']): 'per_sequence';
$sequence_id  = isset($_POST['sequence_id'])  ? intval($_POST['sequence_id']): 0;

$pdo = get_pdo();

// ── Fetch run details ────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM Runs WHERE id = :id");
$stmt->execute([':id' => $run_id]);
$run  = $stmt->fetch();

if (!$run) {
    header('location: search.php');
    exit;
}

$results_dir = __DIR__ . '/results';

// ── Build the FASTA file to scan ─────────────────────────────────
// If scanning one sequence, write a temp FASTA with just that sequence
// If scanning all, use the existing run FASTA file

if ($scan_scope === 'one' && $sequence_id > 0) {

    // Fetch the single sequence from database via PDO
    $stmt_one = $pdo->prepare(
        "SELECT accession, species, sequence
         FROM Sequences WHERE id = :id AND run_id = :run_id"
    );
    $stmt_one->execute([':id' => $sequence_id, ':run_id' => $run_id]);
    $seq_row = $stmt_one->fetch();

    if (!$seq_row) {
        $_SESSION['search_error'] = 'Sequence not found. Please try again.';
        header("location: run_motifs.php?run_id=$run_id");
        exit;
    }

    // Write a temporary single-sequence FASTA
    $tmp_fasta = $results_dir . "/run_{$run_id}_single_{$sequence_id}.fasta";
    $fasta_content = ">{$seq_row['accession']} [{$seq_row['species']}]\n";
    $fasta_content .= wordwrap($seq_row['sequence'], 60, "\n", true) . "\n";
    file_put_contents($tmp_fasta, $fasta_content);

    $fasta_to_scan = $tmp_fasta;
    $result_format = 'per_sequence';
    $scan_label    = $seq_row['accession'] . ' [' . $seq_row['species'] . ']';

} else {
    $fasta_to_scan = $results_dir . "/run_{$run_id}_sequences.fasta";
    $scan_label    = 'all sequences';
}

// ── Call Python motif scanning script ────────────────────────────
$script      = escapeshellarg(__DIR__ . '/scripts/run_motifs.py');
$fasta_arg   = escapeshellarg($fasta_to_scan);
$rid_arg     = escapeshellarg($run_id);
$results_arg = escapeshellarg($results_dir);

// Use unique output suffix if scanning one sequence
if ($scan_scope === 'one') {
    // Temporarily rename expected output so it doesn't clash
    $motif_output = $results_dir . "/run_{$run_id}_motifs_seq{$sequence_id}.txt";
} else {
    $motif_output = $results_dir . "/run_{$run_id}_motifs.txt";
}

$command = "python3 $script $fasta_arg $rid_arg $results_arg 2>&1";
$output  = shell_exec($command);

// Clean up temp FASTA if used
if ($scan_scope === 'one' && isset($tmp_fasta) && file_exists($tmp_fasta)) {
    unlink($tmp_fasta);
}

// Rename default output file if scanning one sequence
$default_output = $results_dir . "/run_{$run_id}_motifs.txt";
if ($scan_scope === 'one' && file_exists($default_output)) {
    rename($default_output, $motif_output);
}

if ($output === null || strpos(trim($output), 'SUCCESS:') !== 0) {
    $error = htmlspecialchars(trim($output));
    $_SESSION['motif_error'] = "Motif scan failed: $error";
    header("location: run_motifs.php?run_id=$run_id");
    exit;
}

$total_motifs = intval(str_replace('SUCCESS:', '', trim($output)));

// ── Parse motif output file ───────────────────────────────────────
$motif_data = [];
$no_motif_seqs = [];

if (file_exists($motif_output)) {
    foreach (file($motif_output) as $line) {
        $line = trim($line);
        if ($line === '') continue;

        $parts = explode('|', $line);

        if ($parts[0] === 'MOTIF') {
            $motif_data[] = [
                'accession' => $parts[1] ?? '',
                'species'   => $parts[2] ?? '',
                'motif'     => $parts[3] ?? '',
                'start'     => $parts[4] ?? '',
                'end'       => $parts[5] ?? '',
                'length'    => $parts[6] ?? '',
            ];
        } elseif ($parts[0] === 'NOMOTIFS') {
            $no_motif_seqs[] = [
                'accession' => $parts[1] ?? '',
                'species'   => $parts[2] ?? ''
            ];
        }
    }
}

// ── Store result in database via PDO ─────────────────────────────
$stmt_res = $pdo->prepare(
    "INSERT INTO Results (run_id, result_type, file_path, summary)
     VALUES (:run_id, 'motif_scan', :path, :summary)"
);
$stmt_res->execute([
    ':run_id'  => $run_id,
    ':path'    => "results/" . basename($motif_output),
    ':summary' => "$total_motifs motifs found scanning $scan_label"
]);

// ── Store everything in session for display page ─────────────────
$_SESSION['motif_data']      = $motif_data;
$_SESSION['no_motif_seqs']   = $no_motif_seqs;
$_SESSION['motif_total']     = $total_motifs;
$_SESSION['motif_format']    = $result_format;
$_SESSION['motif_run_id']    = $run_id;
$_SESSION['motif_scan_label']= $scan_label;

header('location: motif_results.php');
exit;
?>
