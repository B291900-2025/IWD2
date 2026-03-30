#!/usr/bin/python3
"""
run_phylogeny.py
Generates a phylogenetic tree from a FASTA file using Clustal Omega.
Produces a Newick file for interactive display and a PNG for static display.

Usage:
    python3 run_phylogeny.py <fasta_path> <run_id> <results_dir>

Prints:
    SUCCESS:N  where N = number of taxa in tree
    ERROR:message
"""

import os
os.environ['MPLCONFIGDIR'] = '/tmp'

import sys
import subprocess
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.patches as mpatches
import re

# ── arguments ────────────────────────────────────────────────────
if len(sys.argv) != 4:
    print("ERROR:wrong number of arguments")
    sys.exit(1)

fasta_path  = sys.argv[1]
run_id      = sys.argv[2]
results_dir = sys.argv[3]

newick_path = os.path.join(results_dir, f'run_{run_id}_tree.nwk')
plot_path   = os.path.join(results_dir, f'run_{run_id}_tree.png')

try:
    if not os.path.exists(fasta_path):
        print(f"ERROR:FASTA file not found at {fasta_path}")
        sys.exit(1)

    # ── 1. Run Clustal Omega to get Newick tree ───────────────────
    clustalo_cmd = [
        'clustalo',
        '-i',        fasta_path,
        '--outfmt',  'clustal',
        '-o',        '/dev/null',
        '--guidetree-out', newick_path,
        '--force'
    ]

    result = subprocess.run(clustalo_cmd, capture_output=True, text=True)

    if result.returncode != 0:
        print(f"ERROR:Clustal Omega failed: {result.stderr.strip()}")
        sys.exit(1)

    if not os.path.exists(newick_path):
        print("ERROR:Newick file was not created")
        sys.exit(1)

    # ── 2. Read Newick string ────────────────────────────────────
    with open(newick_path, 'r') as f:
        newick_str = f.read().strip()

    if not newick_str:
        print("ERROR:Newick file is empty")
        sys.exit(1)

    # ── 3. Count taxa ─────────────────────────────────────────────
    # Count accession-like labels in the Newick string
    n_taxa = newick_str.count(',') + 1

    # ── 4. Generate static tree image using matplotlib ────────────
    # Parse Newick manually for a simple cladogram layout
    # We'll draw a simple rectangular cladogram

    def parse_newick_labels(nwk):
        """Extract leaf labels from Newick string."""
        # Remove all whitespace and newlines first
        clean = re.sub(r'\s+', '', nwk)
        # Remove branch lengths
        clean = re.sub(r':[0-9.eE+\-]+', '', clean)
        # Extract labels — anything between ( , ) that isn't empty
        labels = re.findall(r'([A-Za-z0-9_\.]+)(?=[,\);])', clean)
        return labels

    labels = parse_newick_labels(newick_str)

    if len(labels) == 0:
        print("ERROR:Could not parse any labels from Newick string")
        sys.exit(1)

    # Draw a simple vertical cladogram
    fig, ax = plt.subplots(figsize=(10, max(4, len(labels) * 0.5)))

    n      = len(labels)
    y_pos  = {label: i for i, label in enumerate(labels)}
    x_tips = 1.0

    # Draw horizontal lines to tips
    for i, label in enumerate(labels):
        ax.plot([0.5, x_tips], [i, i],
                color='#7c6fcd', linewidth=1.2)
        ax.text(x_tips + 0.02, i, label,
                va='center', fontsize=8, color='#2d2a3e')
        ax.plot(x_tips, i, 'o',
                color='#7c6fcd', markersize=5)

    # Draw vertical connecting line
    ax.plot([0.5, 0.5], [0, n - 1],
            color='#5a4fb0', linewidth=1.5)

    # Draw root
    ax.plot([0.0, 0.5], [n / 2 - 0.5, n / 2 - 0.5],
            color='#5a4fb0', linewidth=1.5)

    ax.set_xlim(-0.1, 1.8)
    ax.set_ylim(-0.5, n - 0.5)
    ax.axis('off')
    ax.set_title(
        f'Phylogenetic tree — {n_taxa} sequences',
        fontsize=11, color='#2d2a3e', pad=10
    )

    plt.tight_layout()
    plt.savefig(plot_path, dpi=150, bbox_inches='tight',
                facecolor='white')
    plt.close()

    print(f"SUCCESS:{n_taxa}")

except Exception as e:
    print(f"ERROR:{str(e)}")
    sys.exit(1)
