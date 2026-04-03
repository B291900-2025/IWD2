<?php
session_start();
$active_page = 'about';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProtExplorer &mdash; about</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .about-section {
            margin-bottom: 1.5rem;
        }
        .about-section h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 0.6rem;
            padding-bottom: 0.3rem;
            border-bottom: 2px solid var(--primary-light);
        }
        .about-section p {
            font-size: 0.92rem;
            line-height: 1.8;
            margin-bottom: 0.75rem;
            color: var(--text);
        }
        .about-section a {
            color: var(--primary);
            text-decoration: none;
            border-bottom: 1px dotted var(--primary);
        }
        .about-section a:hover { border-bottom-style: solid; }
        .arch-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .arch-box {
            background: var(--primary-light);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem;
        }
        .arch-box h4 {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
        }
        .arch-box ul {
            margin: 0;
            padding-left: 1.2rem;
            font-size: 0.85rem;
            color: var(--text);
            line-height: 1.8;
        }
        .flow-table td {
            vertical-align: top;
            font-size: 0.88rem;
            padding: 0.6rem 0.9rem;
        }
        .flow-table th {
            font-size: 0.82rem;
        }
        .info-box {
            background: var(--primary-light);
            border-left: 4px solid var(--primary);
            border-radius: 0 var(--radius) var(--radius) 0;
            padding: 0.85rem 1rem;
            margin: 0.75rem 0;
            font-size: 0.88rem;
            color: var(--primary-dark);
            line-height: 1.7;
        }
        .decision-item {
            border-bottom: 1px solid var(--border);
            padding: 0.85rem 0;
        }
        .decision-item:last-child { border-bottom: none; }
        .decision-q {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--primary-dark);
            margin-bottom: 0.3rem;
        }
        .decision-a {
            font-size: 0.88rem;
            color: var(--text);
            line-height: 1.7;
        }
    </style>
</head>
<body>

<?php require_once 'menuf.php'; ?>

<div class="page-wrap">

    <div class="page-header" style="margin-top:2rem;">
        <h1>About ProtExplorer</h1>
        <p>Implementation overview for developers &mdash; no code examples</p>
    </div>

    <div class="info-box">
        This page describes how ProtExplorer is built and the design
        decisions behind it. It is aimed at a developer audience. For
        biological context and usage guidance, see the
        <a href="help.php">help page</a>.
        For full attribution of all tools and code sources used, see
        the <a href="credits.php">credits page</a>.
    </div>

    <!-- ── Overview ─────────────────────────────────────────────── -->
    <div class="card">
        <h2>overview</h2>

        <div class="about-section">
            <p>
                ProtExplorer is a LAMP stack web application running on
                the University of Edinburgh bioinfmsc8 server. It provides
                an automated comparative protein sequence analysis pipeline
                accessible through a browser-based interface. The application
                follows a strict separation between database interaction
                (PHP/PDO only) and analysis computation (Python only),
                in line with the course specification.
            </p>
            <p>
                The application is structured around six core missions
                defined in the assessment brief: sequence retrieval,
                conservation analysis, motif scanning, additional
                analyses (phylogeny and structure links), a pre-processed
                example dataset, and session-based result history.
            </p>
        </div>

        <div class="arch-grid">
            <div class="arch-box">
                <h4>server stack</h4>
                <ul>
                    <li>Ubuntu Linux (bioinfmsc8)</li>
                    <li>Apache 2 web server (HTTPS)</li>
                    <li>PHP 8.2 (server-side logic)</li>
                    <li>MySQL 8.0 (data persistence)</li>
                    <li>Python 3.12 (analysis pipeline)</li>
                </ul>
            </div>
            <div class="arch-box">
                <h4>client side</h4>
                <ul>
                    <li>HTML5 / CSS3</li>
                    <li>Vanilla JavaScript (DOM manipulation)</li>
                    <li>D3.js v7 (phylogenetic tree rendering)</li>
                    <li>No frameworks (no jQuery, React etc.)</li>
                    <li>Single external CSS stylesheet</li>
                </ul>
            </div>
            <div class="arch-box">
                <h4>bioinformatics tools</h4>
                <ul>
                    <li>BioPython Bio.Entrez (NCBI API access)</li>
                    <li>Clustal Omega (alignment + guide tree)</li>
                    <li>EMBOSS patmatmotifs (motif scanning)</li>
                    <li>matplotlib + NumPy (plot generation)</li>
                    <li>AlphaFold / UniProt REST APIs</li>
                </ul>
            </div>
            <div class="arch-box">
                <h4>version control</h4>
                <ul>
                    <li>Git (local version control)</li>
                    <li>GitHub repository:
                        <a href="https://github.com/B291900-2025/IWD2"
                           target="_blank">B291900-2025/IWD2</a>
                    </li>
                    <li>Commits after each working feature</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- ── Database design ──────────────────────────────────────── -->
    <div class="card">
        <h2>database design</h2>

        <div class="about-section">
            <h3>schema overview</h3>
            <p>
                The database <code>s2793337_website</code> contains four
                tables designed to support both live analysis runs and
                the pre-loaded example dataset.
            </p>
            <table class="flow-table">
                <tr>
                    <th>table</th>
                    <th>purpose</th>
                    <th>key columns</th>
                </tr>
                <tr>
                    <td><code>Runs</code></td>
                    <td>
                        One row per user search. Tracks protein family,
                        taxon, status and sequence count. The
                        <code>session_token</code> links runs to browser
                        sessions for history retrieval.
                    </td>
                    <td>
                        id (PK), session_token, protein_family,
                        taxon, status, num_sequences, created_at
                    </td>
                </tr>
                <tr>
                    <td><code>Sequences</code></td>
                    <td>
                        One row per fetched sequence, linked to a Run
                        via a foreign key. Stores the full sequence for
                        display and downstream use.
                    </td>
                    <td>
                        id (PK), run_id (FK), accession, species,
                        sequence, seq_length
                    </td>
                </tr>
                <tr>
                    <td><code>Results</code></td>
                    <td>
                        One row per analysis output file, linked to a
                        Run. Stores the file path and a plain-text
                        summary for each completed analysis.
                    </td>
                    <td>
                        id (PK), run_id (FK), result_type, file_path,
                        summary, created_at
                    </td>
                </tr>
                <tr>
                    <td><code>ExampleDataset</code></td>
                    <td>
                        Pre-populated with 15 G6Pase sequences from Aves.
                        Completely independent of the live pipeline —
                        never written to during normal operation.
                    </td>
                    <td>
                        id (PK), accession, species, sequence,
                        seq_length, protein, taxon
                    </td>
                </tr>
            </table>
            <p style="margin-top:0.75rem;">
                Indexes are defined on <code>Runs.session_token</code>,
                <code>Sequences.run_id</code> and
                <code>Results.run_id</code> to speed up the most common
                queries (session history lookup and result retrieval
                by run ID).
            </p>
        </div>

        <div class="about-section">
            <h3>database interaction policy</h3>
            <p>
                All MySQL interactions use PHP PDO exclusively.
                Python scripts do not connect to the database.
                This is a deliberate architectural decision required
                by the assessment specification — PDO provides a
                consistent, secure, database-agnostic interface that
                generalises across different database backends.
                Prepared statements with named parameters are used
                throughout to prevent SQL injection.
            </p>
        </div>
    </div>

    <!-- ── Application flow ─────────────────────────────────────── -->
    <div class="card">
        <h2>application flow</h2>

        <div class="about-section">
            <h3>live search pipeline</h3>
            <p>
                The core pipeline follows an input &rarr; process &rarr;
                storage &rarr; output pattern. PHP acts as the
                orchestrator — it validates input, creates database
                records, invokes Python scripts via
                <code>shell_exec()</code>, reads output files, and
                stores results back to the database via PDO.
            </p>
            <table class="flow-table">
                <tr>
                    <th>stage</th>
                    <th>handled by</th>
                    <th>description</th>
                </tr>
                <tr>
                    <td>form submission</td>
                    <td>PHP (<code>search.php</code>)</td>
                    <td>
                        JavaScript validates input client-side;
                        PHP sanitises with <code>preg_replace</code>
                        and creates a Run record in the database.
                    </td>
                </tr>
                <tr>
                    <td>sequence fetch</td>
                    <td>Python (<code>fetch_sequences.py</code>)</td>
                    <td>
                        Bio.Entrez searches NCBI Protein and fetches
                        sequences in FASTA format. Writes a FASTA file
                        to <code>results/</code>. Prints
                        <code>SUCCESS:N</code> or
                        <code>ERROR:message</code> to stdout.
                    </td>
                </tr>
                <tr>
                    <td>database insert</td>
                    <td>PHP (<code>process_search.php</code>)</td>
                    <td>
                        PHP reads the FASTA file, parses headers and
                        sequences, inserts rows into
                        <code>Sequences</code> via PDO, and updates
                        the Run status to <code>complete</code>.
                    </td>
                </tr>
                <tr>
                    <td>conservation analysis</td>
                    <td>Python (<code>run_conservation.py</code>)</td>
                    <td>
                        Clustal Omega alignment via subprocess.
                        Per-position conservation calculated as modal
                        amino acid frequency. matplotlib bar chart
                        saved as PNG.
                    </td>
                </tr>
                <tr>
                    <td>motif scanning</td>
                    <td>Python (<code>run_motifs.py</code>)</td>
                    <td>
                        Each sequence written to a temp FASTA,
                        scanned with EMBOSS patmatmotifs, output parsed
                        and written to a pipe-delimited flat file.
                        Temp files cleaned up after each sequence.
                    </td>
                </tr>
                <tr>
                    <td>phylogenetic tree</td>
                    <td>
                        Python (<code>run_phylogeny.py</code>)
                        + JavaScript (D3.js)
                    </td>
                    <td>
                        Clustal Omega generates a Newick guide tree.
                        Python saves a static PNG. PHP passes the
                        Newick string to the browser as JSON;
                        D3.js renders an interactive tree client-side.
                    </td>
                </tr>
                <tr>
                    <td>structure lookup</td>
                    <td>Python (<code>run_structures.py</code>)</td>
                    <td>
                        NCBI GenBank records fetched for each accession
                        to find UniProt cross-references. AlphaFold API
                        queried per UniProt ID. Results written to a
                        pipe-delimited flat file.
                    </td>
                </tr>
                <tr>
                    <td>result storage</td>
                    <td>PHP (each analysis page)</td>
                    <td>
                        On successful completion, each analysis PHP page
                        inserts a row into <code>Results</code> via PDO
                        with the file path and a plain-text summary.
                    </td>
                </tr>
            </table>
        </div>

        <div class="about-section">
            <h3>session management</h3>
            <p>
                PHP sessions (<code>session_start()</code>) are used to
                carry state between pages. The current run ID is stored
                in <code>$_SESSION['current_run_id']</code> and an array
                of all runs in the session is stored in
                <code>$_SESSION['run_history']</code>. The history page
                reads this array and re-queries the database for the
                current status and available results for each run.
                A unique <code>session_token</code> is generated with
                <code>bin2hex(random_bytes(16))</code> for each run
                and stored in the database, allowing future extension
                to persistent cross-session history.
            </p>
        </div>

        <div class="about-section">
            <h3>example dataset</h3>
            <p>
                The example dataset was pre-generated by a one-off setup
                script (<code>scripts/setup_example.py</code>) which
                fetched 15 G6Pase sequences from Aves, ran the full
                analysis pipeline, and saved all output files to
                <code>results/example_*</code>. A separate SQL generation
                script populated the <code>ExampleDataset</code> table.
                The example page reads directly from these pre-generated
                files and the database table — it does not invoke
                any Python scripts at runtime.
            </p>
        </div>
    </div>

    <!-- ── File structure ───────────────────────────────────────── -->
    <div class="card">
        <h2>file structure</h2>

        <div class="about-section">
            <table class="flow-table">
                <tr><th>file / directory</th><th>purpose</th></tr>
                <tr>
                    <td><code>index.php</code></td>
                    <td>Landing page</td>
                </tr>
                <tr>
                    <td><code>login.php</code></td>
                    <td>Database credentials — included by all PHP pages</td>
                </tr>
                <tr>
                    <td><code>menuf.php</code></td>
                    <td>Shared navigation bar — included by all PHP pages</td>
                </tr>
                <tr>
                    <td><code>redir.php</code></td>
                    <td>Session guard — redirects if session not set</td>
                </tr>
                <tr>
                    <td><code>style.css</code></td>
                    <td>Global stylesheet — all visual styling</td>
                </tr>
                <tr>
                    <td><code>search.php</code></td>
                    <td>Search form with dropdown inputs and JS validation</td>
                </tr>
                <tr>
                    <td><code>process_search.php</code></td>
                    <td>
                        Form processor — creates Run record, calls Python,
                        parses FASTA, inserts Sequences via PDO
                    </td>
                </tr>
                <tr>
                    <td><code>results.php</code></td>
                    <td>
                        Results page — sequence table with expandable
                        sequences and analysis launch buttons
                    </td>
                </tr>
                <tr>
                    <td><code>run_conservation.php</code></td>
                    <td>
                        Calls conservation Python script, displays plot
                        and statistics
                    </td>
                </tr>
                <tr>
                    <td><code>run_motifs.php</code></td>
                    <td>Motif scan options form</td>
                </tr>
                <tr>
                    <td><code>process_motifs.php</code></td>
                    <td>
                        Motif scan processor — calls Python, parses
                        output, stores in session
                    </td>
                </tr>
                <tr>
                    <td><code>motif_results.php</code></td>
                    <td>
                        Displays motif results in combined or
                        per-sequence format
                    </td>
                </tr>
                <tr>
                    <td><code>run_phylogeny.php</code></td>
                    <td>
                        Calls phylogeny Python script, renders
                        interactive D3 tree and static PNG
                    </td>
                </tr>
                <tr>
                    <td><code>run_structures.php</code></td>
                    <td>
                        Calls structure Python script, displays
                        AlphaFold cards with PAE images
                    </td>
                </tr>
                <tr>
                    <td><code>example.php</code></td>
                    <td>
                        Pre-processed G6Pase/Aves example with
                        biological context and all analyses displayed
                    </td>
                </tr>
                <tr>
                    <td><code>history.php</code></td>
                    <td>
                        Session run history with status badges and
                        direct links to each analysis
                    </td>
                </tr>
                <tr>
                    <td><code>help.php</code></td>
                    <td>
                        User guide for all audiences — biology,
                        bioinformatics, glossary, FAQ
                    </td>
                </tr>
                <tr>
                    <td><code>about.php</code></td>
                    <td>Developer-facing implementation overview</td>
                </tr>
                <tr>
                    <td><code>credits.php</code></td>
                    <td>Full attribution and AI usage statement</td>
                </tr>
                <tr>
                    <td><code>scripts/</code></td>
                    <td>
                        Python analysis scripts —
                        <code>fetch_sequences.py</code>,
                        <code>run_conservation.py</code>,
                        <code>run_motifs.py</code>,
                        <code>run_phylogeny.py</code>,
                        <code>run_structures.py</code>,
                        <code>setup_example.py</code>
                    </td>
                </tr>
                <tr>
                    <td><code>results/</code></td>
                    <td>
                        Generated output files — FASTA, aligned FASTA,
                        Newick trees, PNG plots, flat files.
                        Permissions set to 777 for Apache write access.
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- ── Design decisions ─────────────────────────────────────── -->
    <div class="card">
        <h2>key design decisions</h2>

        <div class="decision-item">
            <div class="decision-q">
                Why does PHP call Python via shell_exec() rather than
                using a Python web framework?
            </div>
            <div class="decision-a">
                The assessment specifies Apache + PHP as the web layer.
                Python is used purely as an analysis engine invoked from
                PHP. This keeps the web layer consistent and allows each
                Python script to be tested independently on the command
                line before integration, which was a key part of the
                development workflow.
            </div>
        </div>

        <div class="decision-item">
            <div class="decision-q">
                Why are analysis results stored as flat files rather
                than entirely in the database?
            </div>
            <div class="decision-a">
                PNG images and FASTA files are inherently file-based
                outputs — storing them as BLOBs in MySQL would add
                complexity with no benefit. The database stores metadata
                (file paths, summaries, run status) while the filesystem
                stores the actual output files. This mirrors the approach
                used in the directed learning practicals.
            </div>
        </div>

        <div class="decision-item">
            <div class="decision-q">
                Why is the motif scan run per-sequence rather than on
                the full FASTA file at once?
            </div>
            <div class="decision-a">
                patmatmotifs does not reliably associate hits with
                individual sequences when given a multi-sequence FASTA
                file in all output modes. Running it per-sequence
                guarantees correct accession-to-hit mapping and allows
                the results to be clearly attributed to individual
                species, which is more biologically meaningful.
            </div>
        </div>

        <div class="decision-item">
            <div class="decision-q">
                Why D3.js for the phylogenetic tree rather than
                a dedicated phylogenetics library?
            </div>
            <div class="decision-a">
                Phylotree.js (the first choice) produced a
                <code>TypeError: ___namespace.extend is not a function</code>
                error due to an incompatibility between its expected D3
                version and D3 v7. D3's built-in
                <code>d3.tree()</code> and <code>d3.hierarchy()</code>
                functions with a custom Newick parser provided equivalent
                functionality without the dependency conflict, and gave
                direct control over the visual style.
            </div>
        </div>

        <div class="decision-item">
            <div class="decision-q">
                Why does the example dataset use a separate table
                rather than a dedicated Run record?
            </div>
            <div class="decision-a">
                The example dataset must be available regardless of
                session state and must never be modified by the live
                pipeline. A separate <code>ExampleDataset</code> table
                enforces this isolation at the database level. The
                example page reads directly from this table and from
                pre-generated files in <code>results/</code>, with no
                dependency on the <code>Runs</code> or
                <code>Sequences</code> tables.
            </div>
        </div>

        <div class="decision-item">
            <div class="decision-q">
                How is user input sanitised to prevent injection attacks?
            </div>
            <div class="decision-a">
                All user input is sanitised at two levels. First,
                JavaScript validation on the client side rejects empty
                inputs and enforces numeric ranges. Second, PHP strips
                any characters outside the allowed set using
                <code>preg_replace('/[^a-zA-Z0-9\s\-]/', '', $input)</code>
                before the input is used in any database query or
                shell command. All database queries use PDO prepared
                statements with named parameters to prevent SQL injection.
                Shell arguments are escaped with
                <code>escapeshellarg()</code> before being passed to
                <code>shell_exec()</code>.
            </div>
        </div>

    </div>

    <!-- ── Known limitations ────────────────────────────────────── -->
    <div class="card">
        <h2>known limitations</h2>

        <div class="about-section">
            <table class="flow-table">
                <tr><th>limitation</th><th>details</th></tr>
                <tr>
                    <td>AlphaFold coverage</td>
                    <td>
                        Many NCBI predicted sequences do not have UniProt
                        cross-references in their GenBank records. The
                        UniProt organism search fallback may return an
                        entry from the correct species but not the
                        correct protein. Users should verify AlphaFold
                        matches independently.
                    </td>
                </tr>
                <tr>
                    <td>Session persistence</td>
                    <td>
                        Run history is stored in PHP sessions which are
                        lost when the browser session ends. The
                        <code>session_token</code> in the database
                        provides a hook for future persistent history
                        but is not currently exposed in the UI.
                    </td>
                </tr>
                <tr>
                    <td>Large sequence sets</td>
                    <td>
                        Fetching and analysing more than 50 sequences
                        will cause long page load times. No asynchronous
                        processing or job queue is implemented —
                        all analysis runs synchronously within the
                        Apache request timeout.
                    </td>
                </tr>
                <tr>
                    <td>Phylogenetic tree accuracy</td>
                    <td>
                        The guide tree produced by Clustal Omega is
                        a neighbour-joining approximation used to guide
                        alignment, not a rigorously inferred phylogeny.
                        It is not suitable for evolutionary inference.
                    </td>
                </tr>
                <tr>
                    <td>results/ permissions</td>
                    <td>
                        The <code>results/</code> directory requires
                        chmod 777 for Apache to write output files.
                        This is a known limitation of the shared server
                        environment and would not be acceptable in a
                        production deployment.
                    </td>
                </tr>
            </table>
        </div>
    </div>

</div>

<footer>
    ProtExplorer &mdash; IWD2 assessed website &mdash;
    <a href="credits.php">credits &amp; AI usage</a>
</footer>

</body>
</html>
