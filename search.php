<?php
session_start();
// Clear current run so results page always shows the latest search
unset($_SESSION['current_run_id']);
$active_page = 'search';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProtExplorer &mdash; search</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Show/hide the free text boxes depending on dropdown selection
        function toggleCustomProtein() {
            var sel = document.getElementById('protein_select').value;
            var box = document.getElementById('custom_protein_box');
            box.style.display = (sel === 'other') ? 'block' : 'none';
            document.getElementById('custom_protein').required = (sel === 'other');
        }

        function toggleCustomTaxon() {
            var sel = document.getElementById('taxon_select').value;
            var box = document.getElementById('custom_taxon_box');
            box.style.display = (sel === 'other') ? 'block' : 'none';
            document.getElementById('custom_taxon').required = (sel === 'other');
        }

        function validateForm() {
            var protein_sel = document.getElementById('protein_select').value;
            var taxon_sel   = document.getElementById('taxon_select').value;
            var maxseq      = parseInt(document.getElementById('max_sequences').value);

            if (protein_sel === '') {
                alert('Please select or enter a protein family.');
                return false;
            }
            if (protein_sel === 'other' &&
                document.getElementById('custom_protein').value.trim() === '') {
                alert('Please type a protein family in the box below the dropdown.');
                return false;
            }
            if (taxon_sel === '') {
                alert('Please select or enter a taxonomic group.');
                return false;
            }
            if (taxon_sel === 'other' &&
                document.getElementById('custom_taxon').value.trim() === '') {
                alert('Please type a taxonomic group in the box below the dropdown.');
                return false;
            }
            if (isNaN(maxseq) || maxseq < 5 || maxseq > 200) {
                alert('Maximum sequences must be a number between 5 and 200.');
                return false;
            }
            // Show loading overlay
        showLoading(
            'Fetching sequences...',
            'Retrieving protein sequences from NCBI. This may take up to 30 seconds depending on the number of sequences requested.',
            [
                'Connecting to NCBI Entrez',
                'Searching protein database',
                'Fetching sequences in FASTA format',
                'Storing sequences in database',
                'Preparing results page'
            ]
        );
        return true;
        }
    </script>
</head>
<body>

<?php require_once 'menuf.php'; ?>

<div class="page-wrap">

    <div class="page-header" style="margin-top:2rem;">
        <h1>Search</h1>
        <p>Select a protein family and taxonomic group to retrieve and analyse sequences from NCBI</p>
    </div>

    <?php if (isset($_SESSION['search_error'])): ?>
    <div class="alert alert-error">
        <strong>Search failed:</strong>
        <?php echo $_SESSION['search_error']; unset($_SESSION['search_error']); ?>
        <br><br>
        <strong>Suggestions:</strong>
        <ul style="margin-top:0.4rem; margin-left:1.2rem;
                   line-height:1.8; font-size:0.88rem;">
            <li>Check the spelling of your protein family name</li>
            <li>Try a broader taxonomic group
                (e.g. "Vertebrata" instead of "Aves")</li>
            <li>Try searching
                <a href="https://www.ncbi.nlm.nih.gov/protein"
                   target="_blank">NCBI Protein directly</a>
                to verify your search terms return results</li>
            <li>Reduce the maximum number of sequences
                if the request timed out</li>
        </ul>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <h2>search parameters</h2>

        <form action="process_search.php" method="post"
	      onsubmit="return validateForm()">

            <!-- Protein family -->
            <div class="form-group">
                <label for="protein_select">protein family</label>
                <select id="protein_select" name="protein_select"
                        onchange="toggleCustomProtein()">
                    <option value="">-- select a protein family --</option>
                    <option value="glucose-6-phosphatase">Glucose-6-phosphatase</option>
                    <option value="kinase">Kinase</option>
                    <option value="ABC transporter">ABC transporter</option>
                    <option value="adenyl cyclase">Adenyl cyclase</option>
                    <option value="cytochrome P450">Cytochrome P450</option>
                    <option value="hemoglobin">Hemoglobin</option>
                    <option value="insulin">Insulin</option>
                    <option value="collagen">Collagen</option>
                    <option value="actin">Actin</option>
                    <option value="myosin">Myosin</option>
                    <option value="DNA polymerase">DNA polymerase</option>
                    <option value="RNA polymerase">RNA polymerase</option>
                    <option value="carbonic anhydrase">Carbonic anhydrase</option>
                    <option value="sodium-potassium ATPase">Sodium-potassium ATPase</option>
                    <option value="other">other (type below)</option>
                </select>
                <div id="custom_protein_box" style="display:none; margin-top:0.5rem;">
                    <input type="text" id="custom_protein" name="custom_protein"
                           placeholder="e.g. serine protease">
                    <span class="hint">type the protein family name as you would search it in NCBI</span>
                </div>
            </div>

            <!-- Taxonomic group -->
            <div class="form-group">
                <label for="taxon_select">taxonomic group</label>
                <select id="taxon_select" name="taxon_select"
                        onchange="toggleCustomTaxon()">
                    <option value="">-- select a taxonomic group --</option>
                    <option value="Aves">Aves (birds)</option>
                    <option value="Mammalia">Mammalia (mammals)</option>
                    <option value="Reptilia">Reptilia (reptiles)</option>
                    <option value="Actinopterygii">Actinopterygii (ray-finned fish)</option>
                    <option value="Insecta">Insecta (insects)</option>
                    <option value="Vertebrata">Vertebrata (vertebrates)</option>
                    <option value="Rodentia">Rodentia (rodents)</option>
                    <option value="Primates">Primates</option>
                    <option value="Amphibia">Amphibia (amphibians)</option>
                    <option value="Chondrichthyes">Chondrichthyes (cartilaginous fish)</option>
                    <option value="Arachnida">Arachnida (spiders, scorpions)</option>
                    <option value="Fungi">Fungi</option>
                    <option value="Viridiplantae">Viridiplantae (green plants)</option>
                    <option value="Bacteria">Bacteria</option>
                    <option value="Archaea">Archaea</option>
                    <option value="Nematoda">Nematoda (roundworms)</option>
                    <option value="other">other (type below)</option>
                </select>
                <div id="custom_taxon_box" style="display:none; margin-top:0.5rem;">
                    <input type="text" id="custom_taxon" name="custom_taxon"
                           placeholder="e.g. Felidae">
                    <span class="hint">use the scientific name of the taxonomic group</span>
                </div>
            </div>

            <!-- Max sequences -->
            <div class="form-group">
                <label for="max_sequences">maximum number of sequences to fetch</label>
                <input type="number" id="max_sequences" name="max_sequences"
                       value="20" min="5" max="200">
                <span class="hint">between 5 and 200 &mdash; larger numbers will take longer to process</span>
            </div>

            <button type="submit" class="btn btn-primary">
                run analysis
            </button>

        </form>
    </div>

    <div class="card">
        <h2>not sure what to search?</h2>
        <p>Try the <a href="example.php">example dataset</a> first &mdash;
        it shows pre-processed results for glucose-6-phosphatase in Aves
        so you can see what the outputs look like before running your own search.</p>
    </div>

</div>

<footer>
    ProtExplorer &mdash; IWD2 assessed website &mdash;
    <a href="credits.php">credits &amp; AI usage</a>
</footer>

</body>
</html>
