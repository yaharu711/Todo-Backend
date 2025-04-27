#!/usr/bin/env bash
# どこから実行されてもカレントディレクトリに移動するようにする（実行されるスクリプトでうまくパスが読み込めなくなる？みたい）
cd "$(dirname "$BASH_SOURCE")"
{
    echo "===== START: $(date '+%Y-%m-%d %H:%M:%S') ====="
    php src/main.php
    echo "===== END:   $(date '+%Y-%m-%d %H:%M:%S') ====="
    # 空行を入れて見やすくする
    echo  
} >> run-update-dns-a-record-job.log 2>&1