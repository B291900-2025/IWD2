<?php
session_start();
$active_page = 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProtExplorer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php require_once 'menuf.php'; ?>

<div class="page-wrap">

    <div class="page-header" style="margin-top:2rem;">
        <h1>ProtExplorer</h1>
        <p>Where sequence data meets biological meaning</p>
    </div>

    <div class="grid-3">

        <div class="card" style="text-align:center;">
            <h2>run a search</h2>
            <p class="card-desc">
                Enter a protein family and taxonomic group to fetch
                and analyse sequences from NCBI.
            </p>
            <a href="search.php" class="btn btn-primary">start</a>
        </div>

        <div class="card" style="text-align:center;">
            <h2>try the example</h2>
            <p class="card-desc">
                Pre-processed results for glucose-6-phosphatase
                in Aves &mdash; no waiting required.
            </p>
            <a href="example.php" class="btn btn-accent">view example</a>
        </div>

        <div class="card" style="text-align:center;">
            <h2>revisit past runs</h2>
            <p class="card-desc">
                Return to any search you have run previously
                in this session.
            </p>
            <a href="history.php" class="btn btn-outline">history</a>
        </div>

    </div>

    <div class="card">
        <h2>analyses performed</h2>
        <table>
            <tr>
                <th>step</th>
                <th>tool</th>
                <th>output</th>
            </tr>
            <tr>
                <td>sequence retrieval</td>
                <td>NCBI Entrez (Bio.Entrez)</td>
                <td>FASTA file, stored in database</td>
            </tr>
            <tr>
                <td>multiple alignment</td>
                <td>Clustal Omega</td>
                <td>aligned FASTA + conservation plot</td>
            </tr>
            <tr>
                <td>motif scanning</td>
                <td>EMBOSS patmatmotifs</td>
                <td>PROSITE domain report</td>
            </tr>
            <tr>
                <td>phylogenetic tree</td>
                <td>Clustal Omega (Newick output)</td>
                <td>interactive tree visualisation</td>
            </tr>
            <tr>
                <td>structure links</td>
                <td>AlphaFold API</td>
                <td>links to predicted 3D structures</td>
            </tr>
        </table>
    </div>

</div>

<footer>
    ProtExplorer &mdash; IWD2 assessed website &mdash;
    <a href="credits.php">credits &amp; AI usage</a>
</footer>

</body>
</html>
