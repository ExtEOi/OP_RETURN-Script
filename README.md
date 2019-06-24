# OP_RETURN-Script
OP_RETURNを含めたトランザクションを作ったり、トランザクションに含まれているOP_RETURNのメッセージを確認できるスクリプトです。
## 仕様・つかいかた
### getopreturn.php
GETで指定したtxidのrawTransactionからOP_RETURN部分を取得するスクリプト。

※ $rpcpasswordを指定してください。
## sendtx.php
write.phpからPOSTされた情報をもとにトランザクションを作るスクリプト。
write.phpでmonacoindにmonaを送信
→ sendtx.phpでmonacoindから送信元アドレスにOP_RETURNを含めたトランザクションを送信

※ $rpcpasswordを指定してください。

※ $changeAddressに余りmonaの送信先のアドレスを指定してください。
## write.php
受け取り用アドレスを生成し、生成するトランザクションの情報をsendtx.phpにPOSTするスクリプト。

※ $rpcpasswordを指定してください。

※ $addressAccountにmonacoindの受け取り用のアカウント名を入力してください。

改造などは自由です。
