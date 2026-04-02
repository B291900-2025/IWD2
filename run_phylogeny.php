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
$newick_path = $results_dir . "/run_{$run_id}_tree.nwk";
$plot_path   = $results_dir . "/run_{$run_id}_tree.png";
$plot_url    = "results/run_{$run_id}_tree.png";

// Check FASTA file exists before attempting tree construction
if (!file_exists($fasta_path)) {
    $_SESSION['search_error'] = "Sequence data not found for run $run_id. Please run a new search.";
    header('location: search.php');
    exit;
}

$error_msg = '';
$n_taxa    = 0;

// ── Run phylogeny if not already done ────────────────────────────
if (!file_exists($newick_path)) {
    $fasta_path  = $results_dir . "/run_{$run_id}_sequences.fasta";
    $script      = escapeshellarg(__DIR__ . '/scripts/run_phylogeny.py');
    $fasta_arg   = escapeshellarg($fasta_path);
    $rid_arg     = escapeshellarg($run_id);
    $results_arg = escapeshellarg($results_dir);

    $command = "python3 $script $fasta_arg $rid_arg $results_arg 2>&1";
    $output  = shell_exec($command);

    if ($output !== null && strpos(trim($output), 'SUCCESS:') === 0) {
        $n_taxa = intval(str_replace('SUCCESS:', '', trim($output)));

        // Store in Results table via PDO
        $stmt_res = $pdo->prepare(
            "INSERT INTO Results (run_id, result_type, file_path, summary)
             VALUES (:run_id, 'phylogeny', :path, :summary)"
        );
        $stmt_res->execute([
            ':run_id'  => $run_id,
            ':path'    => $plot_url,
            ':summary' => "Phylogenetic tree with $n_taxa taxa"
        ]);
    } else {
        $error_msg = htmlspecialchars(trim($output));
    }
}

// ── Read Newick string for interactive tree ───────────────────────
$newick_str = '';
if (file_exists($newick_path)) {
    // Collapse to single line for JavaScript
    $newick_str = preg_replace('/\s+/', '', file_get_contents($newick_path));
}

$active_page = 'search';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProtExplorer &mdash; phylogenetic tree</title>
    <link rel="stylesheet" href="style.css">

    <!-- Phylotree.js dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.8.5/d3.min.js"></script>

    <style>
        #tree_container {
            width: 100%;
            overflow-x: auto;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem;
            min-height: 400px;
        }
        #tree_container svg {
            width: 100%;
        }
        .phylotree-node-text {
            font-size: 11px;
            font-family: Arial, sans-serif;
            fill: #2d2a3e;
        }
        .phylotree-edge {
            stroke: #7c6fcd;
            stroke-width: 1.5px;
            fill: none;
        }
    </style>
</head>
<body>

<?php require_once 'menuf.php'; ?>

<div class="page-wrap">

    <div class="page-header" style="margin-top:2rem;">
        <h1>phylogenetic tree</h1>
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
            Phylogeny failed: <?php echo $error_msg; ?>
        </div>

    <?php elseif ($newick_str !== ''): ?>

        <div class="card">
            <h2>about this tree</h2>
            <p style="font-size:0.88rem;">
                This tree was constructed using Clustal Omega's guide tree,
                built from pairwise distances between the aligned sequences.
                Branch lengths represent the estimated evolutionary distance
                between sequences. Sequences that cluster together share
                greater similarity and are likely more closely related.
                The tree is unrooted — no outgroup was specified.
            </p>
        </div>

        <!-- Interactive tree -->
        <div class="card">
            <h2>interactive tree</h2>
            <p style="font-size:0.88rem; color:var(--text-muted);
                      margin-bottom:0.75rem;">
                Click on any node or branch to highlight it.
                Use the controls below to adjust the layout.
            </p>

            <div style="margin-bottom:0.75rem; display:flex; gap:0.5rem; flex-wrap:wrap;">
                <button class="btn btn-outline"
                        style="font-size:0.82rem; padding:0.35rem 0.8rem;"
                        onclick="toggleLayout()">
                    toggle layout
                </button>
                <button class="btn btn-outline"
                        style="font-size:0.82rem; padding:0.35rem 0.8rem;"
                        onclick="collapseAll()">
                    collapse all
                </button>
                <button class="btn btn-outline"
                        style="font-size:0.82rem; padding:0.35rem 0.8rem;"
                        onclick="expandAll()">
                    expand all
                </button>
            </div>

            <div id="tree_container"></div>
        </div>

        <!-- Static tree image -->
        <div class="card">
            <h2>static tree image</h2>
            <p style="font-size:0.88rem; color:var(--text-muted);
                      margin-bottom:0.75rem;">
                A simplified cladogram showing the grouping of sequences.
                Right-click to save the image.
            </p>
            <img src="<?php echo $plot_url; ?>"
                 alt="Phylogenetic tree" class="result-plot">
        </div>

        <script>
    var newickStr = <?php echo json_encode($newick_str); ?>;

    // ── Parse Newick string into a tree structure ─────────────────
    function parseNewick(s) {
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
                case ':':
                    break;
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

    function drawLinear(root, width, height) {
        var container  = document.getElementById('tree_container');
        var treeLayout = d3.tree().size([height - 40, width - 200]);
        treeLayout(root);

        var svg = d3.select('#tree_container')
            .append('svg')
            .attr('width',  width)
            .attr('height', height);

        var g = svg.append('g')
                   .attr('transform', 'translate(20, 20)');

        g.selectAll('.link')
         .data(root.links())
         .enter()
         .append('path')
         .attr('fill',   'none')
         .attr('stroke', '#7c6fcd')
         .attr('stroke-width', 1.5)
         .attr('d', d3.linkHorizontal()
             .x(function(d) { return d.y; })
             .y(function(d) { return d.x; })
         );

        var node = g.selectAll('.node')
            .data(root.descendants())
            .enter()
            .append('g')
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
                    d.children  = d._children;
                    d._children = null;
                } else if (d.children) {
                    d._children = d.children;
                    d.children  = null;
                }
                container.innerHTML = '';
                drawLinear(root, width, height);
            });

        node.filter(function(d) { return !d.children && !d._children; })
            .append('text')
            .attr('x', 8)
            .attr('dy', '0.31em')
            .style('font-size', '11px')
            .style('font-family', 'Arial, sans-serif')
            .style('fill', '#2d2a3e')
            .text(function(d) {
                return d.data.name ? d.data.name.replace(/_/g, ' ') : '';
            });

        var zoom = d3.zoom()
            .scaleExtent([0.3, 3])
            .on('zoom', function(event) {
                g.attr('transform', event.transform);
            });

        svg.call(zoom);
    }

    document.addEventListener('DOMContentLoaded', function() {
        var container = document.getElementById('tree_container');
        var width     = container.clientWidth || 700;
        var height    = Math.max(400, width * 0.8);

        var root = d3.hierarchy(parseNewick(newickStr),
                                function(d) { return d.children; });
        drawLinear(root, width, height);
    });

    function toggleLayout() {
        // Re-render as radial
        var container = document.getElementById('tree_container');
        container.innerHTML = '';
        var width  = container.clientWidth || 700;
        var radius = Math.min(width, 600) / 2;

        var svg = d3.select('#tree_container')
            .append('svg')
            .attr('width',  width)
            .attr('height', radius * 2 + 40);

        var g = svg.append('g')
                   .attr('transform',
                         'translate(' + (width/2) + ',' + (radius + 20) + ')');

        var root       = d3.hierarchy(parseNewick(newickStr),
                                      function(d) { return d.children; });
        var treeLayout = d3.tree()
                           .size([2 * Math.PI, radius - 80])
                           .separation(function(a, b) {
                               return (a.parent == b.parent ? 1 : 2) / a.depth;
                           });
        treeLayout(root);

        g.selectAll('.link')
         .data(root.links())
         .enter()
         .append('path')
         .attr('fill',   'none')
         .attr('stroke', '#7c6fcd')
         .attr('stroke-width', 1.5)
         .attr('d', d3.linkRadial()
             .angle(function(d)  { return d.x; })
             .radius(function(d) { return d.y; })
         );

        var node = g.selectAll('.node')
            .data(root.descendants())
            .enter()
            .append('g')
            .attr('transform', function(d) {
                return 'rotate(' + (d.x * 180 / Math.PI - 90) + ')'
                     + 'translate(' + d.y + ',0)';
            });

        node.append('circle')
            .attr('r', 4)
            .attr('fill', function(d) {
                return d.children ? '#7c6fcd' : '#e07b6a';
            });

        node.filter(function(d) { return !d.children; })
            .append('text')
            .attr('dy', '0.31em')
            .attr('x', function(d) {
                return d.x < Math.PI === !d.children ? 6 : -6;
            })
            .attr('text-anchor', function(d) {
                return d.x < Math.PI === !d.children ? 'start' : 'end';
            })
            .attr('transform', function(d) {
                return d.x >= Math.PI ? 'rotate(180)' : null;
            })
            .style('font-size', '10px')
            .style('font-family', 'Arial, sans-serif')
            .style('fill', '#2d2a3e')
            .text(function(d) {
                return d.data.name ? d.data.name.replace(/_/g, ' ') : '';
            });

        var zoom = d3.zoom()
            .scaleExtent([0.3, 3])
            .on('zoom', function(event) {
                g.attr('transform', event.transform);
            });

        svg.call(zoom);
    }

    
    function collapseAll() {
        var container = document.getElementById('tree_container');
        var svg       = d3.select('#tree_container svg');
        var g         = svg.select('g');

        var root = d3.hierarchy(parseNewick(newickStr),
                                function(d) { return d.children; });
        var width  = container.clientWidth || 700;
        var height = Math.max(400, width * 0.8);

        var treeLayout = d3.tree().size([height - 40, width - 200]);
        treeLayout(root);

        // Collapse all internal nodes — only show direct children of root
        root.descendants().forEach(function(d) {
            if (d.depth > 0 && d.children) {
                d._children = d.children;
                d.children  = null;
            }
        });

        // Redraw
        container.innerHTML = '';
        drawLinear(root, width, height);
    }

    function expandAll() {
        var container = document.getElementById('tree_container');
        var width     = container.clientWidth || 700;
        var height    = Math.max(400, width * 0.8);

        var root = d3.hierarchy(parseNewick(newickStr),
                                function(d) { return d.children; });
        var treeLayout = d3.tree().size([height - 40, width - 200]);
        treeLayout(root);

        container.innerHTML = '';
        drawLinear(root, width, height);
    }
    </script>

    <?php else: ?>
        <div class="alert alert-error">
            Tree file not found. Please try again.
        </div>
    <?php endif; ?>

</div>

<footer>
    ProtExplorer &mdash; IWD2 assessed website &mdash;
    <a href="credits.php">credits &amp; AI usage</a>
</footer>

</body>
</html>
