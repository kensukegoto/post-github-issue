<?php
/* 
以下５点を各々の環境に合わせてハードコーディングして下さい。
・ユーザー名
・リポジトリ名
・認証トークン
・ブランチ名（マスターブランチを想定）
・画像ディレクトリ
（リポジトリ直下に置くことを想定。
例えば img など　img/wpなど更に深くしてもOKです。）
*/

// 設定
$global_github_conf = array(
    "gitUser" => "ユーザー名",
    "gitRepo" => "リポジトリ名",
    "token" => "トークン",
    "branch" => "ブランチ名", // ex) master
    "imgDir" => "画像ディレクトリ名", // ex )upimage 
);


$global_github_conf["repo"] = "https://api.github.com/repos/{$global_github_conf["gitUser"]}/{$global_github_conf["gitRepo"]}/";

if(isset($_POST["submit"])){
    

    // 設定
    global $global_github_conf;
    extract($global_github_conf);
    
    // テキストの処理
    function prepare_for_markdown($ans) {
        
        
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
    
    
    $ans_2 = "|トラブルのあったPC＋Excel環境について|\n";
    $ans_2 .= "|-|\n";
    $ans_2 .= "|**どのOSを使われていますか？**|\n";
    $ans_2 .= "|{$ans_2_1}|\n";
    $ans_2 .= "|**OSのバージョンを詳しく教えてください**|\n";
    $ans_2 .= "|{$ans_2_2}|\n";
    $ans_2 .= "**Excelのバージョンを詳しく教えてください**\n";
    $ans_2 .= "|{$ans_2_3}|\n";
    $ans_2 .= "|**(ExcelOnlineをご利用の場合)どのブラウザを使われていますか？**|\n";
    $ans_2 .= "|{$ans_2_4}|\n";
    $ans_2 .= "** (ExcelOnlineをご利用の場合)ブラウザのバージョンを詳しく教えてください**\n";
    $ans_2 .= "|{$ans_2_5}|\n\n";
    
    $ans_3 = "|編集されていたExcelファイルについて|\n";
    $ans_3 .= "|-|\n";
    $ans_3 .= "|**Excelで操作されていたファイルは、どこに保存されていましたか？**|\n";
    $ans_3 .= "|{$ans_3_1}|\n\n";
    
    $ans_4 = "|その他申し送り事項|\n";
    $ans_4 .= "|-|\n";
    $ans_4 .= "|**その他申し送り事項がございましたら、こちらに記述をお願いします。**|\n";
    $ans_4 .= "|{$ans_4_1}|\n\n";
    
    $ans = $ans_1.$ans_2.$ans_3.$ans_4;

    
    // 画像の処理
    $usr_imgs = array_filter($_FILES, function($file){
        
        return ($file['name']!=="" && $file['size']!==0);
    });
    

    
    function curl_get_contents( $url, $timeout = 60 ){
        $filename = $url;
        $handle = fopen($filename, "r");
        $contents = fread($handle, filesize($filename));
        return $contents;
        fclose($handle);
    }

    
    /* 送信下準備から送信までの関数群 */
    //　送信データの作成
    function prepare_for_post_imgs($img){
        
        // 設定項目
        global $global_github_conf;
        extract($global_github_conf);

        $ret = [];
    
        echo $img['tmp_name'];
        $img_org = curl_get_contents($img['tmp_name']);
        

        $img_base64 = base64_encode($img_org);
    
        $img_json = json_encode(
            array(
                "message"=>"トラブル報告フォームからの画像添付",
                "content"=>$img_base64
        ),JSON_PRETTY_PRINT);

        $img_name = make_img_name($img['tmp_name']);
        
        $ret['core'] = array(
            "repo" => $repo."contents/{$imgDir}/".$img_name,
            "issue" => $img_json,
            "ua" => $gitUser,
            "token"=> $token
        );
        
        $ret['img_path'] = "https://raw.githubusercontent.com/{$gitUser}/{$gitRepo}/{$branch}/{$imgDir}/".$img_name;
        
        return $ret;
        
    }
    
    //　ファイル名の作成
    function make_img_name($img_tmp){

        $mime = mime_content_type($img_tmp);
        $mime = explode('/',$mime);

        list($usec, $sec) = explode(' ', microtime());
        return ($sec + $usec * 1000000).".".$mime[1];
    }
    
    //　画像送信ラッパー関数
    function post_usr_imgs($img){
    
        post_img2github($img['core']);
        
    }
    // 画像送信本体
    function post_img2github(array $args) {

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
    
    function set_img_markdown($img){
        $img_path = $img['img_path'];
        return "![]({$img_path})";
    }
    
    $usr_imgs = array_map('prepare_for_post_imgs',$usr_imgs);
    array_walk($usr_imgs,"post_usr_imgs");
    
    // 画像のマークダウンを作成
    $img_markdown = array_map('set_img_markdown',$usr_imgs);
    $img_markdown = array_values($img_markdown);
    
    
    // 画像のマークダウンを追加
    for($i=0,$l=count($img_markdown);$i<$l;$i++){
        
        $str = $img_markdown[$i];
        $str = ($i!==($l-1))?$str."\n\n":$str;
        $ans .= $str;
        
    }
    
    // タイトルの作成
    $title_1_3 = str_replace(PHP_EOL, '', $_POST["ans_1_3"]);
    $title = $ans_1_1." : ".mb_substr($title_1_3,0,60);
    
    
    // 以下、テキストの整形と送信
    $issue = json_encode(
        array(
            "title"=>$title,
            "body"=>$ans
        ),
        JSON_PRETTY_PRINT);

    $issueData = array(
        "repo" => $repo."issues",
        "issue" => $issue,
        "ua" => $gitUser,
        "token"=> $token
    );
    
    
    function post_git_issue(array $args) {


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
    
    
    
    
    $res = post_git_issue($issueData);
    
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
            <p><input type="file" name="ans_img_3"></p>

            <h3>2. トラブルのあったPC＋Excel環境について</h3>
            <h4>2.1 どのOSを使われていますか？</h4>
            <p>
                <span class="wpcf7-form-control-wrap ans-2-1">
                    <select name="ans_2_1" class="wpcf7-form-control wpcf7-select select-ans" id="ans_2_1" aria-invalid="false">
                    <option value="Windows系">Windows系</option>
                    <option value="MacOS系">MacOS系</option>
                    <option value="Linux系">Linux系</option>
                    <option value="その他(下記、自由記述欄でご回答をお願いします)">その他(下記、自由記述欄でご回答をお願いします)</option>
                    <option value="不明">不明</option>
                    </select>
                </span>
            </p>
            <h4>2.2 OSのバージョンを詳しく教えてください。</h4>
            <p>
                <span class="wpcf7-form-control-wrap ans-2-2">
                    <input type="text" name="ans_2_2" value="" size="40" class="wpcf7-form-control wpcf7-text select-ans" id="ans_2_2" aria-invalid="false" placeholder="(例1)Windows8 64Bit (例2)macOS High Sierra 10.13.1(17B48)" />
                </span>
            </p>
            <h4>2.3 Excelのバージョンを詳しく教えてください。</h4>
            <p>
                <span class="wpcf7-form-control-wrap ans-2-3">
                    <select name="ans_2_3" class="wpcf7-form-control wpcf7-select select-ans" id="ans_2_3" aria-invalid="false">
                    <option value="Excel2013の32Bit版">Excel2013の32Bit版</option>
                    <option value="Excel2013の64Bit版">Excel2013の64Bit版</option>
                    <option value="Excel2013だが、32Bitか64Bitか把握していない">Excel2013だが、32Bitか64Bitか把握していない</option>
                    <option value="Excel2016の32Bit版">Excel2016の32Bit版</option>
                    <option value="Excel2016の64Bit版">Excel2016の64Bit版</option>
                    <option value="Excel2016だが、32Bitか64Bitか把握していない">Excel2016だが、32Bitか64Bitか把握していない</option>
                    <option value="ExcelOnline">ExcelOnline</option>
                    <option value="Nodeによる開発環境">Nodeによる開発環境</option>
                    <option value="その他(下記、自由記述欄でご回答をお願いします)">その他(下記、自由記述欄でご回答をお願いします)</option>
                    <option value="不明">不明</option>
                    </select>
                </span>
            </p>
            <h4>2.4 (ExcelOnlineをご利用の場合)どのブラウザを使われていますか？</h4>
            <p>
                <span class="wpcf7-form-control-wrap ans-2-4">
                    <select name="ans_2_4" class="wpcf7-form-control wpcf7-select select-ans" id="ans_2_4" aria-invalid="false">
                        <option value="---(ExcelOnline以外)">---(ExcelOnline以外)</option>
                        <option value="Chrome">Chrome</option>
                        <option value="Safari">Safari</option>
                        <option value="Edge">Edge</option>
                        <option value="Firefox">Firefox</option>
                        <option value="Internet Explorer">Internet Explorer</option>
                        <option value="その他(トラブルについての、自由記述欄でご回答をお願いします)">その他(トラブルについての、自由記述欄でご回答をお願いします)</option>
                        <option value="不明">不明</option>
                    </select>
                </span>
            </p>
            
            <h4>2.5 (ExcelOnlineをご利用の場合)ブラウザのバージョンを詳しく教えてください。</h4>
            <p>
                <span class="wpcf7-form-control-wrap ans-2-5">
                    <input type="text" name="ans_2_5" value="" size="40" class="wpcf7-form-control wpcf7-text select-ans" id="ans_2_5" aria-invalid="false" placeholder="(例1)Google Chrome バージョン: 63.0.3239.84（Official Build）（64 ビット）">
                </span>
            </p>
            <h3>3. 編集されていたExcelファイルについて</h3>
            <h4>3.1 Excelで操作されていたファイルは、どこに保存されていましたか？</h4>
            <p>
                <span class="wpcf7-form-control-wrap ans-3-1">
                    <select name="ans_3_1" class="wpcf7-form-control wpcf7-select select-ans" id="ans_3_1" aria-invalid="false">
                    <option value="PC(内蔵ディスク)">PC(内蔵ディスク)</option>
                    <option value="PC(USBストレージなどのドライブ)">PC(USBストレージなどのドライブ)</option>
                    <option value="PC(DVD-Rなど読み取り専用ディスク)">PC(DVD-Rなど読み取り専用ディスク)</option>
                    <option value="GoogleDrive">GoogleDrive</option>
                    <option value="OneDrive">OneDrive</option>
                    <option value="DropBox">DropBox</option>
                    <option value="その他クラウドストレージ(自由記述)">その他クラウドストレージ(自由記述)</option>
                    <option value="NASやNFSなどのLAN内のストレージ">NASやNFSなどのLAN内のストレージ</option>
                    <option value="新規作成(保存をしていない状態)">新規作成(保存をしていない状態)</option>
                    <option value="その他(下記、自由記述欄でご回答をお願いします)">その他(下記、自由記述欄でご回答をお願いします)</option>
                    <option value="不明">不明</option>
                    </select>
                </span>
            </p>
            <h3>4. その他申し送り事項</h3>
            <h4>4.1 その他申し送り事項がございましたら、こちらに記述をお願いします。</h4>
            <p>
                <span class="wpcf7-form-control-wrap ans-4-1">
                    <textarea name="ans_4_1" cols="40" rows="10" class="wpcf7-form-control wpcf7-textarea select-ans" id="ans_4_1" aria-invalid="false" placeholder="ございましたら、こちらにお書きください"></textarea>
                </span>
            </p>
<!--            送信-->
            <p>
            <input name="submit" type="submit" value="送信" class="wpcf7-form-control wpcf7-submit">
            </p>
        </form>
    </main>
</body>
</html>
<?php   
}