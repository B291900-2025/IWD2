#!/usr/bin/python3
"""
run_conservation.py
Runs Clustal Omega on a FASTA file, then generates a conservation
plot using matplotlib. Summary statistics are printed to stdout.

Usage:
    python3 run_conservation.py <fasta_path> <run_id> <results_dir>

Prints:
    SUCCESS:mean_conservation
    ERROR:message
"""

import sys
import os
os.environ['MPLCONFIGDIR'] = '/tmp'
import subprocess
import matplotlib
matplotlib.use('Agg')  # non-interactive backend for server use
import matplotlib.pyplot as plt
import matplotlib.patches as mpatches
import numpy as np

# ── arguments ────────────────────────────────────────────────────
if len(sys.argv) != 4:
    print("ERROR:wrong number of arguments")
    sys.exit(1)

fasta_path  = sys.argv[1]
run_id      = sys.argv[2]
results_dir = sys.argv[3]

aligned_path = os.path.join(results_dir, f'run_{run_id}_aligned.fasta')
plot_path    = os.path.join(results_dir, f'run_{run_id}_conservation.png')

try:
    # ── 1. Run Clustal Omega ─────────────────────────────────────
    if not os.path.exists(fasta_path):
        print(f"ERROR:FASTA file not found at {fasta_path}")
        sys.exit(1)

    clustalo_cmd = [
        'clustalo',
        '-i', fasta_path,
        '-o', aligned_path,
        '--force',
        '--outfmt=fasta'
    ]

    result = subprocess.run(
        clustalo_cmd,
        capture_output=True,
        text=True
    )

    if result.returncode != 0:
        print(f"ERROR:Clustal Omega failed: {result.stderr.strip()}")
        sys.exit(1)

    if not os.path.exists(aligned_path):
        print("ERROR:Aligned file was not created")
        sys.exit(1)

    # ── 2. Parse aligned FASTA ───────────────────────────────────
    sequences  = {}
    current_id = None

    with open(aligned_path, 'r') as f:
        for line in f:
            line = line.strip()
            if line.startswith('>'):
                current_id = line[1:].split()[0]
                sequences[current_id] = ''
            elif current_id:
                sequences[current_id] += line

    if len(sequences) == 0:
        print("ERROR:No sequences parsed from alignment")
        sys.exit(1)

    seq_list   = list(sequences.values())
    aln_length = len(seq_list[0])
    n_seqs     = len(seq_list)

    # ── 3. Calculate conservation per position ───────────────────
    # Conservation = fraction of sequences with the most common
    # non-gap character at each position
    conservation = []

    for i in range(aln_length):
        col = [seq[i] for seq in seq_list if seq[i] != '-']
        if len(col) == 0:
            conservation.append(0.0)
            continue
        counts = {}
        for aa in col:
            counts[aa] = counts.get(aa, 0) + 1
        max_count  = max(counts.values())
        cons_score = max_count / n_seqs
        conservation.append(cons_score)

    conservation = np.array(conservation)

    # ── 4. Summary statistics ────────────────────────────────────
    mean_cons     = float(np.mean(conservation))
    max_cons      = float(np.max(conservation))
    min_cons      = float(np.min(conservation))
    fully_cons    = int(np.sum(conservation == 1.0))
    highly_cons   = int(np.sum(conservation >= 0.8))
    most_cons_pos = int(np.argmax(conservation)) + 1
    least_cons_pos= int(np.argmin(conservation)) + 1

    # ── 5. Generate conservation plot ───────────────────────────
    fig, ax = plt.subplots(figsize=(14, 4))

    # Colour bars by conservation level
    colours = []
    for score in conservation:
        if score == 1.0:
            colours.append('#5a4fb0')   # fully conserved -- dark purple
        elif score >= 0.8:
            colours.append('#9d8fe0')   # highly conserved -- medium purple
        elif score >= 0.5:
            colours.append('#c8bfee')   # moderately conserved -- light purple
        else:
            colours.append('#e8e4f8')   # variable -- very light purple

    ax.bar(range(1, aln_length + 1), conservation,
           color=colours, width=1.0, linewidth=0)

    ax.set_xlabel('Alignment position', fontsize=11)
    ax.set_ylabel('Conservation score', fontsize=11)
    ax.set_title(f'Protein sequence conservation across alignment '
                 f'({n_seqs} sequences, {aln_length} positions)',
                 fontsize=12)
    ax.set_ylim(0, 1.05)
    ax.set_xlim(0, aln_length + 1)

    # Legend
    patches = [
        mpatches.Patch(color='#5a4fb0', label='Fully conserved (1.0)'),
        mpatches.Patch(color='#9d8fe0', label='Highly conserved (≥0.8)'),
        mpatches.Patch(color='#c8bfee', label='Moderately conserved (≥0.5)'),
        mpatches.Patch(color='#e8e4f8', label='Variable (<0.5)')
    ]
    ax.legend(handles=patches, loc='upper right', fontsize=9)

    # Mean conservation line
    ax.axhline(y=mean_cons, color='#e07b6a', linestyle='--',
               linewidth=1.2, label=f'Mean: {mean_cons:.2f}')

    plt.tight_layout()
    plt.savefig(plot_path, dpi=150, bbox_inches='tight')
    plt.close()

    # ── 6. Print summary for PHP to read ────────────────────────
    print(f"SUCCESS:{mean_cons:.4f}|{max_cons:.4f}|{min_cons:.4f}|"
          f"{fully_cons}|{highly_cons}|{most_cons_pos}|"
          f"{least_cons_pos}|{aln_length}|{n_seqs}")

except Exception as e:
    print(f"ERROR:{str(e)}")
    sys.exit(1)
