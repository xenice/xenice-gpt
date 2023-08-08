const messagesWrap = document.getElementById('messages-wrap');
const messagesContainer = document.getElementById('messages');

const input = document.getElementById('input');
const sendButton = document.getElementById('send');
var qaIdx = 0,answers={},answerContent='',answerWords=[],questionContent='';
var codeStart=false,lastWord='',lastLastWord='';
var typingTimer=null,typing=false,typingIdx=0,contentIdx=0,contentEnd=false;
var waitMsg = '<div class="loader"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>';
//markdown解析，代码高亮设置
marked.setOptions({
    highlight: function (code, language) {
        const validLanguage = hljs.getLanguage(language) ? language : 'javascript';
        return hljs.highlight(code, { language: validLanguage }).value;
    },
});


//在输入时和获取焦点后自动调整输入框高度
input.addEventListener('input', adjustInputHeight);
input.addEventListener('focus', adjustInputHeight);


window.addEventListener('resize', function() {

    if(isKeyboardOpen){
        messagesWrap.scrollTop = messagesWrap.scrollHeight;
    }


});

// 监听键盘按下事件
document.addEventListener("keydown", function(event) {
  // 判断是否按下了 Ctrl 键和 Enter 键
  if (event.ctrlKey && event.key === "Enter") {
    // 触发按钮点击事件
    sendButton.click();
  }
});

window.onload=function(){
    initdMessages();
    
    styleResponse();
    
};

// 手机键盘是否弹出
function isKeyboardOpen() {
  var visualViewportHeight = window.visualViewport.height;
  var windowHeight = window.innerHeight;

  // 检查当前视口高度和窗口高度是否有差异
  if (visualViewportHeight < windowHeight) {
    return true; // 键盘弹出
  } else {
    return false; // 键盘未弹出
  }
}


// 自动调整输入框高度
function adjustInputHeight() {
    input.style.height = 'auto'; // 将高度重置为 auto
    input.style.height = (input.scrollHeight+2) + 'px';
}

// html标签转为实体
function escapeHtmlTags(str) {
  // 创建一个 HTML 实体编码映射表
  const htmlEntities = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;'
  };

  // 使用正则表达式替换 HTML 标签为实体
  return str.replace(/[&<>"']/g, function (tag) {
    return htmlEntities[tag] || tag;
  });
}

// 初始化历史记录
function initdMessages() {
    //var arr = [["你好","你也好"],["你好","哈哈哈"]];
    //var str = localStorage.getItem("xenice_messages");
    var arr = JSON.parse(localStorage.getItem("xenice_messages"));
    if(!arr){
        // 没有聊天记录时
        var salutation = ai_first_sentence;
        const answer = document.createElement('div');
        answer.setAttribute('class', 'message answer');
        answer.setAttribute('id', 'answer-'+qaIdx);
        answer.innerHTML = marked.parse(salutation);
        messagesContainer.appendChild(answer);
        qaIdx += 1;
        return;
    }
    arr = arr.slice(-100); // 只保留前100条记录
    for(var i = 0; i < arr.length; i++) {
        const question = document.createElement('div');
        question.setAttribute('class', 'message question');
        question.setAttribute('id', 'question-'+qaIdx);
        arr[i][0] = arr[i][0].replace(/\r\n/g, "<br>").replace(/\n/g, "<br>").replace(/\r/g, "<br>");
        question.innerHTML = marked.parse(arr[i][0]);
        messagesContainer.appendChild(question);
    
        const answer = document.createElement('div');
        answer.setAttribute('class', 'message answer');
        answer.setAttribute('id', 'answer-'+qaIdx);
        answer.innerHTML = marked.parse(arr[i][1]);
        messagesContainer.appendChild(answer);
        qaIdx += 1;
    }
    // 聊天记录滚动条设置到底部
    messagesWrap.scrollTop = messagesWrap.scrollHeight;
}

function sendMessage() {
    const inputValue = input.value;
    if (!inputValue) {
        return;
    }

    const question = document.createElement('div');
    question.setAttribute('class', 'message question');
    question.setAttribute('id', 'question-'+qaIdx);
    var str = escapeHtmlTags(inputValue).replace(/\r\n/g, "<br>").replace(/\n/g, "<br>").replace(/\r/g, "<br>");
    question.innerHTML = marked.parse(str);
    messagesContainer.appendChild(question);

    const answer = document.createElement('div');
    answer.setAttribute('class', 'message answer');
    answer.setAttribute('id', 'answer-'+qaIdx);
    
    
    answer.innerHTML = marked.parse(waitMsg);
    messagesContainer.appendChild(answer);
    // 聊天记录滚动条设置到底部
    messagesWrap.scrollTop = messagesWrap.scrollHeight;
    
    
    answers[qaIdx] = document.getElementById('answer-'+qaIdx);
    
    questionContent = input.value;
    input.value = '';
    input.disabled = true;
    sendButton.disabled = true;
    adjustInputHeight();

    typingTimer = setInterval(typingWords, 50);
    getAnswer(inputValue);
}

function getAnswer(inputValue){
    inputValue = encodeURIComponent(inputValue.replace(/\+/g, '{[$add$]}'));
    const url = admin_url + "?action=chat&q="+inputValue;
    const eventSource = new EventSource(url);
    //eventSource.timeout = 10000; // 设置超时时间为 10 秒钟
    eventSource.addEventListener("open", (event) => {
        console.log("连接已建立", JSON.stringify(event));
    });

    eventSource.addEventListener("message", (event) => {
        //console.log("接收数据：", event);
        try {
            var result = JSON.parse(event.data);
            if(result.time && result.content ){
                answerWords.push(result.content);
                contentIdx += 1;
            }
        } catch (error) {
            console.log(error);
        }
    });

    eventSource.addEventListener("error", (event) => {
        console.error("发生错误：", JSON.stringify(event));
    });

    eventSource.addEventListener("close", (event) => {
        console.log("连接已关闭", JSON.stringify(event.data));
        eventSource.close();
        contentEnd = true;
        console.log((new Date().getTime()), 'answer end');
    });
}


function typingWords(){
    if(contentEnd && contentIdx==typingIdx){
        // 移除加载效果
        var loader = document.querySelector('.loader');
        if (loader) {
          loader.parentNode.removeChild(loader);
        }

        // 保存聊天记录到本地
        var arr = JSON.parse(localStorage.getItem("xenice_messages"));
        if(!arr) arr = [];
        arr.push([questionContent, answerContent]);
        localStorage.setItem("xenice_messages",JSON.stringify(arr));
        
        // 复位相关变量
        clearInterval(typingTimer);
        questionContent = '';
        answerContent = '';
        answerWords = [];
        answers = [];
        qaIdx += 1;
        typingIdx = 0;
        contentIdx = 0;
        contentEnd = false;
        lastWord = '';
        lastLastWord = '';
        input.disabled = false;
        sendButton.disabled = false;
        console.log((new Date().getTime()), 'typing end');
        return;
    }
    if(contentIdx<=typingIdx){
        return;
    }
    if(typing){
        return;
    }
    typing = true;

    if(!answers[qaIdx]){
        answers[qaIdx] = document.getElementById('answer-'+qaIdx);
    }

    const content = answerWords[typingIdx];
    if(content.indexOf('`') != -1){
        if(content.indexOf('```') != -1){
            codeStart = !codeStart;
        }else if(content.indexOf('``') != -1 && (lastWord + content).indexOf('```') != -1){
            codeStart = !codeStart;
        }else if(content.indexOf('`') != -1 && (lastLastWord + lastWord + content).indexOf('```') != -1){
            codeStart = !codeStart;
        }
    }

    lastLastWord = lastWord;
    lastWord = content;

    answerContent += content;
    answers[qaIdx].innerHTML = marked.parse(answerContent+(codeStart?'\n\n```':''))+waitMsg;

    typingIdx += 1;
    typing = false;
    
    // 聊天记录滚动条设置到底部
    messagesWrap.scrollTop = messagesWrap.scrollHeight;
}
