{
    echo "===== START: $(date '+%Y-%m-%d %H:%M:%S') ====="
    php src/main.php
    echo "===== END:   $(date '+%Y-%m-%d %H:%M:%S') ====="
    # 空行を入れて見やすくする
    echo  
} >> run-update-dns-a-record-job.log 2>&1