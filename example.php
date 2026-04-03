<?php
session_start();
require_once 'login.php';

$pdo = get_pdo();

// ── Load example sequences from database ─────────────────────────
$stmt = $pdo->prepare(
    "SELECT id, accession, species, seq_length, sequence
     FROM ExampleDataset ORDER BY id ASC"
);
$stmt->execute();
$sequences = $stmt->fetchAll();

// ── Load pre-computed stats ───────────────────────────────────────
$stats      = [];
$stats_path = __DIR__ . '/results/example_stats.txt';

if (file_exists($stats_path)) {
    foreach (file($stats_path) as $line) {
        $line = trim($line);
        if ($line === '') continue;
        list($key, $val) = explode('=', $line, 2);
        $stats[$key] = $val;
    }
}

// ── Load pre-computed motifs ──────────────────────────────────────
$motif_data    = [];
$no_motif_seqs = [];
$motif_path    = __DIR__ . '/results/example_motifs.txt';

if (file_exists($motif_path)) {
    foreach (file($motif_path) as $line) {
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
                'length'    => $parts[6] ?? ''
            ];
        } elseif ($parts[0] === 'NOMOTIFS') {
            $no_motif_seqs[] = [
                'accession' => $parts[1] ?? '',
                'species'   => $parts[2] ?? ''
            ];
        }
    }
}

// ── Load aligned sequences ────────────────────────────────────────
$aligned_seqs = [];
$aligned_path = __DIR__ . '/results/example_aligned.fasta';

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

// ── Load Newick string ────────────────────────────────────────────
$newick_str = '';
$newick_path = __DIR__ . '/results/example_tree.nwk';
if (file_exists($newick_path)) {
    $newick_str = preg_replace('/\s+/', '', file_get_contents($newick_path));
}

$active_page = 'example';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProtExplorer &mdash; example dataset</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.8.5/d3.min.js"></script>
    <style>
        .section-intro {
            font-size: 0.92rem;
            color: var(--text);
            line-height: 1.75;
            margin-bottom: 1rem;
        }
        .section-intro a {
            color: var(--primary);
            text-decoration: none;
            border-bottom: 1px dotted var(--primary);
        }
        .section-intro a:hover { text-decoration: none; border-bottom-style: solid; }
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
        .seq-hidden { display: none; margin-top: 0.5rem; }
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
        #tree_container_ex {
            width: 100%;
            overflow-x: auto;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem;
            min-height: 400px;
        }
        .info-box {
            background: var(--primary-light);
            border-left: 4px solid var(--primary);
            border-radius: 0 var(--radius) var(--radius) 0;
            padding: 0.85rem 1rem;
            margin: 1rem 0;
            font-size: 0.88rem;
            color: var(--primary-dark);
        }
        .ext-link::after {
            content: ' \2197';
            font-size: 0.75rem;
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
        <h1>Example Dataset</h1>
        <p>Glucose-6-phosphatase in Aves &mdash; a pre-processed walkthrough</p>
    </div>

    <!-- ── Biological introduction ──────────────────────────────── -->
    <div class="card">
        <h2>biological background</h2>
        <p class="section-intro">
            <strong>Glucose-6-phosphatase (G6Pase)</strong> is a key enzyme in
            glucose homeostasis, catalysing the final step of both gluconeogenesis
            and glycogenolysis — the hydrolysis of glucose-6-phosphate to free
            glucose and inorganic phosphate. This reaction takes place in the
            endoplasmic reticulum and is essential for maintaining blood glucose
            levels between meals.
            <a href="https://www.uniprot.org/uniprotkb?query=glucose-6-phosphatase"
               target="_blank" class="ext-link">
                View G6Pase entries on UniProt
            </a>
        </p>
        <p class="section-intro">
            In birds (<strong>Aves</strong>), glucose metabolism is particularly
            interesting because avian blood glucose levels are typically 1.5&ndash;2
            times higher than in mammals of equivalent body mass, yet birds rarely
            develop diabetes-like pathologies. This suggests that avian G6Pase
            regulation may differ meaningfully from mammalian counterparts, making
            it a compelling target for comparative sequence analysis.
            <a href="https://pubmed.ncbi.nlm.nih.gov/11522829/"
               target="_blank" class="ext-link">
                Read more about avian glucose metabolism (PubMed)
            </a>
        </p>
        <p class="section-intro">
            This example dataset contains
            <strong><?php echo count($sequences); ?> G6Pase protein sequences</strong>
            retrieved from
            <a href="https://www.ncbi.nlm.nih.gov/protein/?term=glucose-6-phosphatase+Aves"
               target="_blank" class="ext-link">NCBI Protein</a>
            spanning <?php echo count($sequences); ?> avian species.
            All analyses shown below were pre-computed and are displayed
            here to illustrate the full functionality of ProtExplorer.
            Use the
            <a href="search.php">search page</a>
            to run the same analyses on any protein family and taxonomic group
            of your choice.
        </p>

        <div class="info-box">
            <strong>How to read this page:</strong> each section below corresponds
            to one analysis step. Click the section headings or scroll through to
            explore the results. Links marked with &nearr; open external resources
            in a new tab.
        </div>
    </div>

    <!-- ── Run summary ──────────────────────────────────────────── -->
    <div class="card">
        <h2>dataset summary</h2>
        <table>
            <tr><th>parameter</th><th>value</th></tr>
            <tr>
                <td>protein family</td>
                <td>Glucose-6-phosphatase</td>
            </tr>
            <tr>
                <td>taxonomic group</td>
                <td>
                    Aves (birds)
                    <a href="https://www.ncbi.nlm.nih.gov/Taxonomy/Browser/wwwtax.cgi?id=8782"
                       target="_blank" class="ext-link"
                       style="font-size:0.8rem;">NCBI taxonomy</a>
                </td>
            </tr>
            <tr>
                <td>sequences retrieved</td>
                <td><?php echo count($sequences); ?></td>
            </tr>
            <tr>
                <td>data source</td>
                <td>
                    <a href="https://www.ncbi.nlm.nih.gov/protein"
                       target="_blank" class="ext-link">NCBI Protein database</a>
                </td>
            </tr>
            <tr>
                <td>alignment tool</td>
                <td>
                    <a href="http://www.clustal.org/omega/"
                       target="_blank" class="ext-link">Clustal Omega</a>
                </td>
            </tr>
            <tr>
                <td>motif database</td>
                <td>
                    <a href="https://prosite.expasy.org/"
                       target="_blank" class="ext-link">PROSITE</a>
                    via EMBOSS patmatmotifs
                </td>
            </tr>
        </table>
    </div>

    <!-- ── Sequences ────────────────────────────────────────────── -->
    <div class="card">
        <h2>retrieved sequences (<?php echo count($sequences); ?>)</h2>
        <p class="section-intro">
            Each sequence below was retrieved from the NCBI Protein database.
            The accession number links directly to the NCBI entry for that
            sequence. Sequence lengths are given in amino acids (aa).
            Click "show sequence" to view the full protein sequence in
            FASTA format.
            <a href="https://www.ncbi.nlm.nih.gov/protein/?term=glucose-6-phosphatase+Aves"
               target="_blank" class="ext-link">
                Browse all G6Pase Aves sequences on NCBI
            </a>
        </p>
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
                        <?php echo htmlspecialchars(
                            wordwrap($seq['sequence'], 60, "\n", true)
                        ); ?>
                    </div>
                </td>
            </tr>
            <?php $i++; endforeach; ?>
        </table>
    </div>

    <!-- ── Conservation analysis ────────────────────────────────── -->
    <div class="card">
        <h2>conservation analysis</h2>
        <p class="section-intro">
            Multiple sequence alignment was performed using
            <a href="http://www.clustal.org/omega/" target="_blank"
               class="ext-link">Clustal Omega</a>,
            a widely used tool for aligning protein sequences.
            Conservation at each position is measured as the fraction
            of sequences sharing the most common amino acid at that position.
            A score of 1.0 means all sequences have the same amino acid —
            indicating strong evolutionary constraint and likely functional
            importance. Lower scores indicate variable positions that may
            tolerate substitution.
            <a href="https://www.ebi.ac.uk/Tools/msa/clustalo/"
               target="_blank" class="ext-link">
                Learn more about multiple sequence alignment
            </a>
        </p>

        <?php if (!empty($stats)): ?>
        <div class="stats-grid">
            <div class="stat-box">
                <span class="stat-value">
                    <?php echo number_format(floatval($stats['mean_cons']) * 100, 1); ?>%
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
                    <?php echo $stats['n_sequences']; ?>
                </span>
                <span class="stat-label">sequences aligned</span>
            </div>
        </div>

        <div class="card" style="background:var(--bg);">
            <h2>additional statistics</h2>
            <table>
                <tr><th>statistic</th><th>value</th><th>biological meaning</th></tr>
                <tr>
                    <td>mean conservation</td>
                    <td><?php echo number_format(floatval($stats['mean_cons']), 4); ?></td>
                    <td>
                        <?php if (floatval($stats['mean_cons']) > 0.8): ?>
                            High overall conservation — suggests strong
                            functional constraint across Aves
                        <?php elseif (floatval($stats['mean_cons']) > 0.6): ?>
                            Moderate conservation — some functional
                            flexibility across species
                        <?php else: ?>
                            Lower conservation — significant sequence
                            divergence across the taxonomic group
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>fully conserved positions</td>
                    <td><?php echo $stats['fully_cons']; ?></td>
                    <td>
                        Positions identical across all species — likely
                        critical for enzyme structure or catalysis
                    </td>
                </tr>
                <tr>
                    <td>highly conserved positions (&ge;0.8)</td>
                    <td><?php echo $stats['highly_cons']; ?></td>
                    <td>
                        Positions conserved in &ge;80% of sequences —
                        likely important but tolerating rare substitutions
                    </td>
                </tr>
                <tr>
                    <td>most conserved position</td>
                    <td>position <?php echo $stats['most_cons_pos']; ?></td>
                    <td>
                        The single most conserved alignment position —
                        a candidate for functional site investigation
                    </td>
                </tr>
                <tr>
                    <td>least conserved position</td>
                    <td>position <?php echo $stats['least_cons_pos']; ?></td>
                    <td>
                        Highest variability — may reflect species-specific
                        adaptation or a structurally tolerant region
                    </td>
                </tr>
            </table>
        </div>
        <?php endif; ?>

        <?php if (file_exists(__DIR__ . '/results/example_conservation.png')): ?>
        <p style="font-size:0.88rem; color:var(--text-muted); margin-bottom:0.75rem;">
            Each bar represents one alignment position. Bar height is the
            conservation score (0&ndash;1). The dashed orange line shows the
            mean score. Colours indicate conservation level — dark purple
            bars are fully conserved positions.
        </p>
        <img src="results/example_conservation.png"
             alt="Conservation plot" class="result-plot">
        <?php endif; ?>

        <!-- Aligned sequences -->
        <?php if (!empty($aligned_seqs)): ?>
        <div style="margin-top:1.5rem;">
            <p style="font-size:0.88rem; font-weight:700;
                      color:var(--text); margin-bottom:0.5rem;">
                aligned sequences
            </p>
            <p class="section-intro">
                Dashes represent gap characters introduced by the alignment
                algorithm to maximise positional correspondence between sequences.
                Regions with many gaps indicate insertions or deletions
                (indels) specific to some lineages.
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
    </div>

    <!-- ── Motif scan ────────────────────────────────────────────── -->
    <div class="card">
        <h2>PROSITE motif scan</h2>
        <p class="section-intro">
            Each sequence was scanned against the
            <a href="https://prosite.expasy.org/" target="_blank"
               class="ext-link">PROSITE database</a>
            using the EMBOSS
            <a href="https://emboss.sourceforge.net/apps/cvs/emboss/apps/patmatmotifs.html"
               target="_blank" class="ext-link">patmatmotifs</a>
            tool. PROSITE contains curated patterns and profiles describing
            protein domains, families and functional sites. A motif hit
            indicates that the sequence contains a subsequence matching
            a known PROSITE pattern, suggesting the presence of a
            functionally important region.
        </p>

        <div class="info-box">
            <?php
            $motif_count  = count($motif_data);
            $unique_motifs = array_unique(array_column($motif_data, 'motif'));
            ?>
            <strong><?php echo $motif_count; ?> motif hits</strong>
            found across <?php echo count($sequences); ?> sequences,
            involving <strong><?php echo count($unique_motifs); ?></strong>
            unique PROSITE motif(s):
            <?php foreach ($unique_motifs as $um): ?>
                <span class="motif-badge"><?php echo htmlspecialchars($um); ?></span>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($motif_data)): ?>
        <table>
            <tr>
                <th>accession</th>
                <th>species</th>
                <th>motif</th>
                <th>start</th>
                <th>end</th>
                <th>length</th>
                <th>PROSITE entry</th>
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
                <td><em><?php echo htmlspecialchars($m['species']); ?></em></td>
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
                               target="_blank" class="ext-link">
                                search on PROSITE
                            </a>
                        </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <?php if (!empty($no_motif_seqs)): ?>
        <p style="margin-top:1rem; font-size:0.85rem; color:var(--text-muted);">
            Sequences with no motifs detected:
            <?php foreach ($no_motif_seqs as $nm): ?>
                <em><?php echo htmlspecialchars($nm['accession']); ?></em>
                (<?php echo htmlspecialchars($nm['species']); ?>)<?php
                echo ($nm !== end($no_motif_seqs)) ? ', ' : '.'; ?>
            <?php endforeach; ?>
        </p>
        <?php endif; ?>

        <?php endif; ?>
    </div>

    <!-- ── Phylogenetic tree ─────────────────────────────────────── -->
    <div class="card">
        <h2>phylogenetic tree</h2>
        <p class="section-intro">
            A guide tree was constructed from the Clustal Omega alignment
            using neighbour-joining distances. The tree illustrates the
            inferred evolutionary relationships between the G6Pase sequences
            across Aves. Sequences on shorter branches are more similar to
            each other; longer branches indicate greater sequence divergence.
            Note that this is a guide tree rather than a rigorously
            inferred phylogeny — for publication-quality trees, dedicated
            tools such as
            <a href="https://www.iqtree.org/" target="_blank"
               class="ext-link">IQ-TREE</a>
            or
            <a href="https://mafft.cbrc.jp/alignment/software/"
               target="_blank" class="ext-link">MAFFT</a>
            + RAxML would be more appropriate.
            <a href="https://www.nature.com/articles/nrg3186"
               target="_blank" class="ext-link">
                Learn more about phylogenetic inference (Nature Reviews)
            </a>
        </p>

        <!-- Interactive tree -->
        <?php if ($newick_str !== ''): ?>
        <p style="font-size:0.88rem; color:var(--text-muted);
                  margin-bottom:0.75rem;">
            Interactive tree — click any node to collapse or expand branches.
            Use scroll to zoom and drag to pan.
        </p>
        <div style="margin-bottom:0.75rem; display:flex; gap:0.5rem;">
            <button class="btn btn-outline"
                    style="font-size:0.82rem; padding:0.35rem 0.8rem;"
                    onclick="toggleLayoutEx()">toggle layout</button>
            <button class="btn btn-outline"
                    style="font-size:0.82rem; padding:0.35rem 0.8rem;"
                    onclick="expandAllEx()">expand all</button>
        </div>
        <div id="tree_container_ex"></div>
        <?php endif; ?>

        <!-- Static tree -->
        <?php if (file_exists(__DIR__ . '/results/example_tree.png')): ?>
        <p style="font-size:0.88rem; color:var(--text-muted);
                  margin-top:1.5rem; margin-bottom:0.5rem;">
            Static cladogram — right-click to save.
        </p>
        <img src="results/example_tree.png"
             alt="Phylogenetic tree" class="result-plot">
        <?php endif; ?>
    </div>

    <!-- ── Try it yourself ──────────────────────────────────────── -->
    <div class="card" style="text-align:center;">
        <h2>try it yourself</h2>
        <p class="section-intro" style="margin-bottom:1rem;">
            All of the analyses shown above can be run on any protein
            family and taxonomic group of your choice.
            Head to the search page to get started.
        </p>
        <a href="search.php" class="btn btn-primary">run your own search</a>
    </div>

</div>

<footer>
    ProtExplorer &mdash; IWD2 assessed website &mdash;
    <a href="credits.php">credits &amp; AI usage</a>
</footer>

<?php if ($newick_str !== ''): ?>
<script>
var newickStrEx = <?php echo json_encode($newick_str); ?>;

function parseNewickEx(s) {
    var ancestors = [];
    var tree      = {};
    var tokens    = s.split(/\s*(;|\(|\)|,|:)\s*/);
    for (var i = 0; i < tokens.length; i++) {
        var token = tokens[i];
        switch (token) {
            case '(':
                var subtree = {};
                tree.children = tree.children || [];
                tree.children.push(subtree);
                ancestors.push(tree);
                tree = subtree;
                break;
            case ',':
                var subtree = {};
                ancestors[ancestors.length-1].children =
                    ancestors[ancestors.length-1].children || [];
                ancestors[ancestors.length-1].children.push(subtree);
                tree = subtree;
                break;
            case ')':
                tree = ancestors.pop();
                break;
            case ':': break;
            default:
                var x = tokens[i-1];
                if (x === ')' || x === '(' || x === ',') {
                    tree.name = token;
                } else if (x === ':') {
                    tree.length = parseFloat(token);
                }
        }
    }
    return tree;
}

function drawLinearEx(root, width, height) {
    var container  = document.getElementById('tree_container_ex');
    var treeLayout = d3.tree().size([height - 40, width - 220]);
    treeLayout(root);

    var svg = d3.select('#tree_container_ex')
        .append('svg')
        .attr('width',  width)
        .attr('height', height);

    var g = svg.append('g').attr('transform', 'translate(20,20)');

    g.selectAll('.link')
     .data(root.links())
     .enter().append('path')
     .attr('fill', 'none')
     .attr('stroke', '#7c6fcd')
     .attr('stroke-width', 1.5)
     .attr('d', d3.linkHorizontal()
         .x(function(d) { return d.y; })
         .y(function(d) { return d.x; })
     );

    var node = g.selectAll('.node')
        .data(root.descendants())
        .enter().append('g')
        .attr('transform', function(d) {
            return 'translate(' + d.y + ',' + d.x + ')';
        });

    node.append('circle')
        .attr('r', 4)
        .attr('fill', function(d) {
            return d.children ? '#7c6fcd' : '#e07b6a';
        })
        .style('cursor', 'pointer')
        .on('click', function(event, d) {
            if (d._children) {
                d.children = d._children; d._children = null;
            } else if (d.children) {
                d._children = d.children; d.children = null;
            }
            container.innerHTML = '';
            drawLinearEx(root, width, height);
        });

    node.filter(function(d) { return !d.children && !d._children; })
        .append('text')
        .attr('x', 8).attr('dy', '0.31em')
        .style('font-size', '11px')
        .style('font-family', 'Arial, sans-serif')
        .style('fill', '#2d2a3e')
        .text(function(d) {
            return d.data.name ? d.data.name.replace(/_/g, ' ') : '';
        });

    svg.call(d3.zoom().scaleExtent([0.3, 3]).on('zoom', function(event) {
        g.attr('transform', event.transform);
    }));
}

var currentLayoutEx = 'linear';

function toggleLayoutEx() {
    var container = document.getElementById('tree_container_ex');
    container.innerHTML = '';
    var width  = container.clientWidth || 700;

    if (currentLayoutEx === 'linear') {
        currentLayoutEx = 'radial';
        var radius = Math.min(width, 600) / 2;
        var svg = d3.select('#tree_container_ex')
            .append('svg')
            .attr('width', width)
            .attr('height', radius * 2 + 40);
        var g = svg.append('g')
            .attr('transform', 'translate(' + (width/2) + ',' + (radius+20) + ')');
        var root = d3.hierarchy(parseNewickEx(newickStrEx),
                                function(d) { return d.children; });
        var treeLayout = d3.tree()
            .size([2 * Math.PI, radius - 80])
            .separation(function(a, b) {
                return (a.parent == b.parent ? 1 : 2) / a.depth;
            });
        treeLayout(root);
        g.selectAll('.link').data(root.links()).enter().append('path')
         .attr('fill', 'none').attr('stroke', '#7c6fcd').attr('stroke-width', 1.5)
         .attr('d', d3.linkRadial()
             .angle(function(d) { return d.x; })
             .radius(function(d) { return d.y; })
         );
        var node = g.selectAll('.node').data(root.descendants()).enter().append('g')
            .attr('transform', function(d) {
                return 'rotate(' + (d.x * 180 / Math.PI - 90) + ')'
                     + 'translate(' + d.y + ',0)';
            });
        node.append('circle').attr('r', 4)
            .attr('fill', function(d) { return d.children ? '#7c6fcd' : '#e07b6a'; });
        node.filter(function(d) { return !d.children; })
            .append('text')
            .attr('dy', '0.31em')
            .attr('x', function(d) { return d.x < Math.PI === !d.children ? 6 : -6; })
            .attr('text-anchor', function(d) {
                return d.x < Math.PI === !d.children ? 'start' : 'end';
            })
            .attr('transform', function(d) {
                return d.x >= Math.PI ? 'rotate(180)' : null;
            })
            .style('font-size', '10px').style('font-family', 'Arial, sans-serif')
            .style('fill', '#2d2a3e')
            .text(function(d) {
                return d.data.name ? d.data.name.replace(/_/g, ' ') : '';
            });
        svg.call(d3.zoom().scaleExtent([0.3,3]).on('zoom', function(event) {
            g.attr('transform', event.transform);
        }));
    } else {
        currentLayoutEx = 'linear';
        var height = Math.max(400, width * 0.8);
        var root = d3.hierarchy(parseNewickEx(newickStrEx),
                                function(d) { return d.children; });
        drawLinearEx(root, width, height);
    }
}

function expandAllEx() {
    var container = document.getElementById('tree_container_ex');
    container.innerHTML = '';
    var width  = container.clientWidth || 700;
    var height = Math.max(400, width * 0.8);
    var root   = d3.hierarchy(parseNewickEx(newickStrEx),
                              function(d) { return d.children; });
    drawLinearEx(root, width, height);
}

document.addEventListener('DOMContentLoaded', function() {
    var container = document.getElementById('tree_container_ex');
    var width     = container.clientWidth || 700;
    var height    = Math.max(400, width * 0.8);
    var root      = d3.hierarchy(parseNewickEx(newickStrEx),
                                 function(d) { return d.children; });
    drawLinearEx(root, width, height);
});
</script>
<?php endif; ?>

</body>
</html>
```
