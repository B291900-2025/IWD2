#!/usr/bin/python3
"""
run_motifs.py
Runs EMBOSS patmatmotifs on one or more protein sequences
and parses the output into a structured format for PHP.

Usage:
    python3 run_motifs.py <fasta_path> <run_id> <results_dir>

Prints:
    SUCCESS:N  where N = total number of motifs found
    ERROR:message
"""

import os
os.environ['MPLCONFIGDIR'] = '/tmp'

import sys
import subprocess
import re

# ── arguments ────────────────────────────────────────────────────
if len(sys.argv) != 4:
    print("ERROR:wrong number of arguments")
    sys.exit(1)

fasta_path  = sys.argv[1]
run_id      = sys.argv[2]
results_dir = sys.argv[3]

output_path = os.path.join(results_dir, f'run_{run_id}_motifs.txt')

try:
    if not os.path.exists(fasta_path):
        print(f"ERROR:FASTA file not found at {fasta_path}")
        sys.exit(1)

    # ── Parse FASTA to get individual sequences ───────────────────
    sequences  = {}
    current_id  = None
    current_seq = ''

    with open(fasta_path, 'r') as f:
        for line in f:
            line = line.strip()
            if line.startswith('>'):
                if current_id is not None:
                    sequences[current_id] = current_seq
                current_id  = line[1:]
                current_seq = ''
            else:
                current_seq += line

    if current_id is not None:
        sequences[current_id] = current_seq

    if len(sequences) == 0:
        print("ERROR:No sequences found in FASTA file")
        sys.exit(1)

    total_motifs = 0
    results      = []

    # ── Run patmatmotifs on each sequence ─────────────────────────
    for header, seq in sequences.items():
        accession = header.split()[0]

        # Extract species from brackets
        species_match = re.search(r'\[([^\]]+)\]', header)
        species = species_match.group(1) if species_match else 'Unknown'

        # Write a temporary single-sequence FASTA file
        tmp_fasta = os.path.join(results_dir, f'tmp_{run_id}_{accession}.fasta')
        tmp_out   = os.path.join(results_dir, f'tmp_{run_id}_{accession}.patmatmotifs')

        with open(tmp_fasta, 'w') as tmp:
            tmp.write(f'>{header}\n')
            for i in range(0, len(seq), 60):
                tmp.write(seq[i:i+60] + '\n')

        # Run patmatmotifs
        cmd = [
            'patmatmotifs',
            '-sequence', tmp_fasta,
            '-outfile',  tmp_out,
            '-full',     'Y'
        ]

        subprocess.run(cmd, capture_output=True, text=True)

        # ── Parse patmatmotifs output ─────────────────────────────
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

                    # Read ahead to find Start, End, Motif
                    j = i + 1
                    while j < len(lines) and not lines[j].strip().startswith('#---'):
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
                            'motif':   motif,
                            'start':   start,
                            'end':     end,
                            'score':   length,
                            'pattern': ''
                        })
                        total_motifs += 1

                    i = j
                else:
                    i += 1

        results.append({
            'accession': accession,
            'species':   species,
            'motifs':    motifs_for_seq
        })

        # Clean up temp files
        if os.path.exists(tmp_fasta):
            os.remove(tmp_fasta)
        if os.path.exists(tmp_out):
            os.remove(tmp_out)

    # ── Write structured output file for PHP to read ──────────────
    # Format:
    # MOTIF|accession|species|motif|start|end|length|pattern
    # NOMOTIFS|accession|species

    with open(output_path, 'w') as out:
        for res in results:
            if len(res['motifs']) == 0:
                out.write(f"NOMOTIFS|{res['accession']}|{res['species']}\n")
            else:
                for m in res['motifs']:
                    out.write(
                        f"MOTIF|{res['accession']}|{res['species']}|"
                        f"{m['motif']}|{m['start']}|{m['end']}|"
                        f"{m['score']}|{m['pattern']}\n"
                    )

    print(f"SUCCESS:{total_motifs}")

except Exception as e:
    print(f"ERROR:{str(e)}")
    sys.exit(1)
