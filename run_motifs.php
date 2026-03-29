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

// ── Fetch sequences for this run ─────────────────────────────────
$stmt_seq = $pdo->prepare(
    "SELECT id, accession, species, seq_length
     FROM Sequences WHERE run_id = :run_id ORDER BY id ASC"
);
$stmt_seq->execute([':run_id' => $run_id]);
$sequences = $stmt_seq->fetchAll();

$active_page = 'search';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProtExplorer &mdash; motif scan</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function toggleSeqSelect() {
            var scope      = document.getElementById('scan_scope').value;
            var seq_box    = document.getElementById('seq_select_box');
            var format_box = document.getElementById('format_select_box');

            if (scope === 'one') {
                seq_box.style.display    = 'block';
                format_box.style.display = 'none';
                document.getElementById('sequence_id').required = true;
                // Force per_sequence when scanning one sequence
                document.getElementById('result_format').value  = 'per_sequence';
            } else if (scope === 'all') {
                seq_box.style.display    = 'none';
                format_box.style.display = 'block';
                document.getElementById('sequence_id').required = false;
            } else {
                seq_box.style.display    = 'none';
                format_box.style.display = 'none';
            }
        }

        function validateForm() {
            var scope = document.getElementById('scan_scope').value;
            if (scope === '') {
                alert('Please select whether to scan all sequences or one sequence.');
                return false;
            }
            if (scope === 'one' &&
                document.getElementById('sequence_id').value === '') {
                alert('Please select a sequence to scan.');
                return false;
            }
            if (scope === 'all' &&
                document.getElementById('result_format').value === '') {
                alert('Please select how you would like results displayed.');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<?php require_once 'menuf.php'; ?>

<div class="page-wrap">

    <div class="page-header" style="margin-top:2rem;">
        <h1>motif scan</h1>
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

    <div class="card">
        <h2>about motif scanning</h2>
        <p style="font-size:0.9rem;">
            Motif scanning uses the EMBOSS <strong>patmatmotifs</strong> tool
            to search each protein sequence against the PROSITE database.
            PROSITE contains documented protein domains, families and functional
            sites described as patterns and profiles. Identifying known motifs
            in your sequences can reveal their functional domains and biological roles.
        </p>
    </div>

    <div class="card">
        <h2>scan options</h2>

        <form action="process_motifs.php" method="post"
              onsubmit="return validateForm()">

            <input type="hidden" name="run_id"
                   value="<?php echo $run_id; ?>">

            <!-- Scan scope -->
            <div class="form-group">
                <label for="scan_scope">what would you like to scan?</label>
                <select id="scan_scope" name="scan_scope"
                        onchange="toggleSeqSelect()">
                    <option value="">-- select an option --</option>
                    <option value="all">scan all sequences</option>
                    <option value="one">scan one specific sequence</option>
                </select>
                <span class="hint">
                    Scanning all sequences gives a broader picture;
                    scanning one sequence is faster and more focused.
                </span>
            </div>

            <!-- Sequence selector — only shown if 'one' selected -->
            <div id="seq_select_box" style="display:none;">
                <div class="form-group">
                    <label for="sequence_id">select a sequence</label>
                    <select id="sequence_id" name="sequence_id">
                        <option value="">-- select a sequence --</option>
                        <?php foreach ($sequences as $seq): ?>
                        <option value="<?php echo $seq['id']; ?>">
                            <?php echo htmlspecialchars($seq['accession']); ?>
                            &mdash;
                            <em><?php echo htmlspecialchars($seq['species']); ?></em>
                            (<?php echo $seq['seq_length']; ?> aa)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Results display format — only shown if 'all' selected -->
            <div id="format_select_box" style="display:none;">
                <div class="form-group">
                    <label for="result_format">
                        how would you like results displayed?
                    </label>
                    <select id="result_format" name="result_format">
                        <option value="">-- select a format --</option>
                        <option value="combined">
                            combined &mdash; all motifs found across sequences in one table
                        </option>
                        <option value="per_sequence">
                            per sequence &mdash; motifs grouped by sequence
                        </option>
                    </select>
                    <span class="hint">
                        Combined view is useful for spotting which motifs are
                        shared across species. Per sequence view gives more
                        detail per individual protein.
                    </span>
                </div>
            </div>

            <button type="submit" class="btn btn-accent">
                run motif scan
            </button>

        </form>
    </div>

</div>

<footer>
    ProtExplorer &mdash; IWD2 assessed website &mdash;
    <a href="credits.php">credits &amp; AI usage</a>
</footer>

</body>
</html>
