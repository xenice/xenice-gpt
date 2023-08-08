<?php

namespace xenice\gpt;

use xenice\gpt\models\Logs;
use function xenice\gpt\get as get;
use function xenice\member\is_member as is_member;

class Shortcode
{
    public function __construct()
    {
        add_shortcode('chatgpt', [$this, 'chatgpt']);
        //add_action('wp_head',[$this, 'head']);
        

        //add_action( 'wp_ajax_dplayerVerifyCode', [$this, 'verify']);
        //add_action( 'wp_ajax_nopriv_dplayerVerifyCode', [$this, 'verify']);

    }

    public function chatgpt($attr, $content){
        $mode = $attr['mode']??'';
        $this->head();
        add_action('wp_footer',[$this, 'footer'],99);
        ob_start();
        ?>
        <div id="app" class="<?php echo $mode?>">
            <div class="messages-container" id="messages-wrap">
                <div id="messages">
                </div>
            </div>
            <div class="input-area">
                <textarea id="input" placeholder="聊两句吧...（按Ctrl+Enter发送)"></textarea>
                <button id="send" onclick="sendMessage()">发送</button>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        return $content;
    }
    
    
    public function head()
    {
        $static_url = plugins_url('static', __FILE__);
        ?>
        <link rel="stylesheet" href="<?php echo $static_url ?>/css/monokai-sublime.css">
        <link rel="stylesheet" href="<?php echo $static_url ?>/css/chat.css?v=11">
        <style type="text/css">
            /* 聊天容器样式 */
    .messages-container {
        height: calc(100vh - 360px);
        border: 1px solid #eee;
        padding: 10px;
        border-radius: 10px 10px 0 0;
        overflow-y: auto; 
    }

    /* 消息框样式 */
    #messages {
        margin-bottom: 10px;
    }

    /* 输入区域样式 */
    .input-area {
        display: flex;
        align-items: center;
        padding: 10px;
        border: 1px solid #eee;
        border-top: none;
    }

    /* 输入框样式 */
    #input {
        flex: 1;
        padding: 5px 8px;
        border-radius: 5px;
        border: 1px solid #eee;
    }

    /* 发送按钮样式 */
    #send {
        border: none;
        color: #fff;
        cursor: pointer;
    }
    
    /* ai正在思考中效果 */
    .loader {
      display: inline-flex;
      justify-content: center;
      align-items: center;
      height: 44px;
    }
    
    .dot {
      width: 5px;
      height: 5px;
      border-radius: 50%;
      background-color: #333;
      margin: 0 5px;
      animation: pulse 1.5s ease-in-out infinite;
    }
    
    @keyframes pulse {
      0% {
        transform: scale(1);
        opacity: 1;
      }
      50% {
        transform: scale(1.5);
        opacity: 0.8;
      }
      100% {
        transform: scale(1);
        opacity: 1;
      }
    }
    
    /* 全屏设置 */
    .full-screen{
        position: fixed;
        top:0;
        bottom: 0;
        left:0;
        right:0;
        z-index: 999;
        background-color: #fff;
        height: 100vh;
    }
    
    .full-screen .messages-container {
        padding-top: 18px;
        min-height: calc(100vh - 70px);
        margin:0 auto;
        max-width: 1200px;
    }
    
    .full-screen .input-area{
        border-top:1px solid #eee;
        background-color: #fff;
        position: sticky;
        bottom:0;
        margin:0 auto;
        max-width: 1200px;
        
    }
    
    .admin-bar .full-screen .messages-container{
        padding-top: 50px;
    }
   
   @media (max-width: 767px) {
         .full-screen .messages-container {
            padding-top: 25px;
        }
        .admin-bar .full-screen .messages-container{
            padding-top: 62px;
        }
        
    }
    </style>
        
        
        
        
        
        <?php
    }
    public function footer()
    {
        $static_url = plugins_url('static', __FILE__);
        
        ?>
        <script>
        
        var admin_url = '<?php echo admin_url('admin-ajax.php')?>';
        var ai_first_sentence = '<?php echo get('ai_first_sentence')?:__('您好，我是AI智能机器人','xenice-gpt')?>';
        
        // 样式响应
        function styleResponse(){
            // 判断是否为苹果手机浏览器
            var isAppleBrowser = /iPhone|iPad|iPod/i.test(navigator.userAgent);
            
            // 判断是否为微信浏览器
            var isWeChatBrowser = /MicroMessenger/i.test(navigator.userAgent);
            
            // 判断是否为安卓浏览器
            var isAndroidBrowser = /Android/i.test(navigator.userAgent);
            
            if(isAndroidBrowser && !isWeChatBrowser){
                document.querySelector('.full-screen .messages-container').setAttribute('style',' min-height: calc(100vh - 120px);');
            }
            
            if(isAppleBrowser && !isWeChatBrowser){
                document.querySelector('.full-screen .messages-container').setAttribute('style',' min-height: calc(100vh - 170px);');
                //document.querySelector('.full-screen .input-area').setAttribute('style','bottom:20px');
            }
            
            if(document.querySelector('.full-screen')){
                document.querySelector('body').setAttribute('style','overflow: hidden');
            }

        }
        
        
        </script>
        <script src="<?php echo $static_url ?>/js/marked.min.js"></script>
        <script src="<?php echo $static_url ?>/js/highlight.min.js"></script>
        <script src="<?php echo $static_url ?>/js/chat.js?v=47"></script>
        <?php
        
    }

}