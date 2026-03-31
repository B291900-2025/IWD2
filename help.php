<?php
session_start();
$active_page = 'help';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProtExplorer &mdash; help</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .help-section {
            margin-bottom: 1.5rem;
        }
        .help-section h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 0.6rem;
            padding-bottom: 0.3rem;
            border-bottom: 2px solid var(--primary-light);
        }
        .help-section p {
            font-size: 0.92rem;
            line-height: 1.8;
            margin-bottom: 0.75rem;
            color: var(--text);
        }
        .help-section a {
            color: var(--primary);
            text-decoration: none;
            border-bottom: 1px dotted var(--primary);
        }
        .help-section a:hover { border-bottom-style: solid; }
        .audience-box {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .audience-card {
            background: var(--primary-light);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem;
        }
        .audience-card h4 {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 0.4rem;
        }
        .audience-card p {
            font-size: 0.82rem;
            color: var(--text-muted);
            line-height: 1.6;
        }
        .step-grid {
            display: grid;
            grid-template-columns: 40px 1fr;
            gap: 0.5rem 1rem;
            align-items: start;
            margin-bottom: 1rem;
        }
        .step-num {
            background: var(--primary);
            color: #fff;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.88rem;
            flex-shrink: 0;
            margin-top: 0.1rem;
        }
        .step-content h4 {
            font-size: 0.92rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 0.3rem;
        }
        .step-content p {
            font-size: 0.88rem;
            line-height: 1.7;
            color: var(--text);
            margin-bottom: 0;
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
        .warning-box {
            background: #fef9e7;
            border-left: 4px solid #f0ad4e;
            border-radius: 0 var(--radius) var(--radius) 0;
            padding: 0.85rem 1rem;
            margin: 0.75rem 0;
            font-size: 0.88rem;
            color: #7d5a00;
            line-height: 1.7;
        }
        .faq-item {
            border-bottom: 1px solid var(--border);
            padding: 0.85rem 0;
        }
        .faq-item:last-child { border-bottom: none; }
        .faq-q {
            font-weight: 700;
            font-size: 0.92rem;
            color: var(--primary-dark);
            margin-bottom: 0.4rem;
        }
        .faq-a {
            font-size: 0.88rem;
            color: var(--text);
            line-height: 1.7;
        }
        .glossary-item {
            display: grid;
            grid-template-columns: 180px 1fr;
            gap: 0.5rem 1rem;
            padding: 0.6rem 0;
            border-bottom: 1px solid var(--border);
            font-size: 0.88rem;
        }
        .glossary-item:last-child { border-bottom: none; }
        .glossary-term {
            font-weight: 700;
            color: var(--primary-dark);
        }
        .glossary-def {
            color: var(--text);
            line-height: 1.6;
        }
    </style>
</head>
<body>

<?php require_once 'menuf.php'; ?>

<div class="page-wrap">

    <div class="page-header" style="margin-top:2rem;">
        <h1>help</h1>
        <p>How to use ProtExplorer &mdash; for users of all backgrounds</p>
    </div>

    <!-- ── Who is this for ──────────────────────────────────────── -->
    <div class="card">
        <h2>who is this page for?</h2>
        <p style="font-size:0.92rem; line-height:1.8; margin-bottom:1rem;">
            ProtExplorer is designed to be accessible to users with a range
            of backgrounds. Whether you are an expert biologist, a
            bioinformatician, a student with basic biology knowledge, or
            simply curious about proteins and evolution, this page will help
            you get the most out of the tool. Use the sections below to
            find the level of detail that suits you.
        </p>
        <div class="audience-box">
            <div class="audience-card">
                <h4>biologist / life scientist</h4>
                <p>
                    Focus on the
                    <a href="#what-it-does">what it does</a>,
                    <a href="#how-to-use">how to use it</a> and
                    <a href="#interpreting">interpreting results</a>
                    sections. The glossary at the bottom explains any
                    technical terms.
                </p>
            </div>
            <div class="audience-card">
                <h4>bioinformatician</h4>
                <p>
                    The
                    <a href="#analyses">analyses in detail</a>
                    section describes the specific tools, parameters
                    and output formats used. See also the
                    <a href="about.php">about page</a>
                    for implementation details.
                </p>
            </div>
            <div class="audience-card">
                <h4>non-specialist</h4>
                <p>
                    Start with the
                    <a href="#what-is-protein">what is a protein?</a>
                    section, then follow the
                    <a href="#how-to-use">step-by-step guide</a>.
                    The
                    <a href="example.php">example page</a>
                    shows a complete worked example you can explore
                    before running your own search.
                </p>
            </div>
        </div>
    </div>

    <!-- ── What is a protein ────────────────────────────────────── -->
    <div class="card" id="what-is-protein">
        <h2>what is a protein? <span style="font-size:0.8rem;
            font-weight:400; color:var(--text-muted);">
            (background for non-specialists)</span>
        </h2>

        <div class="help-section">
            <p>
                Proteins are the molecular machines that carry out almost
                every biological process in living organisms — from digesting
                food to copying DNA to sensing light. They are built from
                chains of smaller units called <strong>amino acids</strong>,
                of which there are 20 different types. The order of these
                amino acids — the protein's <strong>sequence</strong> — is
                encoded in an organism's DNA and determines the protein's
                three-dimensional shape, and therefore its function.
                <a href="https://www.khanacademy.org/science/ap-biology/gene-expression-and-regulation/translation/a/protein-structure-review"
                   target="_blank">
                    Learn more about protein structure (Khan Academy)
                </a>
            </p>
            <p>
                When biologists study a protein across multiple species —
                for example, the same enzyme in birds, mammals and fish —
                they can compare the sequences to understand which parts
                are essential for function (they tend to stay the same
                across species) and which parts are more flexible (they
                vary). This comparison is called
                <strong>comparative sequence analysis</strong>, and it
                is the core purpose of ProtExplorer.
                <a href="https://www.ncbi.nlm.nih.gov/books/NBK26822/"
                   target="_blank">
                    Introduction to molecular evolution (NCBI Bookshelf)
                </a>
            </p>
        </div>
    </div>

    <!-- ── What ProtExplorer does ───────────────────────────────── -->
    <div class="card" id="what-it-does">
        <h2>what does ProtExplorer do?</h2>

        <div class="help-section">
            <p>
                ProtExplorer is a web-based bioinformatics tool that
                automates a standard comparative protein sequence analysis
                workflow. Given a protein family and a taxonomic group
                (e.g. "kinase" and "Mammalia"), it will:
            </p>

            <div class="step-grid">
                <div class="step-num">1</div>
                <div class="step-content">
                    <h4>retrieve sequences</h4>
                    <p>
                        Fetch all matching protein sequences from the
                        <a href="https://www.ncbi.nlm.nih.gov/protein"
                           target="_blank">NCBI Protein database</a>
                        — the world's largest publicly accessible protein
                        sequence repository, maintained by the US National
                        Library of Medicine.
                    </p>
                </div>
            </div>

            <div class="step-grid">
                <div class="step-num">2</div>
                <div class="step-content">
                    <h4>align sequences and measure conservation</h4>
                    <p>
                        Align all sequences using
                        <a href="http://www.clustal.org/omega/"
                           target="_blank">Clustal Omega</a>
                        and calculate how well-conserved each position in
                        the alignment is across all species. Highly
                        conserved positions are likely functionally or
                        structurally important.
                        <a href="https://www.ebi.ac.uk/training/online/courses/pfam-creating-protein-families/what-are-protein-families/protein-sequence-conservation/"
                           target="_blank">
                            Why does conservation matter?
                        </a>
                    </p>
                </div>
            </div>

            <div class="step-grid">
                <div class="step-num">3</div>
                <div class="step-content">
                    <h4>scan for known protein motifs</h4>
                    <p>
                        Search each sequence against the
                        <a href="https://prosite.expasy.org/"
                           target="_blank">PROSITE database</a>
                        of known protein domains and functional sites.
                        Finding a known motif in your sequences can tell
                        you something about their biological function,
                        even if the protein has never been experimentally
                        studied.
                        <a href="https://prosite.expasy.org/prosuser.html"
                           target="_blank">
                            What is PROSITE?
                        </a>
                    </p>
                </div>
            </div>

            <div class="step-grid">
                <div class="step-num">4</div>
                <div class="step-content">
                    <h4>build a phylogenetic tree</h4>
                    <p>
                        Construct a tree showing the inferred evolutionary
                        relationships between sequences. Species with more
                        similar sequences cluster together on shorter
                        branches, reflecting more recent common ancestry.
                        <a href="https://www.nature.com/scitable/topicpage/reading-a-phylogenetic-tree-the-meaning-of-41956/"
                           target="_blank">
                            How to read a phylogenetic tree (Nature Education)
                        </a>
                    </p>
                </div>
            </div>

            <div class="step-grid">
                <div class="step-num">5</div>
                <div class="step-content">
                    <h4>find 3D structure data</h4>
                    <p>
                        Retrieve links to predicted 3D protein structures
                        from the
                        <a href="https://alphafold.ebi.ac.uk/"
                           target="_blank">AlphaFold database</a>,
                        which contains AI-predicted structures for
                        hundreds of millions of proteins. Knowing the
                        3D shape of a protein helps explain how it
                        functions and how it might interact with drugs
                        or other molecules.
                        <a href="https://www.ebi.ac.uk/training/online/courses/alphafold/"
                           target="_blank">
                            Introduction to AlphaFold (EBI Training)
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- ── How to use it ────────────────────────────────────────── -->
    <div class="card" id="how-to-use">
        <h2>how to use ProtExplorer</h2>

        <div class="help-section">
            <h3>step 1 &mdash; try the example first</h3>
            <p>
                Before running your own search, we recommend exploring the
                <a href="example.php">example dataset</a> — a pre-processed
                analysis of glucose-6-phosphatase proteins from Aves (birds).
                This shows you exactly what the outputs look like and helps
                you understand how to interpret each result before applying
                the tool to your own question.
            </p>
        </div>

        <div class="help-section">
            <h3>step 2 &mdash; choose your protein and taxonomic group</h3>
            <p>
                Go to the <a href="search.php">search page</a> and select
                a protein family from the dropdown menu. If your protein
                of interest is not listed, select "other (type below)"
                and enter the name as it appears in the NCBI database
                — use the scientific name where possible (e.g.
                "cytochrome oxidase" rather than "COX"). Then select
                a taxonomic group.
            </p>
            <div class="info-box">
                <strong>Tip for biologists:</strong> The more specific your
                taxonomic group, the more closely related your sequences
                will be and the more meaningful your conservation analysis
                will be. Searching "kinase" in "Vertebrata" will return
                very diverse results; searching "insulin" in "Primates"
                will return tightly related sequences with high conservation.
            </div>
            <div class="warning-box">
                <strong>Important:</strong> Set the maximum number of
                sequences carefully. Fetching and analysing more than
                50 sequences will take several minutes. For a first
                search, 10&ndash;20 sequences is recommended.
            </div>
        </div>

        <div class="help-section">
            <h3>step 3 &mdash; run the analyses</h3>
            <p>
                After your sequences are retrieved, the results page shows
                a table of all sequences found. From here you can run
                each analysis individually by clicking the relevant button:
            </p>
            <table>
                <tr><th>button</th><th>what it does</th><th>typical wait time</th></tr>
                <tr>
                    <td>run alignment</td>
                    <td>
                        Aligns sequences and generates a conservation
                        profile plot
                    </td>
                    <td>5&ndash;30 seconds</td>
                </tr>
                <tr>
                    <td>scan motifs</td>
                    <td>
                        Scans sequences against PROSITE for known domains
                    </td>
                    <td>10&ndash;60 seconds</td>
                </tr>
                <tr>
                    <td>build tree</td>
                    <td>
                        Constructs a phylogenetic tree from the alignment
                    </td>
                    <td>5&ndash;20 seconds</td>
                </tr>
                <tr>
                    <td>find structures</td>
                    <td>
                        Looks up AlphaFold predicted structures for each
                        sequence
                    </td>
                    <td>30&ndash;120 seconds</td>
                </tr>
            </table>
        </div>

        <div class="help-section">
            <h3>step 4 &mdash; revisit your results</h3>
            <p>
                All searches you run in a session are saved to the
                <a href="history.php">history page</a>. You can return
                to any previous run and re-run any analyses you have
                not yet completed. Your data remains in the database
                for the duration of your session.
            </p>
        </div>
    </div>

    <!-- ── Interpreting results ─────────────────────────────────── -->
    <div class="card" id="interpreting">
        <h2>interpreting results</h2>

        <div class="help-section">
            <h3>conservation plot</h3>
            <p>
                The conservation plot shows a bar for each position in
                the multiple sequence alignment. The height of the bar
                is the <strong>conservation score</strong> — a value
                between 0 and 1 representing the fraction of sequences
                that share the most common amino acid at that position.
            </p>
            <p>
                A score of <strong>1.0</strong> (dark purple) means every
                sequence has exactly the same amino acid at that position —
                this is called <em>full conservation</em> and typically
                indicates that the position is critical for the protein's
                function or structure. Changing it tends to break the
                protein. A score close to <strong>0</strong> means almost
                every sequence has a different amino acid — this position
                is highly variable and likely tolerates substitution.
            </p>
            <p>
                The dashed orange line shows the <strong>mean conservation
                score</strong> across the whole alignment. For a typical
                functional protein, you would expect a mean conservation
                above 0.7. Values above 0.9 indicate very strong
                evolutionary constraint — the protein sequence is under
                high purifying selection.
                <a href="https://www.nature.com/articles/nrg2814"
                   target="_blank">
                    Further reading: sequence conservation and function
                    (Nature Reviews Genetics)
                </a>
            </p>
        </div>

        <div class="help-section">
            <h3>motif scan results</h3>
            <p>
                A motif hit means that a region of your sequence matches
                a pattern in the
                <a href="https://prosite.expasy.org/" target="_blank">
                    PROSITE database
                </a>.
                PROSITE patterns are derived from known protein families
                and describe short, specific sequence signatures associated
                with particular biological functions — for example, an
                active site, a binding pocket, or a post-translational
                modification site.
            </p>
            <p>
                The <strong>start</strong> and <strong>end</strong>
                positions tell you where in the sequence the motif is
                located. If the same motif appears at the same position
                across multiple species, this is strong evidence that
                it is functionally important. Click "view on PROSITE"
                to read the full description of any motif found.
            </p>
            <div class="info-box">
                <strong>Note:</strong> Not finding any motifs does not
                mean the protein has no known function — it may simply
                mean that PROSITE does not yet have a characterised
                pattern for this protein family, or that the sequences
                are too divergent from the reference patterns.
            </div>
        </div>

        <div class="help-section">
            <h3>phylogenetic tree</h3>
            <p>
                The tree shows inferred evolutionary relationships between
                your sequences. Each <strong>leaf node</strong> (orange
                circle at the tip) represents one sequence. Internal nodes
                (purple circles) represent inferred common ancestors.
                Sequences on shorter branches connecting to the same node
                are more closely related to each other.
            </p>
            <p>
                You can interact with the tree by clicking on any node to
                collapse or expand branches, clicking "toggle layout" to
                switch between a rectangular and circular (radial) layout,
                and using scroll to zoom and drag to pan.
            </p>
            <div class="warning-box">
                <strong>Limitation:</strong> The tree in ProtExplorer is a
                <em>guide tree</em> produced by Clustal Omega, not a
                rigorously inferred phylogenetic tree. It is suitable for
                exploring sequence relationships but should not be used for
                publication-quality phylogenetic inference. For that,
                dedicated tools such as
                <a href="https://www.iqtree.org/" target="_blank">IQ-TREE</a>
                or
                <a href="https://beast.community/" target="_blank">BEAST</a>
                are recommended.
            </div>
        </div>

        <div class="help-section">
            <h3>structure links</h3>
            <p>
                Where available, ProtExplorer retrieves a link to an
                <a href="https://alphafold.ebi.ac.uk/" target="_blank">
                    AlphaFold
                </a>
                predicted 3D structure for each sequence. AlphaFold is
                an AI system developed by DeepMind that predicts protein
                structures from sequence alone with high accuracy.
            </p>
            <p>
                The <strong>PAE image</strong> (Predicted Aligned Error)
                shows AlphaFold's confidence in the relative positions
                of pairs of residues — dark blue squares indicate high
                confidence. The <strong>pLDDT score</strong> (per-residue
                confidence) is shown as a percentage — scores above 90
                are considered very high confidence, 70&ndash;90 are
                confident, and below 70 are low confidence regions that
                may be disordered.
                <a href="https://www.ebi.ac.uk/training/online/courses/alphafold/inputs-and-outputs/evaluating-alphafolds-predicted-structures-using-confidence-scores/"
                   target="_blank">
                    Understanding AlphaFold confidence scores (EBI)
                </a>
            </p>
            <div class="info-box">
                <strong>Note:</strong> AlphaFold structures are only
                available for sequences with a known UniProt accession.
                Many predicted/genomic sequences from NCBI (e.g. those
                with XP_ or KAN accession prefixes) may not have a
                direct UniProt entry and will show "no AlphaFold
                structure" — a link to the NCBI protein page is
                provided instead.
            </div>
        </div>
    </div>

    <!-- ── Analyses in detail ───────────────────────────────────── -->
    <div class="card" id="analyses">
        <h2>analyses in detail
            <span style="font-size:0.8rem; font-weight:400;
                         color:var(--text-muted);">
                (for bioinformaticians)
            </span>
        </h2>

        <div class="help-section">
            <h3>sequence retrieval</h3>
            <p>
                Sequences are retrieved from the NCBI Protein database
                using the Entrez E-utilities API via BioPython's
                <code>Bio.Entrez</code> module. The search query is
                constructed as:
                <code>[protein name][Protein Name] AND [taxon][Organism]</code>.
                Results are fetched in FASTA format using
                <code>efetch</code> with <code>rettype="fasta"</code>.
                An NCBI API key is used to increase the rate limit
                from 3 to 10 requests per second.
                <a href="https://www.ncbi.nlm.nih.gov/books/NBK179288/"
                   target="_blank">
                    NCBI E-utilities documentation
                </a>
            </p>
        </div>

        <div class="help-section">
            <h3>multiple sequence alignment</h3>
            <p>
                Clustal Omega is called via Python
                <code>subprocess</code> with the flags
                <code>-i [fasta] -o [output] --force --outfmt=fasta</code>.
                Conservation is calculated per-column as the fraction of
                non-gap characters equal to the modal amino acid at that
                position, i.e.
                <code>score = max_count / n_sequences</code>.
                Gap characters are excluded from the denominator.
            </p>
        </div>

        <div class="help-section">
            <h3>motif scanning</h3>
            <p>
                Each sequence is written to a temporary single-sequence
                FASTA file and scanned individually with
                <code>patmatmotifs -sequence [fasta] -outfile [out]
                -full Y</code>.
                Output is parsed by reading the
                <code>Length =</code>, <code>Start =</code>,
                <code>End =</code> and <code>Motif =</code> fields from
                each hit block. Temporary files are deleted after parsing.
                Results are written as a pipe-delimited flat file for
                PHP to read.
            </p>
        </div>

        <div class="help-section">
            <h3>phylogenetic tree</h3>
            <p>
                A guide tree is generated by Clustal Omega using
                <code>--guidetree-out [newick]</code> with
                <code>--outfmt clustal -o /dev/null --force</code>.
                The Newick string is passed to the browser as a JSON
                string and parsed client-side using a custom recursive
                descent parser. The tree is rendered using D3.js v7
                <code>d3.tree()</code> for linear layout and
                <code>d3.tree().size([2*Math.PI, radius])</code>
                for radial layout, with
                <code>d3.linkHorizontal()</code> and
                <code>d3.linkRadial()</code> respectively.
            </p>
        </div>

        <div class="help-section">
            <h3>structure lookup</h3>
            <p>
                For each sequence, the NCBI GenBank record is fetched
                via <code>Entrez.efetch(rettype="gb")</code> and
                searched for a
                <code>db_xref="UniProtKB/..."</code> cross-reference.
                If found, the UniProt accession is used to query the
                AlphaFold API at
                <code>https://alphafold.ebi.ac.uk/api/prediction/[ID]</code>.
                If no cross-reference is found in the GenBank record,
                the UniProt REST API is queried by organism name as a
                fallback. The PAE image URL, pLDDT score and sequence
                coverage are extracted from the AlphaFold API response.
            </p>
        </div>
    </div>

    <!-- ── FAQ ──────────────────────────────────────────────────── -->
    <div class="card">
        <h2>frequently asked questions</h2>

        <div class="faq-item">
            <div class="faq-q">
                Why did my search return no sequences?
            </div>
            <div class="faq-a">
                This usually means the protein name or taxonomic group
                you entered does not match any entries in the NCBI
                Protein database. Try using a broader taxonomic group
                (e.g. "Vertebrata" instead of "Aves"), or check the
                spelling of your protein name against NCBI.
                <a href="https://www.ncbi.nlm.nih.gov/protein"
                   target="_blank">
                    Search NCBI Protein directly to check
                </a>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-q">
                Why does the analysis take a long time?
            </div>
            <div class="faq-a">
                Fetching from NCBI and running alignment and motif
                scanning on many sequences takes time. The structure
                lookup is particularly slow because it makes an
                individual API call for each sequence. Using a smaller
                maximum sequence count (10&ndash;20) will speed things
                up considerably.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-q">
                Why are some sequences missing from the motif scan?
            </div>
            <div class="faq-a">
                Sequences listed as "no motifs detected" simply do not
                contain any subsequence matching a PROSITE pattern.
                This is common for predicted/hypothetical proteins or
                proteins from less well-studied organisms. It does not
                necessarily mean the protein has no function.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-q">
                Why do some sequences show "no AlphaFold structure"?
            </div>
            <div class="faq-a">
                AlphaFold structures are indexed by UniProt accession.
                Many NCBI protein sequences — particularly predicted
                sequences with XP_, KAN_, KAJ_ or similar prefixes —
                do not have a corresponding reviewed UniProt entry and
                therefore cannot be looked up in AlphaFold. A direct
                NCBI link is provided as a fallback.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-q">
                Will my results still be there if I close the browser?
            </div>
            <div class="faq-a">
                Your sequences and analysis files are stored on the
                server and persist between sessions. However, the
                history page uses browser session storage — if you
                close the browser, your session history will be lost.
                You can still access your results directly if you
                know your run ID.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-q">
                Is the phylogenetic tree suitable for publication?
            </div>
            <div class="faq-a">
                No — the tree is a Clustal Omega guide tree, which
                uses a simplified neighbour-joining approach and is
                intended for alignment guidance rather than rigorous
                phylogenetic inference. For publication, use a
                dedicated tool such as
                <a href="https://www.iqtree.org/" target="_blank">
                    IQ-TREE
                </a>
                or
                <a href="https://mafft.cbrc.jp/alignment/software/"
                   target="_blank">MAFFT</a>
                + RAxML.
            </div>
        </div>

    </div>

    <!-- ── Glossary ──────────────────────────────────────────────── -->
    <div class="card">
        <h2>glossary</h2>

        <div class="glossary-item">
            <span class="glossary-term">amino acid</span>
            <span class="glossary-def">
                The building block of proteins. There are 20 standard
                amino acids, each with different chemical properties.
                The sequence of amino acids determines a protein's
                shape and function.
            </span>
        </div>
        <div class="glossary-item">
            <span class="glossary-term">alignment</span>
            <span class="glossary-def">
                The process of arranging multiple protein sequences so
                that equivalent positions are placed in the same column,
                allowing direct comparison. Gaps (dashes) are inserted
                where one sequence has an insertion or deletion relative
                to another.
            </span>
        </div>
        <div class="glossary-item">
            <span class="glossary-term">conservation</span>
            <span class="glossary-def">
                The degree to which a particular position in a protein
                sequence remains the same across different species.
                High conservation suggests functional or structural
                importance.
            </span>
        </div>
        <div class="glossary-item">
            <span class="glossary-term">FASTA format</span>
            <span class="glossary-def">
                A standard text format for protein and DNA sequences.
                Each sequence starts with a header line beginning with
                "&gt;", followed by the sequence itself on subsequent lines.
            </span>
        </div>
        <div class="glossary-item">
            <span class="glossary-term">motif</span>
            <span class="glossary-def">
                A short, conserved sequence pattern associated with a
                specific biological function, such as an enzyme active
                site or a binding domain. PROSITE catalogues known
                protein motifs.
            </span>
        </div>
        <div class="glossary-item">
            <span class="glossary-term">NCBI</span>
            <span class="glossary-def">
                National Center for Biotechnology Information — a US
                government organisation that maintains major biological
                databases including GenBank (DNA sequences) and the
                Protein database used by ProtExplorer.
            </span>
        </div>
        <div class="glossary-item">
            <span class="glossary-term">pLDDT</span>
            <span class="glossary-def">
                Per-residue Local Distance Difference Test — a confidence
                score (0&ndash;100) used by AlphaFold to indicate how
                confident it is in the predicted position of each amino
                acid. Scores above 90 are considered very high confidence.
            </span>
        </div>
        <div class="glossary-item">
            <span class="glossary-term">phylogenetic tree</span>
            <span class="glossary-def">
                A diagram showing inferred evolutionary relationships
                between sequences or species, based on their similarity.
                Closely related sequences cluster together on shorter
                branches.
            </span>
        </div>
        <div class="glossary-item">
            <span class="glossary-term">PROSITE</span>
            <span class="glossary-def">
                A database of documented protein families, domains and
                functional sites, maintained by the Swiss Institute of
                Bioinformatics. It describes protein motifs as patterns
                or profiles that can be used to identify functional
                regions in new sequences.
            </span>
        </div>
        <div class="glossary-item">
            <span class="glossary-term">taxonomic group</span>
            <span class="glossary-def">
                A named group of organisms in the biological classification
                system. Examples: Aves (birds), Mammalia (mammals),
                Primates (apes, monkeys, humans). More specific groups
                contain fewer, more closely related species.
            </span>
        </div>
        <div class="glossary-item">
            <span class="glossary-term">UniProt</span>
            <span class="glossary-def">
                A comprehensive database of protein sequences and
                functional annotation, maintained by a consortium of
                European institutes. UniProt accessions are required
                to look up AlphaFold predicted structures.
            </span>
        </div>
    </div>

</div>

<footer>
    ProtExplorer &mdash; IWD2 assessed website &mdash;
    <a href="credits.php">credits &amp; AI usage</a>
</footer>

</body>
</html>
