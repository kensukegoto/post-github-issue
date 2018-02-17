<?php
if(isset($_POST["submit"])){
    
    $gitUser = "kensukegoto";
    $gitRepo = "post-test";
    $token = "9fc24f83efe859688782297941ba41d3d6450a97";   

    
    $repo = "https://api.github.com/repos/{$gitUser}/{$gitRepo}/";
    
    
    // テキストの処理
    function prepare_for_markdown($ans){
        
        $gitMarkDown = preg_quote("`|#>:-*_+!.\?",'/');
        // メタ文字のエスケープ
        $ans = preg_replace("/([{$gitMarkDown}])/",'\\\$1',$ans);
        // $ans = trim(preg_replace('/\t/g', '', $ans));
        $ans = str_replace(PHP_EOL, '<br>', $ans);
        
        return $ans;
    }
    
    $answer = array_map("prepare_for_markdown", $_POST);
    
    extract($answer,EXTR_SKIP);

    $ans_1 = "|トラブルの内容について|\n";
    $ans_1 .= "|-|\n";
    $ans_1 .= "|**どの機能で、トラブルが起こりましたか？**|\n";
    $ans_1 .= "|{$ans_1_1}|\n";
    $ans_1 .= "|**トラブルは、どのくらいの確率で発生しますか？**|\n";
    $ans_1 .= "|{$ans_1_2}|\n";
    $ans_1 .= "**トラブルの内容を詳しく教えてください**\n";
    $ans_1 .= "|{$ans_1_3}|\n\n";
    
    
//    $ans_2 = "|トラブルのあったPC\＋Excel環境について|\n";
//    $ans_2 .= "|-|\n";
//    $ans_2 .= "|**どのOSを使われていますか？**|\n";
//    $ans_2 .= "|{$ans_2_1}|\n";
//    $ans_2 .= "|**OSのバージョンを詳しく教えてください**|\n";
//    $ans_2 .= "|{$ans_2_2}|\n";
//    $ans_2 .= "**Excelのバージョンを詳しく教えてください**\n";
//    $ans_2 .= "|{$ans_2_3}|\n";
//    $ans_2 .= "|**(ExcelOnlineをご利用の場合)どのブラウザを使われていますか？**|\n";
//    $ans_2 .= "|{$ans_2_4}|\n";
//    $ans_2 .= "** (ExcelOnlineをご利用の場合)ブラウザのバージョンを詳しく教えてください**\n";
//    $ans_2 .= "|{$ans_2_3}|\n";
    

    $issue = json_encode(
        array(
            "title"=>"hello",
            "body"=>$ans_1
        ),
        JSON_PRETTY_PRINT);

    $issueData = array(
        "repo" => $repo."issues",
        "issue" => $issue,
        "ua" => $gitUser,
        "token"=> $token
    );
    
    
    
    // 画像の処理
//    $ans_imgs = array_filter($_FILES, function($file){
//        
//        return ($file['name']!=="" && $file['size']!==0);
//    });
//    
//    function postGitIssue(array $args) {
//
//        extract($args);
//
//        $header = [
//            'Content-Type: application/json',
//            'Authorization: token '.$token
//        ];
//
//        // RETURNTRANSFER exec時に結果出力を回避
//        $options = array(
//            CURLOPT_URL => $repo,
//            CURLOPT_HTTPHEADER => $header,
//            CURLOPT_CUSTOMREQUEST => 'PUT',
//            CURLOPT_POSTFIELDS => $issue,
//            CURLOPT_USERAGENT => $ua,
//            CURLOPT_RETURNTRANSFER => true
//        );
//
//        $ch = curl_init();
//
//        curl_setopt_array($ch, $options);
//        $res = curl_exec($ch);
//        curl_close($ch);
//        return $res;
//    }
//    
//    
//    $ans_imgs = array_map(function($img){
//        
//        global $repo;
//        global $gitUser;
//        global $token;
//        
//        $img_ret = [];
//        $img_ret['name'] = $img['name'];
//        
//        $img_tmp = file_get_contents($img['tmp_name']);
//        $img_base64 = base64_encode($img_tmp);
//    
//        $img_ret['data'] = json_encode(
//            array(
//                "message"=>"トラブル報告フォームからの画像添付",
//                "content"=>$img_base64
//        ),JSON_PRETTY_PRINT);
//        
//        
//        $args = array(
//            "repo" => $repo."contents/".$img['name'],
//            "issue" => $img_ret['data'],
//            "ua" => $gitUser,
//            "token"=> $token
//        );
//        
//
//    $res = postGitIssue($args);
//        
//    }, $ans_imgs);
    

    
    
//    exit;
//    
//
//    
//    $args = array(
//        "repo" => $repo."contents/".$gazou_name,
//        "issue" => $issue,
//        "ua" => $gitUser,
//        "token"=> $token
//    );
//    
//    exit;
    
    
    
    
    function post_git_issue2(array $args) {


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
    
    
    
    
    $res = post_git_issue2($issueData);
    
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
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
            <h3>トラブルの内容について</h3>
            <h4>1.1 どの機能で、トラブルが起こりましたか？</h4>
            <p>
                <span class="wpcf7-form-control-wrap ans-1-1">
                    <select name="ans_1_1" class="wpcf7-form-control wpcf7-select select-ans" id="ans_1_1" aria-invalid="false">
                        <option value="グラフ機能">グラフ機能</option>
                        <option value="グラフ以外の機能(グラフの種類に関係なく発生する)">グラフ以外の機能(グラフの種類に関係なく発生する)</option>
                        <option value="その他(下記、自由記述欄でご回答をお願いします)">その他(下記、自由記述欄でご回答をお願いします)</option>
                    </select>
                </span>
            </p>
            <h4>1.2 トラブルは、どのくらいの確率で発生しますか？</h4>
            <p>
                <span class="wpcf7-form-control-wrap ans-1-2">
                    <select name="ans_1_2" class="wpcf7-form-control wpcf7-select select-ans" id="ans_1_2" aria-invalid="false">
                    <option value="5回に4回くらい発生する(80%)">5回に4回くらい発生する(80%)</option>
                    <option value="2回に1回くらい発生する(50%)">2回に1回くらい発生する(50%)</option>
                    <option value="10回に1回くらい発生する(10%)">10回に1回くらい発生する(10%)</option>
                    <option value="その他(下記、自由記述欄でご回答をお願いします)">その他(下記、自由記述欄でご回答をお願いします)</option>
                    </select>
                </span>
            </p>
            <h4>1.3 トラブルの内容を詳しく教えてください</h4>
            <p>
                <span class="wpcf7-form-control-wrap ans-1-3">
                <textarea name="ans_1_3" cols="40" rows="10" class="wpcf7-form-control wpcf7-textarea select-ans" id="ans-1-3" aria-invalid="false" placeholder="Excelを立ち上げたところから、どの画面でどの操作をしたなど、操作手順をご説明頂けますと大変助かります"></textarea>
                </span>
            </p>
            <h4>1.4 トラブル発生時のスクリーンショットや、ログがありましたら、添付をお願いします</h4>
            <p><input type="file" name="ans_img_1"></p>
            <p><input type="file" name="ans_img_2"></p>
            <p>
            <input name="submit" type="submit" value="送信" class="wpcf7-form-control wpcf7-submit">
            </p>
        </form>
    </main>
</body>
</html>
<?php   
}