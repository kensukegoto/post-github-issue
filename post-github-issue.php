<?php
if(isset($_POST["submit"])){
    
    $gitUser = isset($_POST["gituser"])?$_POST["gituser"]:"";
    $gitRepo = isset($_POST["gitrepo"])?$_POST["gitrepo"]:"";
    $token = isset($_POST["token"])?$_POST["token"]:"";   
    $titIssue = isset($_POST["titissue"])?$_POST["titissue"]:""; 
    $bodyIssue = isset($_POST["bodyissue"])?$_POST["bodyissue"]:""; 
    
    $repo = "https://api.github.com/repos/{$gitUser}/{$gitRepo}/issues";


    $issue = json_encode(
        array(
            "title"=>$titIssue,
            "body"=>$bodyIssue
        ),
        JSON_PRETTY_PRINT);

    $args = array(
        "repo" => $repo,
        "issue" => $issue,
        "ua" => $gitUser,
        "token"=> $token
    );
    
    $res = postGitIssue($args);
    
    $res = json_decode($res,true);
    
    if(array_key_exists("title",$res)){
        echo "送信しました。";
    }else{
        echo "失敗しました。何かしら間違ってます。";
    }
    
} else{

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <style>
        *{
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }
        a{
            word-break: break-all;
        }
        input,div{
            margin-bottom: 1em;
        }
        main{
            width: 100%;
            max-width: 700px;
            margin: 1em auto;
            padding: 0 10px;
        }
        input[name="token"],input[name="titissue"]{
            width: 100%;
            
        }
        textarea{
            width: 100%;
            height: 500px;
            padding: 10px;
        }
    </style>
</head>
<body>
    <main>
        
        <h1>不具合報告フォーム</h1>
        <div>
            <p>以下の書式にはまるように、ユーザー名とリポジトリ名を記入して下さい。<br>
            php内で以下の書式のURLを作成して、POSTリクエストを送信します。<br>
            https://api.github.com/repos/<b>ユーザー名</b>/<b>リポジトリ名</b>/issues
        </div>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <dl>
                <dt>githubのユーザー名</dt>
                <dd><input type="text" name="gituser"></dd>
                <dt>githubのリポジトリ名</dt>
                <dd><input type="text" name="gitrepo"></dd>
                <dt>認証トークン</dt>
                <dd>
                    <div>
                        <p><input type="text" name="token"></p>
                        <p>認証トークンの発行方法は下記リンク先を参照<br>
                        権限は「repo」の「public_repo」のチェックのみでいけました。
                        
                        <p><a href="https://qiita.com/kz800/items/497ec70bff3e555dacd0">https://qiita.com/kz800/items/497ec70bff3e555dacd0</a></p>
                    </div>
                </dd>
                <dt>不具合の概要（タイトル）</dt>
                <dd><input type="text" name="titissue"></dd>
                <dt>不具合の詳細</dt>
                <dd>
                   <div>
                    <textarea name="bodyissue" id="" cols="30" rows="10"></textarea>
                    </div>
                </dd>
            </dl>
            <p><input type="submit" name="submit" value="不具合を報告"></p>
        </form>
    </main>
</body>
</html>
<?php   
}


function postGitIssue(array $args) {
    
    extract($args);
    
    $header = [
        'Content-Type: application/json',
        'Authorization: token '.$token
    ];
    
    // RETURNTRANSFER exec時に結果出力を回避
	$options = array(
        CURLOPT_URL => $repo,
        CURLOPT_HTTPHEADER => $header,
        CURLOPT_POSTFIELDS => $issue,
        CURLOPT_USERAGENT => $ua,
        CURLOPT_RETURNTRANSFER => true
	);
        
	$ch = curl_init();
    
	curl_setopt_array($ch, $options);
	$res = curl_exec($ch);
	curl_close($ch);
	return $res;
}