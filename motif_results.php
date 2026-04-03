<?php
session_start();
require_once 'login.php';

// ── Guard ────────────────────────────────────────────────────────
if (!isset($_SESSION['motif_data'])) {
    header('location: search.php');
    exit;
}

$motif_data    = $_SESSION['motif_data'];
$no_motif_seqs = $_SESSION['no_motif_seqs'];
$total_motifs  = $_SESSION['motif_total'];
$result_format = $_SESSION['motif_format'];
$run_id        = $_SESSION['motif_run_id'];
$scan_label    = $_SESSION['motif_scan_label'];

// ── Fetch run details ────────────────────────────────────────────
$pdo  = get_pdo();
$stmt = $pdo->prepare("SELECT * FROM Runs WHERE id = :id");
$stmt->execute([':id' => $run_id]);
$run  = $stmt->fetch();

// ── Group motifs by sequence for per_sequence view ───────────────
$grouped = [];
foreach ($motif_data as $m) {
    $key = $m['accession'] . '|' . $m['species'];
    $grouped[$key][] = $m;
}

$active_page = 'search';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProtExplorer &mdash; motif results</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .motif-badge {
            display: inline-block;
            background: var(--primary-light);
            color: var(--primary-dark);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 0.15rem 0.6rem;
            font-size: 0.78rem;
            font-weight: 700;
        }
        .no-motif {
            color: var(--text-muted);
            font-style: italic;
            font-size: 0.88rem;
        }
        .seq-group {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: 1rem;
            overflow: hidden;
        }
        .seq-group-header {
            background: var(--primary-light);
            padding: 0.6rem 1rem;
            font-weight: 700;
            font-size: 0.88rem;
            color: var(--primary-dark);
        }
        .seq-group-header em {
            font-weight: 400;
            color: var(--text-muted);
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>

<?php require_once 'menuf.php'; ?>

<div class="page-wrap">

    <div class="page-header" style="margin-top:2rem;">
        <h1>motif scan results</h1>
        <p>
            <?php echo htmlspecialchars($run['protein_family']); ?>
            in <?php echo htmlspecialchars($run['taxon']); ?>
            &mdash; scanned: <?php echo htmlspecialchars($scan_label); ?>
        </p>
    </div>

    <p style="margin-bottom:1rem;">
        <a href="results.php" class="btn btn-outline"
           style="font-size:0.85rem; padding:0.4rem 1rem;">
            &larr; back to results
        </a>
        &nbsp;
        <a href="run_motifs.php?run_id=<?php echo $run_id; ?>"
           class="btn btn-accent"
           style="font-size:0.85rem; padding:0.4rem 1rem;">
            run another scan
        </a>
    </p>

    <!-- Summary card -->
    <div class="card">
        <h2>scan summary</h2>
        <table>
            <tr><th>parameter</th><th>value</th></tr>
            <tr>
                <td>sequences scanned</td>
                <td><?php echo count($grouped) + count($no_motif_seqs); ?></td>
            </tr>
            <tr>
                <td>sequences with motifs</td>
                <td><?php echo count($grouped); ?></td>
            </tr>
            <tr>
                <td>sequences with no motifs</td>
                <td><?php echo count($no_motif_seqs); ?></td>
            </tr>
            <tr>
                <td>total motif hits</td>
                <td><?php echo $total_motifs; ?></td>
            </tr>
            <tr>
                <td>display format</td>
                <td><?php echo str_replace('_', ' ', $result_format); ?></td>
            </tr>
        </table>
    </div>

    <?php if ($total_motifs === 0): ?>
        <div class="alert alert-info">
            No PROSITE motifs were found in the scanned sequences.
            This may indicate that the protein family does not contain
            well-characterised PROSITE patterns, or that the sequences
            are too divergent from the reference patterns.
        </div>

    <?php elseif ($result_format === 'combined'): ?>

        <!-- Combined view -->
        <div class="card">
            <h2>all motifs found (<?php echo $total_motifs; ?>)</h2>
            <table>
                <tr>
                    <th>accession</th>
                    <th>species</th>
                    <th>motif</th>
                    <th>start</th>
                    <th>end</th>
                    <th>length</th>
                    <th>PROSITE</th>
                </tr>
                <?php foreach ($motif_data as $m): ?>
                <tr>
                    <td>
                        <a href="https://www.ncbi.nlm.nih.gov/protein/<?php
                            echo htmlspecialchars($m['accession']); ?>"
                           target="_blank">
                            <?php echo htmlspecialchars($m['accession']); ?>
                        </a>
                    </td>
                    <td>
                        <em><?php echo htmlspecialchars($m['species']); ?></em>
                    </td>
                    <td>
                        <span class="motif-badge">
                            <?php echo htmlspecialchars($m['motif']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($m['start']); ?></td>
                    <td><?php echo htmlspecialchars($m['end']); ?></td>
                    <td><?php echo htmlspecialchars($m['length']); ?></td>
                    <td>
                        <a href="https://prosite.expasy.org/cgi-bin/prosite/prosite_search_full.pl?SEARCH=<?php echo urlencode($m['motif']); ?>"
                           target="_blank">search</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

    <?php else: ?>

        <!-- Per sequence view -->
        <div class="card">
            <h2>motifs by sequence</h2>

            <?php foreach ($grouped as $key => $motifs):
                $parts     = explode('|', $key);
                $accession = $parts[0];
                $species   = $parts[1] ?? '';
            ?>
            <div class="seq-group">
                <div class="seq-group-header">
                    <a href="https://www.ncbi.nlm.nih.gov/protein/<?php
                        echo htmlspecialchars($accession); ?>"
                       target="_blank">
                        <?php echo htmlspecialchars($accession); ?>
                    </a>
                    <em><?php echo htmlspecialchars($species); ?></em>
                </div>
                <table>
                    <tr>
                        <th>motif</th>
                        <th>start</th>
                        <th>end</th>
                        <th>length</th>
                        <th>PROSITE</th>
                    </tr>
                    <?php foreach ($motifs as $m): ?>
                    <tr>
                        <td>
                            <span class="motif-badge">
                                <?php echo htmlspecialchars($m['motif']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($m['start']); ?></td>
                        <td><?php echo htmlspecialchars($m['end']); ?></td>
                        <td><?php echo htmlspecialchars($m['length']); ?></td>
                        <td>
                            <a href="https://prosite.expasy.org/cgi-bin/prosite/prosite_search_full.pl?SEARCH=<?php echo urlencode($m['motif']); ?>"
                               target="_blank">search</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php endforeach; ?>

            <?php if (!empty($no_motif_seqs)): ?>
            <div style="margin-top:1rem;">
                <p style="font-size:0.88rem; font-weight:700;
                          color:var(--text-muted); margin-bottom:0.5rem;">
                    sequences with no motifs found:
                </p>
                <?php foreach ($no_motif_seqs as $nm): ?>
                <div class="seq-group">
                    <div class="seq-group-header">
                        <?php echo htmlspecialchars($nm['accession']); ?>
                        <em><?php echo htmlspecialchars($nm['species']); ?></em>
                    </div>
                    <div style="padding:0.6rem 1rem;">
                        <span class="no-motif">
                            No PROSITE motifs detected in this sequence
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>

    <?php endif; ?>

    <!-- PROSITE info -->
    <div class="card">
        <h2>about PROSITE motifs</h2>
        <p style="font-size:0.88rem; line-height:1.75;">
            PROSITE is a database of protein families, domains and
            functional sites. Motifs are described as patterns or profiles
            derived from known protein families. The AMIDATION motif, for
            example, marks C-terminal amidation sites — a post-translational
            modification that activates many signalling peptides. You can
            look up any motif found here on the
	    <a href="https://prosite.expasy.org/cgi-bin/prosite/prosite_search_full.pl?SEARCH=<?php echo urlencode($m['motif']); ?>" target="_blank">
                PROSITE database
            </a>.
        </p>
    </div>

</div>

<footer>
    ProtExplorer &mdash; IWD2 assessed website &mdash;
    <a href="credits.php">credits &amp; AI usage</a>
</footer>

</body>
</html>
