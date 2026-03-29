<?php
session_start();
require_once 'login.php';

// ── Guard ────────────────────────────────────────────────────────
if (!isset($_GET['run_id'])) {
    header('location: search.php');
    exit;
}

$run_id = intval($_GET['run_id']);
$pdo    = get_pdo();

// ── Fetch run details ────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM Runs WHERE id = :id");
$stmt->execute([':id' => $run_id]);
$run  = $stmt->fetch();

if (!$run) {
    header('location: search.php');
    exit;
}

$results_dir = __DIR__ . '/results';
$fasta_path  = $results_dir . "/run_{$run_id}_sequences.fasta";
$plot_path   = $results_dir . "/run_{$run_id}_conservation.png";
$plot_url    = "results/run_{$run_id}_conservation.png";
$aligned_path = $results_dir . "/run_{$run_id}_aligned.fasta";

// ── Check if conservation already run for this run_id ────────────
$already_run = file_exists($plot_path);

// ── Run conservation analysis if not already done ────────────────
$error_msg   = '';
$stats       = [];

if (!$already_run) {
    $script      = escapeshellarg(__DIR__ . '/scripts/run_conservation.py');
    $fasta_arg   = escapeshellarg($fasta_path);
    $rid_arg     = escapeshellarg($run_id);
    $results_arg = escapeshellarg($results_dir);

    $command = "python3 $script $fasta_arg $rid_arg $results_arg 2>&1";
    $output  = shell_exec($command);

    if ($output !== null && strpos(trim($output), 'SUCCESS:') === 0) {
        // Parse the pipe-separated stats
        $data  = str_replace('SUCCESS:', '', trim($output));
        $parts = explode('|', $data);

        $stats = [
            'mean_cons'      => floatval($parts[0]),
            'max_cons'       => floatval($parts[1]),
            'min_cons'       => floatval($parts[2]),
            'fully_cons'     => intval($parts[3]),
            'highly_cons'    => intval($parts[4]),
            'most_cons_pos'  => intval($parts[5]),
            'least_cons_pos' => intval($parts[6]),
            'aln_length'     => intval($parts[7]),
            'n_seqs'         => intval($parts[8])
        ];

        // Store result in database via PDO
        $stmt_res = $pdo->prepare(
            "INSERT INTO Results (run_id, result_type, file_path, summary)
             VALUES (:run_id, 'conservation_plot', :path, :summary)"
        );
        $stmt_res->execute([
            ':run_id'  => $run_id,
            ':path'    => $plot_url,
            ':summary' => "Mean conservation: " . number_format($stats['mean_cons'], 4) .
                          " | Alignment length: " . $stats['aln_length'] .
                          " | Sequences: " . $stats['n_seqs']
        ]);

    } else {
        $error_msg = htmlspecialchars(trim($output));
    }

} else {
    // Already run — fetch stats from Results table
    $stmt_res = $pdo->prepare(
        "SELECT summary FROM Results
         WHERE run_id = :run_id AND result_type = 'conservation_plot'
         LIMIT 1"
    );
    $stmt_res->execute([':run_id' => $run_id]);
    $res_row = $stmt_res->fetch();
}

// ── Parse aligned FASTA for display ─────────────────────────────
$aligned_seqs = [];
if (file_exists($aligned_path)) {
    $current_id  = null;
    $current_seq = '';
    foreach (file($aligned_path) as $line) {
        $line = trim($line);
        if (strpos($line, '>') === 0) {
            if ($current_id !== null) {
                $aligned_seqs[$current_id] = $current_seq;
            }
            $current_id  = substr($line, 1);
            $current_seq = '';
        } else {
            $current_seq .= $line;
        }
    }
    if ($current_id !== null) {
        $aligned_seqs[$current_id] = $current_seq;
    }
}

$active_page = 'search';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProtExplorer &mdash; conservation analysis</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-box {
            background: var(--primary-light);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem;
            text-align: center;
        }
        .stat-box .stat-value {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary-dark);
            display: block;
        }
        .stat-box .stat-label {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 0.2rem;
            display: block;
        }
        .seq-aligned {
            font-family: 'Courier New', monospace;
            font-size: 0.75rem;
            line-height: 1.8;
            overflow-x: auto;
            white-space: pre;
        }
        .seq-id {
            display: inline-block;
            width: 200px;
            color: var(--primary-dark);
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: top;
        }
    </style>
</head>
<body>

<?php require_once 'menuf.php'; ?>

<div class="page-wrap">

    <div class="page-header" style="margin-top:2rem;">
        <h1>conservation analysis</h1>
        <p>
            <?php echo htmlspecialchars($run['protein_family']); ?>
            in <?php echo htmlspecialchars($run['taxon']); ?>
        </p>
    </div>

    <p style="margin-bottom:1rem;">
        <a href="results.php" class="btn btn-outline"
           style="font-size:0.85rem; padding:0.4rem 1rem;">
            &larr; back to results
        </a>
    </p>

    <?php if ($error_msg !== ''): ?>
        <div class="alert alert-error">
            Conservation analysis failed: <?php echo $error_msg; ?>
        </div>

    <?php elseif (file_exists($plot_path)): ?>

        <!-- Summary statistics -->
        <?php if (!empty($stats)): ?>
        <div class="stats-grid">
            <div class="stat-box">
                <span class="stat-value">
                    <?php echo number_format($stats['mean_cons'] * 100, 1); ?>%
                </span>
                <span class="stat-label">mean conservation</span>
            </div>
            <div class="stat-box">
                <span class="stat-value">
                    <?php echo $stats['fully_cons']; ?>
                </span>
                <span class="stat-label">fully conserved positions</span>
            </div>
            <div class="stat-box">
                <span class="stat-value">
                    <?php echo $stats['aln_length']; ?>
                </span>
                <span class="stat-label">alignment length (aa)</span>
            </div>
            <div class="stat-box">
                <span class="stat-value">
                    <?php echo $stats['n_seqs']; ?>
                </span>
                <span class="stat-label">sequences aligned</span>
            </div>
        </div>

        <div class="card">
            <h2>additional statistics</h2>
            <table>
                <tr><th>statistic</th><th>value</th></tr>
                <tr>
                    <td>maximum conservation score</td>
                    <td><?php echo number_format($stats['max_cons'], 4); ?></td>
                </tr>
                <tr>
                    <td>minimum conservation score</td>
                    <td><?php echo number_format($stats['min_cons'], 4); ?></td>
                </tr>
                <tr>
                    <td>highly conserved positions (&ge;0.8)</td>
                    <td><?php echo $stats['highly_cons']; ?></td>
                </tr>
                <tr>
                    <td>most conserved position</td>
                    <td>position <?php echo $stats['most_cons_pos']; ?></td>
                </tr>
                <tr>
                    <td>least conserved position</td>
                    <td>position <?php echo $stats['least_cons_pos']; ?></td>
                </tr>
            </table>
        </div>
        <?php endif; ?>

        <!-- Conservation plot -->
        <div class="card">
            <h2>conservation profile</h2>
            <p style="font-size:0.88rem; color:var(--text-muted); margin-bottom:0.75rem;">
                Each bar represents one position in the alignment.
                Bar height indicates the fraction of sequences sharing
                the most common amino acid at that position.
                The dashed line shows the mean conservation score.
            </p>
            <img src="<?php echo $plot_url; ?>"
                 alt="Conservation plot" class="result-plot">
        </div>

        <!-- Aligned sequences -->
        <?php if (!empty($aligned_seqs)): ?>
        <div class="card">
            <h2>aligned sequences (<?php echo count($aligned_seqs); ?>)</h2>
            <p style="font-size:0.88rem; color:var(--text-muted); margin-bottom:0.75rem;">
                Dashes represent gap characters introduced by the alignment.
                Sequences are shown in full alignment length.
            </p>
            <div class="seq-aligned">
<?php foreach ($aligned_seqs as $seq_id => $seq): ?>
<span class="seq-id"><?php echo htmlspecialchars(substr($seq_id, 0, 25)); ?></span>  <?php
$chunks = str_split($seq, 60);
echo htmlspecialchars($chunks[0]) . "\n";
for ($c = 1; $c < count($chunks); $c++) {
    echo str_repeat(' ', 202) . htmlspecialchars($chunks[$c]) . "\n";
}
?>
<?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-error">
            Conservation plot could not be generated. Please try again.
        </div>
    <?php endif; ?>

</div>

<footer>
    ProtExplorer &mdash; IWD2 assessed website &mdash;
    <a href="credits.php">credits &amp; AI usage</a>
</footer>

</body>
</html>
```
