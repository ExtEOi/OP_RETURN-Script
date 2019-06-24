<?php
ini_set( 'display_errors', 0 ); 

require_once(__DIR__ . '/jsonRPCClient.php');
$host = 'localhost';		/* monacoind 又は monacoin-qt を実行中のホストのアドレス */
$rpcuser = 'monacoinrpc';	/* monacoin.conf で指定した rpcユーザー名 */
$rpcpassword = '';		/* monacoin.conf で指定した rpcパスワード */
$rpcport = '19402';			/* monacoin.conf で指定した rpcポート */
$coindaddr = "http://$rpcuser:$rpcpassword@$host:$rpcport/";
$coind = new jsonRPCClient($coindaddr);

$addressAccount = 'write';

function error($errorinput){return $errorinput;}

try {
	$address = $coind->getnewaddress($addressAccount);
} catch (Exception $e) {
	$error = error('アドレス取得エラー');
}

if (isset($error)) {echo $error;exit();}
echo 'monacoin:'.$address.'<br>このアドレスに0.02mona以上(0.01mona単位を推奨)送金し、その取引IDをtxに入力してください。<br><br>';
?>
説明<br>
※試験中の機能です。<br>
fee = 手数料は0.01monaに設定しています。<br>
tx = txid、必須。<br>
pay = OP_RETURNで支払うmona(消えます)。0.01mona以上か0mona、入力が無い場合0monaに設定されます。<br>
outputaddress = 上記アドレスにfee + pay(デフォルトは0.01mona)以上のmonaを送金した場合に、余りmonaを送金するアドレス。入力が無い場合寄付アドレスに送金されます。<br>
outputamount = outputaddressに送信するmona数。指定が無い場合、余りmonaの80%が指定されます。残りの20%は寄付アドレスに送金されます。<br>
text = OP_RETURNで書き込む文字列。hexに変換されます(文字数が多いとエラーが出る可能性があります)。必須。<br><br>
エラーが出た場合は、ブラウザバックするか、再度recaptcha認証した後、同じ取引idを入力してください(過去に表示したアドレスの取引idでも使えます)。<br>
<br>
<form action="sendtx.php" method="post">
  tx
  <input type="text" id="tx" name="tx" maxlength="64"><br>
  pay
  <input type="text" id="tx" name="pay"><br>
  outputaddress
  <input type="text" id="address" name="address" maxlength="34"><br>
  outputamount
  <input type="text" id="tx" name="output"><br>
  text
  <input type="text" id="text" name="text"><br>
  <input type="submit" id="submit">
</form>