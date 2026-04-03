# ProtExplorer

**Where sequence data meets biological meaning**

ProtExplorer is a web-based bioinformatics tool for comparative protein
sequence analysis. Given a protein family and taxonomic group, it
automatically retrieves sequences from NCBI, runs multiple sequence
alignment, conservation profiling, PROSITE motif scanning, phylogenetic
tree construction, and AlphaFold structure lookup.

Built as the assessed practical project for BILG11016 Introduction to
Website and Database Design, University of Edinburgh (2025-26).

---

## Website

https://bioinfmsc8.bio.ed.ac.uk/~s2793337/Website/

---

## Features

- **Sequence retrieval** — fetch protein sequences from NCBI Protein
  database by protein family and taxonomic group via Bio.Entrez
- **Conservation analysis** — multiple sequence alignment with Clustal
  Omega, per-position conservation scoring, matplotlib plot
- **Motif scanning** — PROSITE motif detection via EMBOSS patmatmotifs,
  combined and per-sequence views, user-controlled scope
- **Phylogenetic tree** — Clustal Omega guide tree, static PNG and
  interactive D3.js visualisation with zoom, pan, collapse/expand
- **Structure links** — AlphaFold predicted structure lookup via UniProt
  cross-references, PAE images and pLDDT confidence scores
- **Example dataset** — pre-processed glucose-6-phosphatase sequences
  from Aves (15 species) with full biological context
- **Run history** — session-based history with status badges and
  direct links to completed analyses
- **Feedback and contact** — anonymous feedback form and contact form,
  both stored in MySQL via PDO
- **Mobile responsive** — tested on iOS and Android

---

## Technology stack

| Layer | Technology |
|-------|-----------|
| Web server | Apache 2 (bioinfmsc8, University of Edinburgh) |
| Server-side | PHP 8.2 |
| Database | MySQL 8.0 via PDO |
| Analysis | Python 3.12 |
| Alignment | Clustal Omega |
| Motif scanning | EMBOSS patmatmotifs / PROSITE |
| Sequence fetch | BioPython Bio.Entrez |
| Plotting | matplotlib + NumPy |
| Tree rendering | D3.js v7 |
| Version control | Git + GitHub |

---

## Database schema

s2793337_website
├── Runs            — one row per user search
├── Sequences       — fetched sequences linked to Runs
├── Results         — output file paths linked to Runs
├── ExampleDataset  — pre-loaded G6Pase/Aves sequences
└── Feedback        — user feedback and contact messages

---

## File structure

Website/
├── index.php               Landing page
├── search.php              Search form
├── process_search.php      Form processor
├── results.php             Results page
├── run_conservation.php    Conservation analysis
├── run_motifs.php          Motif scan options
├── process_motifs.php      Motif scan processor
├── motif_results.php       Motif scan results
├── run_phylogeny.php       Phylogenetic tree
├── run_structures.php      Structure links
├── example.php             Pre-loaded G6Pase/Aves example
├── history.php             Session run history
├── help.php                User guide (biology-facing)
├── about.php               Implementation overview (dev-facing)
├── credits.php             Full attribution and AI usage
├── feedback.php            User feedback form
├── contact.php             Contact form
├── login.php               Database credentials
├── menuf.php               Shared navigation bar
├── redir.php               Session guard
├── style.css               Global stylesheet
├── loading.js              Loading overlay with progress bar
├── animate.js              Scroll-triggered animations
├── scripts/
│   ├── fetch_sequences.py      NCBI sequence retrieval
│   ├── run_conservation.py     Clustalo + conservation plot
│   ├── run_motifs.py           patmatmotifs wrapper
│   ├── run_phylogeny.py        Guide tree + static plot
│   ├── run_structures.py       AlphaFold structure lookup
│   ├── setup_example.py        One-off example dataset setup
│   └── generate_example_sql.py SQL generator for example data
└── results/                Generated output files (gitignored)

---

## Setup

This project runs on the University of Edinburgh bioinfmsc8 server.
All dependencies (Clustal Omega, EMBOSS, Python, MySQL, Apache) are
pre-installed on that server.

To set up from scratch:
```bash
# 1. Clone the repository
git clone https://github.com/B291900-2025/IWD2.git
cd IWD2

# 2. Create the database tables
mysql -u sYOURNUMBER -p < maketables.sql

# 3. Edit login.php with your MySQL credentials

# 4. Set results folder permissions
chmod 777 results/

# 5. Add your NCBI credentials to the Python scripts
#    Edit Entrez.email and Entrez.api_key in:
#    scripts/fetch_sequences.py
#    scripts/run_structures.py
#    scripts/setup_example.py

# 6. Run the example dataset setup
python3 scripts/setup_example.py
python3 scripts/generate_example_sql.py
mysql -u sYOURNUMBER -p sYOURNUMBER_website < scripts/populate_example.sql
```

---

## Key design decisions

- **PHP/PDO only for MySQL** — Python scripts never touch the database.
  All database interactions use PDO prepared statements.
- **Python for analysis only** — Python handles NCBI fetching, alignment,
  motif scanning, plotting and API calls. Results are written to files
  which PHP reads and stores in MySQL.
- **Shell_exec() bridge** — PHP calls Python scripts via shell_exec(),
  reads stdout for SUCCESS/ERROR status, then processes output files.
- **Flat files for outputs** — PNG images, FASTA files and Newick trees
  are stored on the filesystem; MySQL stores metadata and file paths.
- **Session-based history** — run history uses PHP sessions with a
  session_token stored in the database for future persistent history.

---

## Credits and AI usage

Full attribution for all tools, libraries, course materials and AI
assistance is available on the
[credits page](https://bioinfmsc8.bio.ed.ac.uk/~s2793337/Website/credits.php)
of the live website.

**AI Usage Summary:** Claude (Anthropic) was used to generate initial
PHP and Python scaffolding which was then reviewed, modified and
debugged. All design decisions, biological interpretation, and final
implementation were produced by the student.

---
