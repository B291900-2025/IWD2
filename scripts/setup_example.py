#!/usr/bin/python3
"""
setup_example.py
One-off setup script for the ProtExplorer example dataset.
Fetches glucose-6-phosphatase sequences from Aves, runs all
analyses and populates the ExampleDataset table.

Run once from the Website directory:
    python3 scripts/setup_example.py

All output files are saved to results/example_*
"""

import os
os.environ['MPLCONFIGDIR'] = '/tmp'

import sys
import re
import subprocess
import json
import urllib.request
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.patches as mpatches
import numpy as np
from Bio import Entrez, SeqIO

# ── NCBI credentials ─────────────────────────────────────────────
Entrez.email   = "R.Dey-5@sms.ed.ac.uk"
Entrez.api_key = "59d15b93ed133bd97bc7d1e9bafc64856c09"

# ── Paths ─────────────────────────────────────────────────────────
script_dir   = os.path.dirname(os.path.abspath(__file__))
base_dir     = os.path.dirname(script_dir)
results_dir  = os.path.join(base_dir, 'results')

fasta_path   = os.path.join(results_dir, 'example_sequences.fasta')
aligned_path = os.path.join(results_dir, 'example_aligned.fasta')
newick_path  = os.path.join(results_dir, 'example_tree.nwk')
cons_plot    = os.path.join(results_dir, 'example_conservation.png')
tree_plot    = os.path.join(results_dir, 'example_tree.png')
motif_path   = os.path.join(results_dir, 'example_motifs.txt')
struct_path  = os.path.join(results_dir, 'example_structures.txt')
stats_path   = os.path.join(results_dir, 'example_stats.txt')

print("ProtExplorer example dataset setup")
print("=" * 40)

# ── Step 1: Fetch sequences from NCBI ────────────────────────────
print("\n[1/6] Fetching G6Pase sequences from Aves...")

query = "glucose-6-phosphatase[Protein Name] AND Aves[Organism]"

search_handle  = Entrez.esearch(
    db="protein",
    term=query,
    retmax=15,
    usehistory="y"
)
search_results = Entrez.read(search_handle)
search_handle.close()

id_list = search_results["IdList"]
print(f"    Found {len(id_list)} sequences")

fetch_handle = Entrez.efetch(
    db="protein",
    id=id_list,
    rettype="fasta",
    retmode="text"
)
records = list(SeqIO.parse(fetch_handle, "fasta"))
fetch_handle.close()

print(f"    Retrieved {len(records)} sequences")

# Write FASTA file
sequences_data = []
with open(fasta_path, 'w') as f:
    for record in records:
        f.write(f">{record.description}\n")
        seq = str(record.seq)
        for i in range(0, len(seq), 60):
            f.write(seq[i:i+60] + "\n")

        accession = record.id
        species_match = re.search(r'\[([^\]]+)\]', record.description)
        species = species_match.group(1) if species_match else 'Unknown'
        sequences_data.append({
            'accession': accession,
            'species':   species,
            'sequence':  str(record.seq),
            'length':    len(record.seq)
        })

print(f"    FASTA written to {fasta_path}")

# ── Step 2: Multiple sequence alignment ──────────────────────────
print("\n[2/6] Running Clustal Omega alignment...")

clustalo_cmd = [
    'clustalo',
    '-i', fasta_path,
    '-o', aligned_path,
    '--force',
    '--outfmt=fasta'
]
result = subprocess.run(clustalo_cmd, capture_output=True, text=True)

if result.returncode != 0:
    print(f"    ERROR: {result.stderr.strip()}")
    sys.exit(1)

print(f"    Alignment written to {aligned_path}")

# ── Step 3: Conservation analysis ────────────────────────────────
print("\n[3/6] Calculating conservation and generating plot...")

aligned_seqs = {}
current_id   = None
with open(aligned_path, 'r') as f:
    for line in f:
        line = line.strip()
        if line.startswith('>'):
            current_id = line[1:].split()[0]
            aligned_seqs[current_id] = ''
        elif current_id:
            aligned_seqs[current_id] += line

seq_list   = list(aligned_seqs.values())
aln_length = len(seq_list[0])
n_seqs     = len(seq_list)

conservation = []
for i in range(aln_length):
    col = [seq[i] for seq in seq_list if seq[i] != '-']
    if len(col) == 0:
        conservation.append(0.0)
        continue
    counts    = {}
    for aa in col:
        counts[aa] = counts.get(aa, 0) + 1
    max_count  = max(counts.values())
    conservation.append(max_count / n_seqs)

conservation  = np.array(conservation)
mean_cons     = float(np.mean(conservation))
max_cons      = float(np.max(conservation))
min_cons      = float(np.min(conservation))
fully_cons    = int(np.sum(conservation == 1.0))
highly_cons   = int(np.sum(conservation >= 0.8))
most_cons_pos = int(np.argmax(conservation)) + 1
least_cons_pos= int(np.argmin(conservation)) + 1

# Plot
fig, ax = plt.subplots(figsize=(14, 4))
colours = []
for score in conservation:
    if score == 1.0:
        colours.append('#5a4fb0')
    elif score >= 0.8:
        colours.append('#9d8fe0')
    elif score >= 0.5:
        colours.append('#c8bfee')
    else:
        colours.append('#e8e4f8')

ax.bar(range(1, aln_length + 1), conservation,
       color=colours, width=1.0, linewidth=0)
ax.set_xlabel('Alignment position', fontsize=11)
ax.set_ylabel('Conservation score', fontsize=11)
ax.set_title(
    f'G6Pase conservation across Aves '
    f'({n_seqs} sequences, {aln_length} positions)',
    fontsize=12
)
ax.set_ylim(0, 1.05)
ax.set_xlim(0, aln_length + 1)
ax.axhline(y=mean_cons, color='#e07b6a', linestyle='--',
           linewidth=1.2)

patches = [
    mpatches.Patch(color='#5a4fb0', label='Fully conserved (1.0)'),
    mpatches.Patch(color='#9d8fe0', label='Highly conserved (>=0.8)'),
    mpatches.Patch(color='#c8bfee', label='Moderately conserved (>=0.5)'),
    mpatches.Patch(color='#e8e4f8', label='Variable (<0.5)')
]
ax.legend(handles=patches, loc='upper right', fontsize=9)
plt.tight_layout()
plt.savefig(cons_plot, dpi=150, bbox_inches='tight')
plt.close()

print(f"    Mean conservation: {mean_cons:.4f}")
print(f"    Conservation plot written to {cons_plot}")

# ── Step 4: Phylogenetic tree ─────────────────────────────────────
print("\n[4/6] Building phylogenetic tree...")

clustalo_tree = [
    'clustalo',
    '-i',        fasta_path,
    '--outfmt',  'clustal',
    '-o',        '/dev/null',
    '--guidetree-out', newick_path,
    '--force'
]
result = subprocess.run(clustalo_tree, capture_output=True, text=True)

if result.returncode != 0:
    print(f"    ERROR: {result.stderr.strip()}")
else:
    print(f"    Newick tree written to {newick_path}")

    # Simple static tree plot
    with open(newick_path, 'r') as f:
        newick_str = f.read().strip()

    labels = re.findall(
        r'([A-Za-z0-9_\.]+)(?=[,\)])',
        re.sub(r'\s+', '', re.sub(r':[0-9.eE+\-]+', '', newick_str))
    )

    fig, ax = plt.subplots(figsize=(10, max(4, len(labels) * 0.5)))
    n = len(labels)
    for i, label in enumerate(labels):
        ax.plot([0.5, 1.0], [i, i], color='#7c6fcd', linewidth=1.2)
        ax.text(1.02, i, label, va='center', fontsize=8, color='#2d2a3e')
        ax.plot(1.0, i, 'o', color='#7c6fcd', markersize=5)
    ax.plot([0.5, 0.5], [0, n-1], color='#5a4fb0', linewidth=1.5)
    ax.plot([0.0, 0.5], [n/2-0.5, n/2-0.5], color='#5a4fb0', linewidth=1.5)
    ax.set_xlim(-0.1, 1.8)
    ax.set_ylim(-0.5, n-0.5)
    ax.axis('off')
    ax.set_title(f'Phylogenetic tree — {n} Aves G6Pase sequences',
                 fontsize=11, color='#2d2a3e')
    plt.tight_layout()
    plt.savefig(tree_plot, dpi=150, bbox_inches='tight', facecolor='white')
    plt.close()
    print(f"    Tree plot written to {tree_plot}")

# ── Step 5: Motif scanning ────────────────────────────────────────
print("\n[5/6] Running PROSITE motif scan...")

total_motifs = 0
motif_results = []

for record in records:
    accession = record.id
    species_match = re.search(r'\[([^\]]+)\]', record.description)
    species = species_match.group(1) if species_match else 'Unknown'

    tmp_fasta = os.path.join(results_dir, f'tmp_ex_{accession}.fasta')
    tmp_out   = os.path.join(results_dir, f'tmp_ex_{accession}.patmatmotifs')

    with open(tmp_fasta, 'w') as tmp:
        tmp.write(f'>{record.description}\n')
        seq = str(record.seq)
        for i in range(0, len(seq), 60):
            tmp.write(seq[i:i+60] + '\n')

    subprocess.run(
        ['patmatmotifs', '-sequence', tmp_fasta,
         '-outfile', tmp_out, '-full', 'Y'],
        capture_output=True, text=True
    )

    motifs_for_seq = []
    if os.path.exists(tmp_out):
        with open(tmp_out, 'r') as out_f:
            lines = out_f.readlines()

        i = 0
        while i < len(lines):
            line = lines[i].strip()
            if line.startswith('Length ='):
                length = line.replace('Length =', '').strip()
                start  = ''
                end    = ''
                motif  = ''
                j = i + 1
                while j < len(lines) and \
                      not lines[j].strip().startswith('#---'):
                    l = lines[j].strip()
                    if l.startswith('Start ='):
                        m = re.search(r'position (\d+)', l)
                        if m:
                            start = m.group(1)
                    elif l.startswith('End ='):
                        m = re.search(r'position (\d+)', l)
                        if m:
                            end = m.group(1)
                    elif l.startswith('Motif ='):
                        motif = l.replace('Motif =', '').strip()
                    j += 1
                if motif:
                    motifs_for_seq.append({
                        'motif':  motif,
                        'start':  start,
                        'end':    end,
                        'length': length
                    })
                    total_motifs += 1
                i = j
            else:
                i += 1

    motif_results.append({
        'accession': accession,
        'species':   species,
        'motifs':    motifs_for_seq
    })

    if os.path.exists(tmp_fasta):
        os.remove(tmp_fasta)
    if os.path.exists(tmp_out):
        os.remove(tmp_out)

with open(motif_path, 'w') as out:
    for res in motif_results:
        if len(res['motifs']) == 0:
            out.write(f"NOMOTIFS|{res['accession']}|{res['species']}\n")
        else:
            for m in res['motifs']:
                out.write(
                    f"MOTIF|{res['accession']}|{res['species']}|"
                    f"{m['motif']}|{m['start']}|{m['end']}|"
                    f"{m['length']}|\n"
                )

print(f"    Total motifs found: {total_motifs}")
print(f"    Motif results written to {motif_path}")

# ── Step 6: Save summary stats ────────────────────────────────────
print("\n[6/6] Saving summary statistics...")

with open(stats_path, 'w') as f:
    f.write(f"n_sequences={len(sequences_data)}\n")
    f.write(f"mean_cons={mean_cons:.4f}\n")
    f.write(f"max_cons={max_cons:.4f}\n")
    f.write(f"min_cons={min_cons:.4f}\n")
    f.write(f"fully_cons={fully_cons}\n")
    f.write(f"highly_cons={highly_cons}\n")
    f.write(f"most_cons_pos={most_cons_pos}\n")
    f.write(f"least_cons_pos={least_cons_pos}\n")
    f.write(f"aln_length={aln_length}\n")
    f.write(f"total_motifs={total_motifs}\n")

print(f"    Stats written to {stats_path}")

# ── Done ──────────────────────────────────────────────────────────
print("\n" + "=" * 40)
print("Setup complete!")
print(f"Sequences fetched:   {len(sequences_data)}")
print(f"Mean conservation:   {mean_cons:.4f}")
print(f"Fully conserved pos: {fully_cons}")
print(f"Total motifs found:  {total_motifs}")
print("=" * 40)
print("\nNow populate the ExampleDataset table by running:")
print("mysql -u s2793337 -p s2793337_website < scripts/populate_example.sql")
