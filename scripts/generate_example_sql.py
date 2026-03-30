#!/usr/bin/python3
"""
generate_example_sql.py
Generates populate_example.sql from the example FASTA file.
Run once after setup_example.py.
"""

import os
import re

script_dir  = os.path.dirname(os.path.abspath(__file__))
base_dir    = os.path.dirname(script_dir)
fasta_path  = os.path.join(base_dir, 'results', 'example_sequences.fasta')
sql_path    = os.path.join(script_dir, 'populate_example.sql')

sequences = []
current_id  = None
current_seq = ''
current_desc = ''

with open(fasta_path, 'r') as f:
    for line in f:
        line = line.strip()
        if line.startswith('>'):
            if current_id is not None:
                sequences.append({
                    'accession': current_id,
                    'species':   current_desc,
                    'sequence':  current_seq,
                    'length':    len(current_seq)
                })
            current_id   = line[1:].split()[0]
            species_match = re.search(r'\[([^\]]+)\]', line)
            current_desc = species_match.group(1) if species_match else 'Unknown'
            current_seq  = ''
        else:
            current_seq += line

if current_id is not None:
    sequences.append({
        'accession': current_id,
        'species':   current_desc,
        'sequence':  current_seq,
        'length':    len(current_seq)
    })

with open(sql_path, 'w') as out:
    out.write("USE s2793337_website;\n\n")
    out.write("-- Clear existing example data\n")
    out.write("TRUNCATE TABLE ExampleDataset;\n\n")
    out.write("-- Insert example sequences\n")

    for s in sequences:
        # Escape single quotes in sequence
        seq_escaped = s['sequence'].replace("'", "''")
        out.write(
            f"INSERT INTO ExampleDataset "
            f"(accession, species, sequence, seq_length, protein, taxon) "
            f"VALUES ("
            f"'{s['accession']}', "
            f"'{s['species']}', "
            f"'{seq_escaped}', "
            f"{s['length']}, "
            f"'glucose-6-phosphatase', "
            f"'Aves'"
            f");\n"
        )

print(f"Generated SQL for {len(sequences)} sequences -> {sql_path}")
