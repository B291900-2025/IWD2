<?php
session_start();
$active_page = 'credits';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProtExplorer &mdash; credits &amp; AI usage</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .credit-section {
            margin-bottom: 2rem;
        }
        .credit-section h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 0.75rem;
            padding-bottom: 0.4rem;
            border-bottom: 2px solid var(--primary-light);
        }
        .credit-item {
            display: grid;
            grid-template-columns: 220px 1fr;
            gap: 0.5rem 1rem;
            padding: 0.6rem 0;
            border-bottom: 1px solid var(--border);
            font-size: 0.9rem;
            align-items: start;
        }
        .credit-item:last-child { border-bottom: none; }
        .credit-name {
            font-weight: 700;
            color: var(--primary-dark);
        }
        .credit-desc { color: var(--text); line-height: 1.6; }
        .credit-desc a {
            color: var(--primary);
            text-decoration: none;
            border-bottom: 1px dotted var(--primary);
        }
        .credit-desc a:hover { border-bottom-style: solid; }
        .ai-box {
            background: #fdecea;
            border-left: 4px solid #c0392b;
            border-radius: 0 var(--radius) var(--radius) 0;
            padding: 1rem 1.2rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            line-height: 1.7;
        }
        .ai-box strong { color: #7b1a14; }
        .dl-box {
            background: var(--primary-light);
            border-left: 4px solid var(--primary);
            border-radius: 0 var(--radius) var(--radius) 0;
            padding: 1rem 1.2rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            line-height: 1.7;
        }
        .dl-box strong { color: var(--primary-dark); }
    </style>
</head>
<body>

<?php require_once 'menuf.php'; ?>

<div class="page-wrap">

    <div class="page-header" style="margin-top:2rem;">
        <h1>credits &amp; AI usage statement</h1>
        <p>Full attribution for all code, tools, data sources and AI assistance
           used in building ProtExplorer</p>
    </div>

    <!-- ── AI Usage Statement ──────────────────────────────────── -->
    <div class="card">
        <h2>AI usage statement</h2>

        <div class="ai-box">
            <strong>Important:</strong> In accordance with the assessment
            guidelines, all AI tool usage is disclosed in full below.
            AI tools were used as a coding assistant only — all biological
            interpretation, design decisions, database schema, page structure,
            and final implementation were produced and verified by the student.
        </div>

        <div class="credit-section">
            <h3>AI tools used</h3>

            <div class="credit-item">
                <span class="credit-name">
                    Claude (Anthropic)<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        claude.ai
                    </span>
                </span>
                <span class="credit-desc">
                    Used as a coding assistant throughout the project.
                    Specifically used for:
                    <ul style="margin-top:0.4rem; margin-left:1.2rem; line-height:1.9;">
                        <li>
                            Generating initial PHP scaffolding for
                            <code>process_search.php</code>,
                            <code>results.php</code>,
                            <code>run_conservation.php</code>,
                            <code>run_motifs.php</code>,
                            <code>motif_results.php</code>,
                            <code>run_phylogeny.php</code>,
                            <code>run_structures.php</code>,
                            <code>example.php</code> and
                            <code>history.php</code>
                            — all of which were subsequently reviewed,
                            modified and debugged by the student before use.
                        </li>
                        <li>
                            Generating initial Python script scaffolding for
                            <code>run_motifs.py</code>,
                            <code>setup_example.py</code>
                            — all reviewed, tested and modified by the student.
                        </li>
                        <li>
                            Explaining the correct output format of the
                            EMBOSS <code>patmatmotifs</code> tool and helping
                            debug the output parser after inspecting real
                            <code>patmatmotifs</code> output.
                        </li>
                        <li>
                            Suggesting D3.js as the library for interactive
                            phylogenetic tree rendering after Phylotree.js
                            failed due to a version incompatibility.
                        </li>
                        <li>
                            Helping debug permission errors when Apache could
                            not write to the <code>results/</code> directory.
                        </li>
                    </ul>
                </span>
            </div>

            <div class="credit-item">
                <span class="credit-name">No other AI tools used</span>
                <span class="credit-desc">
                    No other AI tools (ChatGPT, GitHub Copilot, DeepSeek,
                    Grammarly, etc.) were used at any point in this project.
                </span>
            </div>
        </div>
    </div>

    <!-- ── Directed Learning ────────────────────────────────────── -->
    <div class="card">
        <h2>directed learning materials</h2>

        <div class="dl-box">
            <strong>Primary source:</strong> The course Directed Learning
            practicals provided the foundational patterns for almost every
            component of this website. The DL materials are the primary
            intellectual basis for the architecture of ProtExplorer.
        </div>

        <div class="credit-section">
            <h3>
                directed learning practicals &mdash;
                Dr. Al Ivens, The University of Edinburgh, IWD2 BILG11016 (2025&ndash;26)
            </h3>

            <div class="credit-item">
                <span class="credit-name">
                    Directed Learning 1<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://bioinfmsc8.bio.ed.ac.uk/AY25_IWD2_DirLearn_01.html"
                           target="_blank">DirLearn_01</a>
                    </span>
                </span>
                <span class="credit-desc">
                    The approach of using a Python script to parse a data file
                    and populate a MySQL database via a separate mechanism was
                    directly modelled on the DL1 workflow
                    (<code>Compounds_tablepop.py</code>,
                    <code>Compounds_remainder_pop.py</code>).
                    The concept of separating data extraction (Python) from
                    database interaction (PHP/PDO) was taught here and applied
                    throughout ProtExplorer.
                    The pattern of building tables with
                    <code>AUTO_INCREMENT</code> primary keys and
                    <code>NOT NULL DEFAULT</code> columns follows the
                    <code>newCompoundsTable.sql</code> example from DL1.
                    The while-loop pattern for processing multiple data
                    entries was directly adapted from the DL1 manufacturer
                    processing loop.
                </span>
            </div>

            <div class="credit-item">
                <span class="credit-name">
                    Directed Learning 2a<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://bioinfmsc8.bio.ed.ac.uk/AY25_IWD2_DirLearn_02a.html"
                           target="_blank">DirLearn_02a</a>
                    </span>
                </span>
                <span class="credit-desc">
                    The database table design pattern used in ProtExplorer
                    — including the use of foreign keys, indexes on frequently
                    queried columns, and the separation of data into related
                    tables — was directly informed by the DL2a table design
                    for the molecule database. The
                    <code>CREATE INDEX</code> commands for
                    <code>idx_session</code>, <code>idx_run_seq</code>
                    and <code>idx_run_res</code> in
                    <code>maketables.sql</code> follow the
                    <code>catind</code> index pattern from DL2a.
                </span>
            </div>

            <div class="credit-item">
                <span class="credit-name">
                    Directed Learning 2b<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://bioinfmsc8.bio.ed.ac.uk/AY25_IWD2_DirLearn_02b.html"
                           target="_blank">DirLearn_02b</a>
                    </span>
                </span>
                <span class="credit-desc">
                    The multi-page PHP website architecture of ProtExplorer
                    is directly modelled on the DL2b website structure.
                    Specifically:
                    <ul style="margin-top:0.4rem; margin-left:1.2rem; line-height:1.9;">
                        <li>
                            The <code>login.php</code> credentials file
                            pattern with <code>require_once</code> inclusion
                            is taken directly from the DL2b
                            <code>login.php</code> example.
                        </li>
                        <li>
                            The <code>redir.php</code> session guard pattern
                            — checking session variables and redirecting if
                            not set — is adapted from the DL2b
                            <code>redir.php</code>.
                        </li>
                        <li>
                            The <code>menuf.php</code> shared navigation
                            include pattern is modelled on the DL2b
                            <code>menuf.php</code>.
                        </li>
                        <li>
                            The use of <code>$_SESSION</code> to carry data
                            between pages (run ID, protein family, taxon)
                            follows the DL2b session mask pattern.
                        </li>
                        <li>
                            The PDO connection pattern in
                            <code>get_pdo()</code> — including
                            <code>PDO::ATTR_ERRMODE</code>,
                            <code>PDO::ERRMODE_EXCEPTION</code> and
                            <code>PDO::FETCH_ASSOC</code> — is adapted
                            from the <code>playblast.php</code> PDO example
                            provided in Lecture 3.
                        </li>
                        <li>
                            The pattern of using
                            <code>$stmt->execute()</code> with named
                            parameters (e.g. <code>:run_id</code>,
                            <code>:protein</code>) and
                            <code>fetchAll(PDO::FETCH_ASSOC)</code>
                            throughout all PHP pages follows the DL2b PDO
                            fixing exercises.
                        </li>
                        <li>
                            The supplier selection bitmask concept from DL2b
                            (<code>supmask</code>) inspired the
                            <code>scan_scope</code> and
                            <code>result_format</code> user option pattern
                            on the motif scan page.
                        </li>
                    </ul>
                </span>
            </div>

            <div class="credit-item">
                <span class="credit-name">
                    Directed Learning 3<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://bioinfmsc8.bio.ed.ac.uk/AY25_IWD2_DirLearn_03.html"
                           target="_blank">DirLearn_03</a>
                    </span>
                </span>
                <span class="credit-desc">
                    <ul style="margin-top:0.4rem; margin-left:1.2rem; line-height:1.9;">
                        <li>
                            The histogram/plot generation approach — calling
                            a Python script from PHP via
                            <code>shell_exec()</code>, saving the output as
                            a PNG, and displaying it with an
                            <code>&lt;img&gt;</code> tag — is directly
                            modelled on the DL3 histogram example
                            (<a href="https://bioinfmsc8.bio.ed.ac.uk/IWD_DL3_06_histogram.html"
                               target="_blank">DL3 section 6</a>).
                        </li>
                        <li>
                            The use of <code>matplotlib.use('Agg')</code>
                            and setting
                            <code>MPLCONFIGDIR</code> to <code>/tmp</code>
                            to allow server-side matplotlib rendering without
                            a display was learned from the DL3 Python
                            plotting examples and the MissingModule page.
                        </li>
                        <li>
                            The concept of a Help page aimed at biologists
                            and an About page aimed at developers — kept
                            strictly separate — is taken directly from
                            DL3 section 2
                            (<a href="https://bioinfmsc8.bio.ed.ac.uk/IWD_DL3_02_Add_help.html"
                               target="_blank">Add a help page</a>).
                        </li>
                        <li>
                            The PDO-or-not decision — using PDO for all
                            MySQL interactions and never using Python MySQL
                            modules — was reinforced by DL3 section 10
                            (<a href="https://bioinfmsc8.bio.ed.ac.uk/IWD_DL3_10_PDO_or_not.html"
                               target="_blank">PDO or not?</a>)
                            and section 11
                            (<a href="https://bioinfmsc8.bio.ed.ac.uk/IWD_DL3_11_morePDOexamples.html"
                               target="_blank">more PDO examples</a>).
                        </li>
                        <li>
                            The correlation/property page concept from DL3
                            section 5 informed the summary statistics display
                            on the conservation analysis page.
                        </li>
                    </ul>
                </span>
            </div>

            <div class="credit-item">
                <span class="credit-name">
                    Lecture 2 &mdash; PHP fundamentals<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://bioinfmsc8.bio.ed.ac.uk/AY25_IWD2_02.html"
                           target="_blank">IWD2_02</a>
                    </span>
                </span>
                <span class="credit-desc">
                    The use of <code>isset($_POST[...])</code> for form
                    validation, <code>heredoc</code> syntax, the
                    <code>$_POST</code> associative array for form
                    processing, <code>session_start()</code> placement
                    at the top of every page, and the
                    <code>shell_exec()</code> pattern for running
                    external programmes from PHP were all taught in
                    Lecture 2 and applied throughout ProtExplorer.
                    The PDB sequence display example
                    (<code>02_showsequence.php</code>) directly informed
                    the approach of calling an external programme from PHP
                    and displaying its output on a web page.
                </span>
            </div>

            <div class="credit-item">
                <span class="credit-name">
                    Lecture 3 &mdash; MySQL and PDO<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://bioinfmsc8.bio.ed.ac.uk/AY25_IWD2_03.html"
                           target="_blank">IWD2_03</a>
                    </span>
                </span>
                <span class="credit-desc">
                    The BLAST data analysis example
                    (<code>playblast.php</code>) provided in Lecture 3
                    was the primary reference for the PDO connection and
                    query pattern used throughout ProtExplorer. The
                    specific use of
                    <code>new PDO("mysql:host=...;dbname=...;charset=utf8mb4")</code>,
                    <code>$pdo->prepare()</code>,
                    <code>$stmt->execute()</code> and
                    <code>$stmt->fetchAll(PDO::FETCH_ASSOC)</code>
                    is directly adapted from that example.
                    The BLAST output table structure also informed the
                    sequences results table layout on
                    <code>results.php</code>.
                </span>
            </div>

            <div class="credit-item">
                <span class="credit-name">
                    Lecture 4 &mdash; JavaScript<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://bioinfmsc8.bio.ed.ac.uk/AY25_IWD2_04.html"
                           target="_blank">IWD2_04</a>
                    </span>
                </span>
                <span class="credit-desc">
                    The JavaScript form validation pattern using
                    <code>onsubmit="return validateForm()"</code>
                    and <code>alert()</code> for user feedback is
                    adapted from the Lecture 4
                    <code>04_js6.php</code> example. The
                    <code>document.getElementById()</code> DOM
                    manipulation pattern for showing/hiding the
                    custom input boxes on <code>search.php</code>
                    and <code>run_motifs.php</code> follows the
                    <code>04_js2A.html</code> example. The dynamic
                    HTML table manipulation concept from
                    <code>04_js8.html</code> through
                    <code>04_js10.html</code> informed the
                    expandable sequence display on
                    <code>results.php</code> and
                    <code>example.php</code>.
                </span>
            </div>

            <div class="credit-item">
                <span class="credit-name">
                    Lecture 6 &mdash; CSS and usability<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://bioinfmsc8.bio.ed.ac.uk/AY25_IWD2_06.html"
                           target="_blank">IWD2_06</a>
                    </span>
                </span>
                <span class="credit-desc">
                    The principle of using a single external CSS stylesheet
                    (<code>style.css</code>) for consistent styling across
                    all pages was taught in Lecture 6. The CSS variable
                    approach for colours (<code>--primary</code>,
                    <code>--accent</code> etc.) was inspired by the
                    lecture's discussion of consistent style management.
                    The navigation link hover and active state styling
                    using <code>border-bottom</code> follows the link
                    styling examples from Lecture 6.
                    The usability principles discussed — readable fonts,
                    adequate contrast, easy navigation, breadcrumb-style
                    back links — were applied throughout the site design.
                </span>
            </div>

            <div class="credit-item">
                <span class="credit-name">
                    MissingModule page<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://bioinfmsc8.bio.ed.ac.uk/MissingModule.html"
                           target="_blank">MissingModule.html</a>
                    </span>
                </span>
                <span class="credit-desc">
                    The guidance on setting up Python virtual environments
                    and handling missing modules on the bioinfmsc8 server
                    was referenced when configuring the Python scripts.
                    The solution of setting
                    <code>os.environ['MPLCONFIGDIR'] = '/tmp'</code>
                    at the top of all Python scripts using matplotlib
                    was derived from troubleshooting informed by this page.
                </span>
            </div>

        </div>
    </div>

    <!-- ── Bioinformatics tools ─────────────────────────────────── -->
    <div class="card">
        <h2>bioinformatics tools and databases</h2>

        <div class="credit-section">
            <h3>sequence retrieval</h3>

            <div class="credit-item">
                <span class="credit-name">
                    NCBI Protein database<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://www.ncbi.nlm.nih.gov/protein"
                           target="_blank">ncbi.nlm.nih.gov</a>
                    </span>
                </span>
                <span class="credit-desc">
                    All protein sequences used in ProtExplorer are retrieved
                    from the NCBI Protein database using the Entrez
                    programmatic access system. NCBI (National Center for
                    Biotechnology Information) is maintained by the U.S.
                    National Library of Medicine.
                    <br>
                    Sayers EW et al. (2022) Database resources of the
                    National Center for Biotechnology Information.
                    <em>Nucleic Acids Research</em> 50(D1):D20&ndash;D26.
                    <a href="https://doi.org/10.1093/nar/gkab1112"
                       target="_blank">doi:10.1093/nar/gkab1112</a>
                </span>
            </div>

            <div class="credit-item">
                <span class="credit-name">
                    BioPython (Bio.Entrez)<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://biopython.org/"
                           target="_blank">biopython.org</a>
                    </span>
                </span>
                <span class="credit-desc">
                    The BioPython library is used in
                    <code>fetch_sequences.py</code> and
                    <code>run_structures.py</code> to access NCBI Entrez
                    services programmatically via <code>Bio.Entrez</code>
                    and parse FASTA format sequences via
                    <code>Bio.SeqIO</code>.
                    <br>
                    Cock PJA et al. (2009) Biopython: freely available
                    Python tools for computational molecular biology and
                    bioinformatics. <em>Bioinformatics</em> 25:1422&ndash;1423.
                    <a href="https://doi.org/10.1093/bioinformatics/btp163"
                       target="_blank">doi:10.1093/bioinformatics/btp163</a>
                </span>
            </div>

            <div class="credit-item">
                <span class="credit-name">
                    NCBI Entrez API<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://www.ncbi.nlm.nih.gov/books/NBK179288/"
                           target="_blank">NBK179288</a>
                    </span>
                </span>
                <span class="credit-desc">
                    The NCBI Entrez Programming Utilities (E-utilities) are
                    used for all sequence retrieval. The
                    <code>esearch</code> and <code>efetch</code> functions
                    are used to search for and retrieve protein sequences
                    by protein name and organism. An NCBI API key is used
                    to increase rate limits as recommended in the
                    documentation.
                </span>
            </div>

        </div>

        <div class="credit-section">
            <h3>sequence alignment and conservation</h3>

            <div class="credit-item">
                <span class="credit-name">
                    Clustal Omega<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="http://www.clustal.org/omega/"
                           target="_blank">clustal.org/omega</a>
                    </span>
                </span>
                <span class="credit-desc">
                    Clustal Omega is used for multiple sequence alignment
                    in <code>run_conservation.py</code> and for guide tree
                    construction in <code>run_phylogeny.py</code>. The
                    <code>--outfmt=fasta</code> flag is used for alignment
                    output and <code>--guidetree-out</code> for Newick
                    tree output.
                    <br>
                    Sievers F et al. (2011) Fast, scalable generation of
                    high-quality protein multiple sequence alignments using
                    Clustal Omega. <em>Molecular Systems Biology</em> 7:539.
                    <a href="https://doi.org/10.1038/msb.2011.75"
                       target="_blank">doi:10.1038/msb.2011.75</a>
                </span>
            </div>

            <div class="credit-item">
                <span class="credit-name">
                    matplotlib<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://matplotlib.org/"
                           target="_blank">matplotlib.org</a>
                    </span>
                </span>
                <span class="credit-desc">
                    matplotlib is used in <code>run_conservation.py</code>
                    and <code>setup_example.py</code> to generate the
                    conservation profile bar charts and static phylogenetic
                    tree images. The <code>Agg</code> non-interactive
                    backend is used to allow rendering on the server
                    without a display.
                    <br>
                    Hunter JD (2007) Matplotlib: A 2D graphics environment.
                    <em>Computing in Science &amp; Engineering</em>
                    9(3):90&ndash;95.
                    <a href="https://doi.org/10.1109/MCSE.2007.55"
                       target="_blank">doi:10.1109/MCSE.2007.55</a>
                </span>
            </div>

            <div class="credit-item">
                <span class="credit-name">
                    NumPy<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://numpy.org/"
                           target="_blank">numpy.org</a>
                    </span>
                </span>
                <span class="credit-desc">
                    NumPy is used in the conservation analysis scripts for
                    efficient array operations when calculating per-position
                    conservation scores across the alignment.
                    <br>
                    Harris CR et al. (2020) Array programming with NumPy.
                    <em>Nature</em> 585:357&ndash;362.
                    <a href="https://doi.org/10.1038/s41586-020-2649-2"
                       target="_blank">doi:10.1038/s41586-020-2649-2</a>
                </span>
            </div>

        </div>

        <div class="credit-section">
            <h3>motif analysis</h3>

            <div class="credit-item">
                <span class="credit-name">
                    EMBOSS patmatmotifs<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://emboss.sourceforge.net/apps/cvs/emboss/apps/patmatmotifs.html"
                           target="_blank">emboss.sourceforge.net</a>
                    </span>
                </span>
                <span class="credit-desc">
                    The EMBOSS <code>patmatmotifs</code> programme is used
                    in <code>run_motifs.py</code> to scan protein sequences
                    against the PROSITE pattern database. The
                    <code>-full Y</code> flag is used to obtain full output
                    including start and end positions of each motif hit.
                    <br>
                    Rice P, Longden I, Bleasby A (2000) EMBOSS: The
                    European Molecular Biology Open Software Suite.
                    <em>Trends in Genetics</em> 16(6):276&ndash;277.
                    <a href="https://doi.org/10.1016/S0168-9525(00)02024-2"
                       target="_blank">doi:10.1016/S0168-9525(00)02024-2</a>
                </span>
            </div>

            <div class="credit-item">
                <span class="credit-name">
                    PROSITE database<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://prosite.expasy.org/"
                           target="_blank">prosite.expasy.org</a>
                    </span>
                </span>
                <span class="credit-desc">
                    The PROSITE database of protein families, domains and
                    functional sites is used as the reference database for
                    motif scanning via patmatmotifs. PROSITE is maintained
                    by the Swiss Institute of Bioinformatics (SIB) as part
                    of the ExPASy bioinformatics resource portal.
                    <br>
                    Sigrist CJA et al. (2013) New and continuing
                    developments at PROSITE.
                    <em>Nucleic Acids Research</em> 41(D1):D344&ndash;D347.
                    <a href="https://doi.org/10.1093/nar/gks1067"
                       target="_blank">doi:10.1093/nar/gks1067</a>
                </span>
            </div>

        </div>

        <div class="credit-section">
            <h3>phylogenetic tree visualisation</h3>

            <div class="credit-item">
                <span class="credit-name">
                    D3.js (v7)<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://d3js.org/"
                           target="_blank">d3js.org</a>
                    </span>
                </span>
                <span class="credit-desc">
                    D3.js is used on <code>run_phylogeny.php</code> and
                    <code>example.php</code> to render interactive
                    phylogenetic trees from Newick format strings.
                    The <code>d3.hierarchy()</code>,
                    <code>d3.tree()</code>, <code>d3.linkHorizontal()</code>
                    and <code>d3.linkRadial()</code> functions are used for
                    linear and radial tree layouts respectively.
                    The Newick parser used is a custom implementation
                    adapted from standard recursive descent parsing.
                    <br>
                    Bostock M, Ogievetsky V, Heer J (2011)
                    D3: Data-Driven Documents.
                    <em>IEEE Transactions on Visualization and
                    Computer Graphics</em> 17(12):2301&ndash;2309.
                    <a href="https://doi.org/10.1109/TVCG.2011.185"
                       target="_blank">doi:10.1109/TVCG.2011.185</a>
                </span>
            </div>

        </div>

        <div class="credit-section">
            <h3>structure data</h3>

            <div class="credit-item">
                <span class="credit-name">
                    AlphaFold Protein Structure Database<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://alphafold.ebi.ac.uk/"
                           target="_blank">alphafold.ebi.ac.uk</a>
                    </span>
                </span>
                <span class="credit-desc">
                    The AlphaFold database, maintained by the European
                    Bioinformatics Institute (EBI) in collaboration with
                    DeepMind, is queried via its REST API in
                    <code>run_structures.py</code> to retrieve predicted
                    3D structure data including PAE images and pLDDT
                    confidence scores for each sequence where a UniProt
                    cross-reference is available.
                    <br>
                    Varadi M et al. (2022) AlphaFold Protein Structure
                    Database: massively expanding the structural coverage
                    of protein-sequence space with high-accuracy models.
                    <em>Nucleic Acids Research</em> 50(D1):D439&ndash;D444.
                    <a href="https://doi.org/10.1093/nar/gkab1061"
                       target="_blank">doi:10.1093/nar/gkab1061</a>
                </span>
            </div>

            <div class="credit-item">
                <span class="credit-name">
                    UniProt<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://www.uniprot.org/"
                           target="_blank">uniprot.org</a>
                    </span>
                </span>
                <span class="credit-desc">
                    UniProt is queried in <code>run_structures.py</code>
                    as a fallback to find UniProt accession numbers for
                    sequences that do not have a UniProt cross-reference
                    in their NCBI GenBank record. The UniProt REST API
                    (<code>rest.uniprot.org</code>) is used for this lookup.
                    UniProt accessions are required to query the AlphaFold
                    database.
                    <br>
                    The UniProt Consortium (2023) UniProt: the Universal
                    Protein Knowledgebase in 2023.
                    <em>Nucleic Acids Research</em> 51(D1):D523&ndash;D531.
                    <a href="https://doi.org/10.1093/nar/gkac1052"
                       target="_blank">doi:10.1093/nar/gkac1052</a>
                </span>
            </div>

        </div>

    </div>

    <!-- ── Web technologies ─────────────────────────────────────── -->
    <div class="card">
        <h2>web technologies</h2>

        <div class="credit-section">
            <h3>server and languages</h3>

            <div class="credit-item">
                <span class="credit-name">
                    Apache HTTP Server<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://httpd.apache.org/"
                           target="_blank">httpd.apache.org</a>
                    </span>
                </span>
                <span class="credit-desc">
                    The website is served by Apache 2 running on the
                    University of Edinburgh bioinfmsc8 server
                    (bioinfmsc8.bio.ed.ac.uk), configured as part of
                    the LAMP stack (Linux, Apache, MySQL, PHP) as taught
                    in IWD2 Lecture 5.
                </span>
            </div>

            <div class="credit-item">
                <span class="credit-name">
                    PHP 8.2<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://www.php.net/"
                           target="_blank">php.net</a>
                    </span>
                </span>
                <span class="credit-desc">
                    All server-side web pages are written in PHP 8.2,
                    the version installed on bioinfmsc8. PHP is used
                    for all HTML generation, form processing, session
                    management, shell execution of Python scripts, and
                    all database interactions via PDO.
                </span>
            </div>

            <div class="credit-item">
                <span class="credit-name">
                    MySQL 8.0<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://dev.mysql.com/"
                           target="_blank">mysql.com</a>
                    </span>
                </span>
                <span class="credit-desc">
                    MySQL 8.0 is used as the relational database
                    management system for storing run data, sequences,
                    analysis results and the example dataset. All MySQL
                    interactions from PHP use PDO (PHP Data Objects)
                    as required by the assessment specification.
                </span>
            </div>

            <div class="credit-item">
                <span class="credit-name">
                    Python 3.12<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://www.python.org/"
                           target="_blank">python.org</a>
                    </span>
                </span>
                <span class="credit-desc">
                    Python 3.12 is used for all analysis pipeline scripts.
                    Python handles NCBI sequence retrieval, multiple
                    sequence alignment via subprocess calls to Clustal
                    Omega and patmatmotifs, conservation calculation,
                    plot generation and AlphaFold API queries.
                    Python does not interact with the MySQL database
                    directly — all database operations are handled by PHP.
                </span>
            </div>

            <div class="credit-item">
                <span class="credit-name">
                    Git &amp; GitHub<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://github.com/B291900-2025/IWD2"
                           target="_blank">github.com/B291900-2025/IWD2</a>
                    </span>
                </span>
                <span class="credit-desc">
                    Git was used for version control throughout development,
                    with the repository hosted on GitHub. All code for
                    ProtExplorer is available in the linked repository.
                </span>
            </div>

        </div>

        <div class="credit-section">
            <h3>external JavaScript libraries</h3>

            <div class="credit-item">
                <span class="credit-name">
                    D3.js v7.8.5<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://cdnjs.cloudflare.com/ajax/libs/d3/7.8.5/d3.min.js"
                           target="_blank">cdnjs.cloudflare.com</a>
                    </span>
                </span>
                <span class="credit-desc">
                    Loaded from Cloudflare CDN for phylogenetic tree
                    rendering. Used on <code>run_phylogeny.php</code>
                    and <code>example.php</code>.
                </span>
            </div>

        </div>

    </div>

    <!-- ── University resources ─────────────────────────────────── -->
    <div class="card">
        <h2>university and course resources</h2>

        <div class="credit-section">

            <div class="credit-item">
                <span class="credit-name">
                    bioinfmsc8 server<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        The University of Edinburgh
                    </span>
                </span>
                <span class="credit-desc">
                    The bioinfmsc8.bio.ed.ac.uk server is provided by
                    The University of Edinburgh School of Biological
                    Sciences for student web development. All software
                    used (Clustal Omega, EMBOSS, Python, MySQL, Apache,
                    PHP) was pre-installed on this server.
                </span>
            </div>

            <div class="credit-item">
                <span class="credit-name">
                    IWD2 course materials<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        Dr. Al Ivens,The University of Edinburgh
                    </span>
                </span>
                <span class="credit-desc">
                    All lecture and directed learning materials for
                    BILG11016 Introduction to Website and Database Design
                    (2025&ndash;26), authored by Dr. Al Ivens,The University of
                    Edinburgh. These materials formed the primary reference
                    for this project. Individual lectures and directed
                    learning pages are credited in detail in the
                    Directed Learning section above.
                </span>
            </div>

            <div class="credit-item">
                <span class="credit-name">
                    W3Schools<br>
                    <span style="font-weight:400; font-size:0.8rem;">
                        <a href="https://www.w3schools.com"
                           target="_blank">w3schools.com</a>
                    </span>
                </span>
                <span class="credit-desc">
                    W3Schools was used as a reference for HTML, CSS, PHP
                    and JavaScript syntax throughout development, as
                    recommended in the IWD2 course materials.
                </span>
            </div>

        </div>
    </div>

</div>

<footer>
    ProtExplorer &mdash; IWD2 assessed website &mdash;
    <a href="credits.php">credits &amp; AI usage</a>
</footer>

</body>
</html>
```
