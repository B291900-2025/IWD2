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

$results_dir    = __DIR__ . '/results';
$fasta_path     = $results_dir . "/run_{$run_id}_sequences.fasta";
$structures_path = $results_dir . "/run_{$run_id}_structures.txt";

// Check FASTA file exists before attempting structure lookup
if (!file_exists($fasta_path)) {
    $_SESSION['search_error'] = "Sequence data not found for run $run_id. Please run a new search.";
    header('location: search.php');
    exit;
}

$error_msg  = '';
$structures = [];

// ── Run structure lookup if not already done ──────────────────────
if (!file_exists($structures_path)) {
    $script      = escapeshellarg(__DIR__ . '/scripts/run_structures.py');
    $fasta_arg   = escapeshellarg($fasta_path);
    $rid_arg     = escapeshellarg($run_id);
    $results_arg = escapeshellarg($results_dir);

    $command = "python3 $script $fasta_arg $rid_arg $results_arg 2>&1";
    $output  = shell_exec($command);

    if ($output !== null && strpos(trim($output), 'SUCCESS:') === 0) {
        $n = intval(str_replace('SUCCESS:', '', trim($output)));

        // Store in Results table via PDO
        $stmt_res = $pdo->prepare(
            "INSERT INTO Results (run_id, result_type, file_path, summary)
             VALUES (:run_id, 'structures', :path, :summary)"
        );
        $stmt_res->execute([
            ':run_id'  => $run_id,
            ':path'    => "results/run_{$run_id}_structures.txt",
            ':summary' => "$n sequences queried for structure data"
        ]);
    } else {
        $error_msg = htmlspecialchars(trim($output));
    }
}

// ── Parse structures file ────────────────────────────────────────
if (file_exists($structures_path)) {
    foreach (file($structures_path) as $line) {
        $line = trim($line);
        if ($line === '') continue;

        $parts = explode('|', $line);
        $structures[] = [
            'accession'     => $parts[0] ?? '',
            'species'       => $parts[1] ?? '',
            'uniprot_id'    => $parts[2] ?? 'N/A',
            'alphafold_url' => $parts[3] ?? '',
            'alphafold_img' => $parts[4] ?? '',
            'confidence'    => $parts[5] ?? 'N/A',
            'coverage'      => $parts[6] ?? 'N/A',
            'ncbi_url'      => $parts[7] ?? ''
        ];
    }
}

// Count how many have AlphaFold structures
$with_structure = count(array_filter($structures, function($s) {
    return $s['alphafold_url'] !== '';
}));

$active_page = 'search';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProtExplorer &mdash; structure links</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .structure-card {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: 1rem;
            overflow: hidden;
        }
        .structure-header {
            background: var(--primary-light);
            padding: 0.6rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .structure-header .accession {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--primary-dark);
        }
        .structure-header .species {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-style: italic;
        }
        .structure-body {
            padding: 1rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            align-items: start;
        }
        .structure-body.no-image {
            grid-template-columns: 1fr;
        }
        .structure-img {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: var(--radius);
        }
        .structure-info table {
            margin-top: 0;
        }
        .badge-found {
            background: #eaf5ee;
            color: #2a5e3e;
            border-radius: 20px;
            padding: 0.15rem 0.6rem;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .badge-notfound {
            background: #fdecea;
            color: #7b1a14;
            border-radius: 20px;
            padding: 0.15rem 0.6rem;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .confidence-bar-wrap {
            background: var(--border);
            border-radius: 4px;
            height: 8px;
            width: 100%;
            margin-top: 0.3rem;
        }
        .confidence-bar {
            height: 8px;
            border-radius: 4px;
            background: var(--primary);
        }
    </style>
</head>
<body>

<?php require_once 'menuf.php'; ?>

<div class="page-wrap">

    <div class="page-header" style="margin-top:2rem;">
        <h1>structure links</h1>
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
            Structure lookup failed: <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <!-- Summary -->
    <div class="card">
        <h2>summary</h2>
        <table>
            <tr><th>parameter</th><th>value</th></tr>
            <tr>
                <td>sequences queried</td>
                <td><?php echo count($structures); ?></td>
            </tr>
            <tr>
                <td>AlphaFold structures found</td>
                <td><?php echo $with_structure; ?></td>
            </tr>
            <tr>
                <td>sequences with NCBI link only</td>
                <td><?php echo count($structures) - $with_structure; ?></td>
            </tr>
        </table>
        <p style="font-size:0.85rem; color:var(--text-muted); margin-top:0.75rem;">
            AlphaFold structures are retrieved via UniProt cross-references
            in the NCBI GenBank record. Where no UniProt ID is available,
            a direct link to the NCBI protein page is provided instead.
            The PAE (Predicted Aligned Error) image shows AlphaFold's
            confidence in the relative positions of residue pairs.
        </p>
    </div>

    <!-- Structure cards -->
    <?php if (empty($structures)): ?>
        <div class="alert alert-info">
            No structure data found. Please try running the analysis again.
        </div>
    <?php else: ?>

        <?php foreach ($structures as $s): ?>
        <div class="structure-card">
            <div class="structure-header">
                <div>
                    <span class="accession">
                        <?php echo htmlspecialchars($s['accession']); ?>
                    </span>
                    &nbsp;
                    <span class="species">
                        <?php echo htmlspecialchars($s['species']); ?>
                    </span>
                </div>
                <?php if ($s['alphafold_url'] !== ''): ?>
                    <span class="badge-found">AlphaFold structure found</span>
                <?php else: ?>
                    <span class="badge-notfound">no AlphaFold structure</span>
                <?php endif; ?>
            </div>

            <div class="structure-body <?php echo $s['alphafold_img'] === '' ? 'no-image' : ''; ?>">

                <?php if ($s['alphafold_img'] !== ''): ?>
                <div>
                    <p style="font-size:0.8rem; font-weight:700;
                              color:var(--text-muted); margin-bottom:0.4rem;">
                        predicted aligned error (PAE) image
                    </p>
                    <img src="<?php echo htmlspecialchars($s['alphafold_img']); ?>"
                         alt="AlphaFold PAE image"
                         class="structure-img">
                </div>
                <?php endif; ?>

                <div class="structure-info">
                    <table>
                        <tr><th>field</th><th>value</th></tr>
                        <tr>
                            <td>UniProt ID</td>
                            <td>
                                <?php if ($s['uniprot_id'] !== 'N/A'): ?>
                                    <a href="https://www.uniprot.org/uniprotkb/<?php
                                        echo htmlspecialchars($s['uniprot_id']); ?>"
                                       target="_blank">
                                        <?php echo htmlspecialchars($s['uniprot_id']); ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color:var(--text-muted);">not found</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if ($s['confidence'] !== 'N/A'): ?>
                        <tr>
                            <td>mean pLDDT confidence</td>
                            <td>
                                <?php echo htmlspecialchars($s['confidence']); ?>
                                <div class="confidence-bar-wrap">
                                    <div class="confidence-bar"
                                         style="width:<?php
                                            echo min(100, floatval($s['confidence']));
                                         ?>%">
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($s['coverage'] !== 'N/A'): ?>
                        <tr>
                            <td>sequence coverage (aa)</td>
                            <td><?php echo htmlspecialchars($s['coverage']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td>NCBI protein page</td>
                            <td>
                                <a href="<?php echo htmlspecialchars($s['ncbi_url']); ?>"
                                   target="_blank">
                                    view on NCBI
                                </a>
                            </td>
                        </tr>
                        <?php if ($s['alphafold_url'] !== ''): ?>
                        <tr>
                            <td>AlphaFold entry</td>
                            <td>
                                <a href="<?php
                                    echo htmlspecialchars($s['alphafold_url']); ?>"
                                   target="_blank">
                                    view structure
                                </a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
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
