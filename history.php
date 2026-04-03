<?php
session_start();
require_once 'login.php';

$pdo = get_pdo();

// ── Get run history from session ──────────────────────────────────
$run_history = isset($_SESSION['run_history']) ? $_SESSION['run_history'] : [];

// ── For each run in history, fetch full details from database ─────
$runs = [];
foreach ($run_history as $h) {
    $stmt = $pdo->prepare(
        "SELECT r.*, COUNT(s.id) as seq_count
         FROM Runs r
         LEFT JOIN Sequences s ON s.run_id = r.id
         WHERE r.id = :id
         GROUP BY r.id"
    );
    $stmt->execute([':id' => $h['run_id']]);
    $run = $stmt->fetch();
    if ($run) {
        $runs[] = $run;
    }
}

// ── Check what results exist for each run ────────────────────────
$results_available = [];
foreach ($runs as $run) {
    $rid = $run['id'];
    $results_available[$rid] = [
        'conservation' => file_exists(__DIR__ . "/results/run_{$rid}_conservation.png"),
        'motifs'       => file_exists(__DIR__ . "/results/run_{$rid}_motifs.txt"),
        'phylogeny'    => file_exists(__DIR__ . "/results/run_{$rid}_tree.nwk"),
        'structures'   => file_exists(__DIR__ . "/results/run_{$rid}_structures.txt")
    ];
}

$active_page = 'history';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProtExplorer &mdash; history</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .run-card {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: 1rem;
            overflow: hidden;
        }
        .run-header {
            background: var(--primary-light);
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .run-header .run-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--primary-dark);
        }
        .run-header .run-date {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        .run-body {
            padding: 1rem;
        }
        .run-analyses {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 0.75rem;
        }
        .badge-done {
            background: #eaf5ee;
            color: #2a5e3e;
            border-radius: 20px;
            padding: 0.15rem 0.6rem;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .badge-pending {
            background: var(--primary-light);
            color: var(--text-muted);
            border-radius: 20px;
            padding: 0.15rem 0.6rem;
            font-size: 0.75rem;
        }
        .status-badge {
            display: inline-block;
            padding: 0.2rem 0.7rem;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-complete { background: #eaf5ee; color: #2a5e3e; }
        .badge-failed   { background: #fdecea; color: #7b1a14; }
        .badge-running  { background: var(--primary-light); color: var(--primary-dark); }
    </style>
</head>
<body>

<?php require_once 'menuf.php'; ?>

<div class="page-wrap">

    <div class="page-header" style="margin-top:2rem;">
        <h1>Run History</h1>
        <p>Searches you have run in this session</p>
    </div>

    <?php if (empty($runs)): ?>

        <div class="card" style="text-align:center;">
            <h2>no runs yet</h2>
            <p class="card-desc">
                You have not run any searches in this session.
                Head to the search page to get started.
            </p>
            <a href="search.php" class="btn btn-primary"
               style="margin-top:0.5rem;">run a search</a>
        </div>

    <?php else: ?>

        <div class="alert alert-info">
            Showing <?php echo count($runs); ?> run(s) from your current session.
            History is stored per session — starting a new browser session
            will begin a fresh history. Your data remains in the database
            and can be reloaded if you know your run ID.
        </div>

        <?php foreach (array_reverse($runs) as $run):
            $rid  = $run['id'];
            $avail = $results_available[$rid];
        ?>
        <div class="run-card">
            <div class="run-header">
                <div>
                    <span class="run-title">
                        <?php echo htmlspecialchars($run['protein_family']); ?>
                        in <?php echo htmlspecialchars($run['taxon']); ?>
                    </span>
                    <span class="run-date">
                        &mdash; <?php echo $run['created_at']; ?>
                    </span>
                </div>
                <span class="status-badge badge-<?php echo $run['status']; ?>">
                    <?php echo $run['status']; ?>
                </span>
            </div>

            <div class="run-body">
                <table style="margin-bottom:0.75rem;">
                    <tr><th>parameter</th><th>value</th></tr>
                    <tr>
                        <td>run ID</td>
                        <td><?php echo $rid; ?></td>
                    </tr>
                    <tr>
                        <td>sequences retrieved</td>
                        <td><?php echo $run['num_sequences']; ?></td>
                    </tr>
                </table>

                <!-- Analyses completed -->
                <p style="font-size:0.82rem; font-weight:700;
                          color:var(--text-muted); margin-bottom:0.4rem;">
                    analyses completed:
                </p>
                <div class="run-analyses">
                    <span class="<?php echo $avail['conservation']
                        ? 'badge-done' : 'badge-pending'; ?>">
                        conservation <?php echo $avail['conservation']
                            ? '&#10003;' : '&ndash;'; ?>
                    </span>
                    <span class="<?php echo $avail['motifs']
                        ? 'badge-done' : 'badge-pending'; ?>">
                        motifs <?php echo $avail['motifs']
                            ? '&#10003;' : '&ndash;'; ?>
                    </span>
                    <span class="<?php echo $avail['phylogeny']
                        ? 'badge-done' : 'badge-pending'; ?>">
                        phylogeny <?php echo $avail['phylogeny']
                            ? '&#10003;' : '&ndash;'; ?>
                    </span>
                    <span class="<?php echo $avail['structures']
                        ? 'badge-done' : 'badge-pending'; ?>">
                        structures <?php echo $avail['structures']
                            ? '&#10003;' : '&ndash;'; ?>
                    </span>
                </div>

                <!-- Action buttons -->
                <div style="margin-top:1rem; display:flex;
                            gap:0.5rem; flex-wrap:wrap;">
                    <?php if ($run['status'] === 'complete'): ?>
                        <a href="results.php?load_run=<?php echo $rid; ?>"
                           class="btn btn-primary"
                           style="font-size:0.85rem; padding:0.4rem 1rem;">
                            view results
                        </a>
                        <?php if ($avail['conservation']): ?>
                        <a href="run_conservation.php?run_id=<?php echo $rid; ?>"
                           class="btn btn-outline"
                           style="font-size:0.85rem; padding:0.4rem 1rem;">
                            conservation
                        </a>
                        <?php endif; ?>
                        <?php if ($avail['motifs']): ?>
                        <a href="run_motifs.php?run_id=<?php echo $rid; ?>"
                           class="btn btn-outline"
                           style="font-size:0.85rem; padding:0.4rem 1rem;">
                            motifs
                        </a>
                        <?php endif; ?>
                        <?php if ($avail['phylogeny']): ?>
                        <a href="run_phylogeny.php?run_id=<?php echo $rid; ?>"
                           class="btn btn-outline"
                           style="font-size:0.85rem; padding:0.4rem 1rem;">
                            phylogeny
                        </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="font-size:0.85rem; color:var(--text-muted);">
                            This run did not complete successfully.
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

    <?php endif; ?>

</div>

<footer>
    ProtExplorer &mdash; IWD2 assessed website &mdash;
    <a href="credits.php">credits &amp; AI usage</a>
</footer>

</body>
</html>
