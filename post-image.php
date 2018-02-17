<?php
if(isset($_POST["submit"])){
    
    
    $gitUser = "kensukegoto";
    $gitRepo = "post-test";
    $token = "9fc24f83efe859688782297941ba41d3d6450a97";   
    $titIssue = isset($_POST["titissue"])?$_POST["titissue"]:""; 
    $bodyIssue = isset($_POST["bodyissue"])?$_POST["bodyissue"]:""; 
    
    $repo = "https://api.github.com/repos/{$gitUser}/{$gitRepo}/";

    // var_dump($_FILES['gazou']);
    echo count($_FILES);
    exit();
    
    $gazou = $_FILES['gazou'];
    $data = file_get_contents($gazou['tmp_name']);
    $gazou_name = $gazou['name'];
    
    $gazou2 = base64_encode($data);

    $issue = json_encode(
        array(
            "message"=>"my commit message",
            "content"=>$gazou2
        ),
        JSON_PRETTY_PRINT);
    
    $args = array(
        "repo" => $repo."contents/".$gazou_name,
        "issue" => $issue,
        "ua" => $gitUser,
        "token"=> $token
    );
    
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
            CURLOPT_CUSTOMREQUEST => 'PUT',
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
    
    $res = postGitIssue($args);
    

    $res = json_decode($res,true);
    echo $args["repo"];
    // echo base64_decode($res["content"]);
    
    exit;
    
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
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
            <dl>
                <dt>ファイルをアップロード</dt>
                <dd>
                    <div>
                        <input type="file" name="gazou">
                        <input type="file" name="gazou2">
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