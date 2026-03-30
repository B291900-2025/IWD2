#!/usr/bin/python3
"""
run_structures.py
For each sequence in the FASTA file, attempts to find a UniProt
accession via NCBI Entrez, then queries the AlphaFold API for a
predicted structure. Falls back to NCBI protein page if not found.

Usage:
    python3 run_structures.py <fasta_path> <run_id> <results_dir>

Prints:
    SUCCESS:N  where N = number of sequences processed
    ERROR:message
"""

import os
os.environ['MPLCONFIGDIR'] = '/tmp'

import sys
import re
import json
import urllib.request
import urllib.error
from Bio import Entrez, SeqIO

# ── arguments ────────────────────────────────────────────────────
if len(sys.argv) != 4:
    print("ERROR:wrong number of arguments")
    sys.exit(1)

fasta_path  = sys.argv[1]
run_id      = sys.argv[2]
results_dir = sys.argv[3]

output_path = os.path.join(results_dir, f'run_{run_id}_structures.txt')

# ── NCBI credentials ─────────────────────────────────────────────
Entrez.email   = "R.Dey-5@sms.ed.ac.uk"
Entrez.api_key = "59d15b93ed133bd97bc7d1e9bafc64856c09"

try:
    if not os.path.exists(fasta_path):
        print(f"ERROR:FASTA file not found at {fasta_path}")
        sys.exit(1)

    # ── Parse FASTA to get accessions ────────────────────────────
    accessions = []
    species_map = {}

    with open(fasta_path, 'r') as f:
        for line in f:
            line = line.strip()
            if line.startswith('>'):
                header    = line[1:]
                accession = header.split()[0]
                species_match = re.search(r'\[([^\]]+)\]', header)
                species = species_match.group(1) if species_match else 'Unknown'
                accessions.append(accession)
                species_map[accession] = species

    if len(accessions) == 0:
        print("ERROR:No sequences found in FASTA file")
        sys.exit(1)

    results = []

    for accession in accessions:
        species     = species_map[accession]
        uniprot_id  = None
        alphafold_url    = None
        alphafold_img    = None
        confidence  = None
        coverage    = None
        ncbi_url    = f"https://www.ncbi.nlm.nih.gov/protein/{accession}"

        # ── Try to find UniProt ID via NCBI GenBank record ───────
        try:
            handle = Entrez.efetch(
                db="protein",
                id=accession,
                rettype="gb",
                retmode="text"
            )
            gb_text = handle.read()
            handle.close()

            uniprot_match = re.search(
                r'db_xref="UniProtKB/[A-Za-z-]+:([A-Z0-9]+)"',
                gb_text
            )
            if uniprot_match:
                uniprot_id = uniprot_match.group(1)

        except Exception:
            pass

        # ── If no UniProt ID, try querying UniProt API directly ──
        if not uniprot_id:
            try:
                # Search UniProt by species name and protein family
                # Extract genus and species from species string
                species_words = species.split()
                if len(species_words) >= 2:
                    organism_query = f"{species_words[0]}+{species_words[1]}"
                else:
                    organism_query = species.replace(' ', '+')

                uniprot_search = (
                    f"https://rest.uniprot.org/uniprotkb/search?"
                    f"query=organism_name:{organism_query}+AND+"
                    f"reviewed:true&format=json&size=1"
                )
                req = urllib.request.Request(
                    uniprot_search,
                    headers={'Accept': 'application/json'}
                )
                with urllib.request.urlopen(req, timeout=10) as response:
                    uniprot_data = json.loads(response.read().decode())

                results_list = uniprot_data.get('results', [])
                if results_list:
                    uniprot_id = results_list[0].get('primaryAccession', None)

            except Exception:
                pass

        # ── Query AlphaFold API if we have a UniProt ID ───────────
        if uniprot_id:
            try:
                af_api_url = f"https://alphafold.ebi.ac.uk/api/prediction/{uniprot_id}"
                req        = urllib.request.Request(
                    af_api_url,
                    headers={'Accept': 'application/json'}
                )
                with urllib.request.urlopen(req, timeout=10) as response:
                    af_data = json.loads(response.read().decode())

                if isinstance(af_data, list) and len(af_data) > 0:
                    entry         = af_data[0]
                    alphafold_url = entry.get('pdbUrl', None)
                    alphafold_img = entry.get('paeImageUrl', None)
                    confidence    = entry.get('globalMetricValue', None)
                    coverage      = entry.get('uniprotEnd', None)

                    # Build the AlphaFold page URL
                    alphafold_page = f"https://alphafold.ebi.ac.uk/entry/{uniprot_id}"
                else:
                    alphafold_page = None

            except Exception:
                alphafold_page = None
        else:
            alphafold_page = None

        results.append({
            'accession':     accession,
            'species':       species,
            'uniprot_id':    uniprot_id    or 'N/A',
            'alphafold_url': alphafold_page or '',
            'alphafold_img': alphafold_img  or '',
            'confidence':    str(round(confidence, 2)) if confidence else 'N/A',
            'coverage':      str(coverage) if coverage else 'N/A',
            'ncbi_url':      ncbi_url
        })

    # ── Write output file for PHP ─────────────────────────────────
    # Format: accession|species|uniprot_id|alphafold_url|alphafold_img|
    #         confidence|coverage|ncbi_url

    with open(output_path, 'w') as out:
        for r in results:
            out.write(
                f"{r['accession']}|{r['species']}|{r['uniprot_id']}|"
                f"{r['alphafold_url']}|{r['alphafold_img']}|"
                f"{r['confidence']}|{r['coverage']}|{r['ncbi_url']}\n"
            )

    print(f"SUCCESS:{len(results)}")

except Exception as e:
    print(f"ERROR:{str(e)}")
    sys.exit(1)
