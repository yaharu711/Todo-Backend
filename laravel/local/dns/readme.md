# ローカルサーバのカスタムドメインの A レコード定期実行について

- ホストマシンの IP アドレスを設定する必要がある
- ホストマシンは通常 DHCP により動的に IP アドレスが振り分けられ、定期的に変わる
- よって、A レコードに設定されている IP アドレスと変化があれば自動的に A レコードを更新する必要がある

以下は一旦後回し、（上手く回らなかった）

- そこで、com.todo.UpdateDnsARecordJob.xml を使う
  - ローカル環境は Mac で動かすことを前提としている
- exmaple からスクリプト実行パスの部分を自分の環境に置き換えて、以下のパスにファイルを作る

```
~/Library/LaunchAgents/com.todo.UpdateDnsARecordJob.plist
```

- 定期実行されるように以下のコマンドを実行する

```
launchctl load ~/Library/LaunchAgents/com.todo.UpdateDnsARecordJob.plist
```

- 一応以下のコマンドで plist の構文が正しいのか確認できる

```
plutil -lint ~/Library/LaunchAgents/com.todo.UpdateDnsARecordJob.plist
```

- また、正しくロードできているかを以下のコマンドで確認できる

```
launchctl list | grep com.todo.UpdateDnsARecordJob
```
