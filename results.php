<?php
session_start();
require_once 'login.php';

// ── Guard: must have a current run ───────────────────────────────
if (!isset($_SESSION['current_run_id'])) {
    header('location: search.php');
    exit;
}

$run_id = $_SESSION['current_run_id'];
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
    "SELECT id, accession, species, seq_length, sequence
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
    <title>ProtExplorer &mdash; results</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .seq-toggle {
            background: none;
            border: none;
            color: var(--primary);
            cursor: pointer;
            font-size: 0.82rem;
            font-weight: 700;
            padding: 0;
            text-decoration: underline;
        }
        .seq-toggle:hover { color: var(--primary-dark); }
        .seq-hidden { display: none; margin-top: 0.5rem; }
        .analysis-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .analysis-grid .card { margin-bottom: 0; }
        .status-badge {
            display: inline-block;
            padding: 0.2rem 0.7rem;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-complete {
            background: #eaf5ee;
            color: #2a5e3e;
        }
        .badge-running {
            background: var(--primary-light);
            color: var(--primary-dark);
        }
        .badge-failed {
            background: #fdecea;
            color: #7b1a14;
        }
    </style>
    <script>
        function toggleSeq(id) {
            var box = document.getElementById('seq-' + id);
            var btn = document.getElementById('btn-' + id);
            if (box.classList.contains('seq-hidden')) {
                box.classList.remove('seq-hidden');
                btn.textContent = 'hide sequence';
            } else {
                box.classList.add('seq-hidden');
                btn.textContent = 'show sequence';
            }
        }
    </script>
</head>
<body>

<?php require_once 'menuf.php'; ?>

<div class="page-wrap">

    <div class="page-header" style="margin-top:2rem;">
        <h1>results</h1>
        <p>
            <?php echo htmlspecialchars($run['protein_family']); ?>
            in <?php echo htmlspecialchars($run['taxon']); ?>
            &mdash; <?php echo $run['num_sequences']; ?> sequences retrieved
        </p>
    </div>

    <!-- Run summary -->
    <div class="card">
        <h2>run summary</h2>
        <table>
            <tr>
                <th>parameter</th>
                <th>value</th>
            </tr>
            <tr>
                <td>protein family</td>
                <td><?php echo htmlspecialchars($run['protein_family']); ?></td>
            </tr>
            <tr>
                <td>taxonomic group</td>
                <td><?php echo htmlspecialchars($run['taxon']); ?></td>
            </tr>
            <tr>
                <td>sequences retrieved</td>
                <td><?php echo $run['num_sequences']; ?></td>
            </tr>
            <tr>
                <td>run date</td>
                <td><?php echo $run['created_at']; ?></td>
            </tr>
            <tr>
                <td>status</td>
                <td>
                    <span class="status-badge badge-<?php echo $run['status']; ?>">
                        <?php echo $run['status']; ?>
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Analysis buttons -->
    <div class="analysis-grid">

        <div class="card" style="text-align:center;">
            <h2>conservation analysis</h2>
            <p class="card-desc">
                Align sequences with Clustal Omega and generate
                a conservation profile plot across the alignment.
            </p>
            <a href="run_conservation.php?run_id=<?php echo $run_id; ?>"
               class="btn btn-primary">run alignment</a>
        </div>

        <div class="card" style="text-align:center;">
            <h2>motif scan</h2>
            <p class="card-desc">
                Scan sequences against the PROSITE database using
                patmatmotifs to identify known protein domains.
            </p>
            <a href="run_motifs.php?run_id=<?php echo $run_id; ?>"
               class="btn btn-accent">scan motifs</a>
        </div>

        <div class="card" style="text-align:center;">
            <h2>phylogenetic tree</h2>
            <p class="card-desc">
                Build a phylogenetic tree from the Clustal Omega
                Newick output and visualise evolutionary relationships.
            </p>
            <a href="run_phylogeny.php?run_id=<?php echo $run_id; ?>"
               class="btn btn-outline">build tree</a>
        </div>

        <div class="card" style="text-align:center;">
            <h2>structure links</h2>
            <p class="card-desc">
                Retrieve 3D structure links for each sequence — AlphaFold
                predicted structures via UniProt, with fallback to NCBI
                protein pages where no structure is available.
            </p>
            <a href="run_structures.php?run_id=<?php echo $run_id; ?>"
               class="btn btn-outline">find structures</a>
        </div>

    </div>

    <!-- Sequences table -->
    <div class="card">
        <h2>retrieved sequences (<?php echo count($sequences); ?>)</h2>

        <?php if (count($sequences) === 0): ?>
            <div class="alert alert-error">
                No sequences found for this run. Please try searching again.
            </div>
        <?php else: ?>

        <table>
            <tr>
                <th>#</th>
                <th>accession</th>
                <th>species</th>
                <th>length (aa)</th>
                <th>sequence</th>
            </tr>
            <?php $i = 1; foreach ($sequences as $seq): ?>
            <tr>
                <td><?php echo $i; ?></td>
                <td>
                    <a href="https://www.ncbi.nlm.nih.gov/protein/<?php
                        echo htmlspecialchars($seq['accession']); ?>"
                       target="_blank">
                        <?php echo htmlspecialchars($seq['accession']); ?>
                    </a>
                </td>
                <td><em><?php echo htmlspecialchars($seq['species']); ?></em></td>
                <td><?php echo $seq['seq_length']; ?></td>
                <td>
                    <button class="seq-toggle"
                            id="btn-<?php echo $seq['id']; ?>"
                            onclick="toggleSeq(<?php echo $seq['id']; ?>)">
                        show sequence
                    </button>
                    <div class="seq-box seq-hidden"
                         id="seq-<?php echo $seq['id']; ?>">
                        <?php
                        // Print sequence in 60-character lines
                        echo htmlspecialchars(
                            wordwrap($seq['sequence'], 60, "\n", true)
                        );
                        ?>
                    </div>
                </td>
            </tr>
            <?php $i++; endforeach; ?>
        </table>

        <?php endif; ?>
    </div>

</div>

<footer>
    ProtExplorer &mdash; IWD2 assessed website &mdash;
    <a href="credits.php">credits &amp; AI usage</a>
</footer>

</body>
</html>
```
