## post-github-issue.php  
以下のキャプチャのようなフォームのみのページ。  
Github APIをPHPでちゃんと叩けるよね、という確認用。  
フォーム上で以下を記入して送信すると指定したリポジトリのISSUEに内容を送信する。  
・ユーザー名  
・リポジトリ名  
・認証トークン  
・不具合件名  
・不具合詳細  
  
![キャプチャ](/images/form.jpg)  


## form7-post-github-issue.php 
WordPressのform7プラグインのアクションフックを記述したもの。  
function.phpにこのファイルの記述を書けば、動くはず（local環境だとメールサーバーの設定をしてるかどうかなどで環境依存するかもしれません）

**---注意---**  
  
form7でフォーム作成時にname属性を以下のように指定  
・不具合件名の name属性を「issuetit」  
・不具合詳細の name属性を「bodyissue」  
  
![キャプチャ](/images/wp-form.png)  
  
以下の３点をハードコーディング  
・ユーザー名  
・リポジトリ名  
・認証トークン  
これらの記述場所は２０行目あたりです。（ファイルを見れば分かると思います）

## skel-post-github-issue.php
処理のコアのみを書いたPHPファイル  
全てハードコーディングすればこれ単体で動くはずです
