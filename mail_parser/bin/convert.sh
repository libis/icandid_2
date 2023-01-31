#!/bin/bash
INDIR=/source_records/mails
OUTDIR=/source_records/mails/pst_to_mbox

if [[ ! -f "$INDIR/input.pst" ]]; then
    echo "Error $INDIR/input.pst does not exists."
    echo ""
    exit
fi

if [[ ! -e $OUTDIR ]]; then
    mkdir $OUTDIR
fi

readpst -u -o $OUTDIR $INDIR/input.pst

echo "Converted input available in $OUTDIR"
