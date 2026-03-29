#!/usr/bin/python3
"""
fetch_sequences.py
Fetches protein sequences from NCBI and writes them to a FASTA file.

Usage:
    python3 fetch_sequences.py <protein> <taxon> <max_seq> <run_id>

Prints:
    SUCCESS:N  where N = number of sequences fetched
    ERROR:message  if something goes wrong
"""

import sys
import os
from Bio import Entrez, SeqIO

# ── arguments ────────────────────────────────────────────────────
if len(sys.argv) != 5:
    print("ERROR:wrong number of arguments")
    sys.exit(1)

protein = sys.argv[1]
taxon   = sys.argv[2]
max_seq = int(sys.argv[3])
run_id  = sys.argv[4]

# ── NCBI credentials ─────────────────────────────────────────────
Entrez.email   = "R.Dey-5@sms.ed.ac.uk"
Entrez.api_key = "59d15b93ed133bd97bc7d1e9bafc64856c09"

# ── search and fetch ─────────────────────────────────────────────
try:
    query = f"{protein}[Protein Name] AND {taxon}[Organism]"

    search_handle  = Entrez.esearch(
        db="protein",
        term=query,
        retmax=max_seq,
        usehistory="y"
    )
    search_results = Entrez.read(search_handle)
    search_handle.close()

    id_list = search_results["IdList"]

    if len(id_list) == 0:
        print(f"ERROR:No sequences found for '{protein}' in '{taxon}'. "
              f"Try a different search.")
        sys.exit(1)

    fetch_handle = Entrez.efetch(
        db="protein",
        id=id_list,
        rettype="fasta",
        retmode="text"
    )
    records = list(SeqIO.parse(fetch_handle, "fasta"))
    fetch_handle.close()

    if len(records) == 0:
        print("ERROR:Sequences found but could not be retrieved. Please try again.")
        sys.exit(1)

    # ── write FASTA file ─────────────────────────────────────────
    # PHP will read this file and insert into the database via PDO
    script_dir = os.path.dirname(os.path.abspath(__file__))
    results_dir = os.path.join(os.path.dirname(script_dir), 'results')
    fasta_path = os.path.join(results_dir, f'run_{run_id}_sequences.fasta')
    with open(fasta_path, 'w') as fasta_out:
        for record in records:
            fasta_out.write(f">{record.description}\n")
            # write sequence in 60-character lines
            seq = str(record.seq)
            for i in range(0, len(seq), 60):
                fasta_out.write(seq[i:i+60] + "\n")

    print(f"SUCCESS:{len(records)}")

except Exception as e:
    print(f"ERROR:{str(e)}")
    sys.exit(1)
