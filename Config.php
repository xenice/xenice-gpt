<?php
/**
 * @name        xenice options
 * @author      xenice <xenice@qq.com>
 * @version     1.0.0 2019-09-26
 * @link        http://www.xenice.com/
 * @package     xenice
 */
 
namespace xenice\gpt;

class Config extends Options
{
    protected $key = 'gpt';
    protected $name = ''; // Database option name
    protected $defaults = [];
    
    public function __construct()
    {
        $this->name = 'xenice_' . $this->key;
        $this->defaults[] = [
            'id'=>'general',
            'name'=> __('ChatGPT','xenice-gpt'),
            'submit'=>__('保存更改','xenice-gpt'),
            'title'=> __('ChatGPT设置', 'xenice-gpt'),
            'tabs' => [
                [
                    'id' => 'general',
                    'title' => __('常规', 'xenice-gpt'),
                    'fields'=>[
                        [
                            'id'   => 'ai_key',
                            'name' => __('AI密钥', 'xenice-gpt'),
                            'desc' => '密钥sk-开头的字符串',
                            'type'  => 'textarea',
                            'value' => '',
                            'style' => 'regular',
                            'rows' => 3
                        ],
                    ]
                ], // tab
                [
                    'id' => 'info',
                    'title' => __('信息', 'xenice-gpt'),
                    'fields'=>[
                        [
                            'id'   => 'ai_first_sentence',
                            'name' => __('AI第一句话', 'xenice-gpt'),
                            'desc' => '',
                            'type'  => 'textarea',
                            'value' => __('您好，我是AI智能机器人！', 'xenice-gpt'),
                            'style' => 'regular',
                            'rows' => 3
                        ],
                    ]
                ], // tab
            ] #tabs
        ];
	    parent::__construct();
    }
    
    /**
     * update options
     */
     /*
    public function update($id, $tab, $fields)
    {
        if($key == 'mail' && $tab == 1){
            global $current_user;
            //$bool = wp_mail($current_user->user_email, $fields['mail_title']??'', $fields['mail_content']??'');
            $bool = true;
            if($bool)
                $result = ['key'=>$id, 'return' => 'success', 'message'=>__('Send successfully', 'xenice-gpt')];
            else
                $result = ['key'=>$id, 'return' => 'error', 'message'=>__('Send failure', 'xenice-gpt')];
            Theme::call('xenice_options_result', $result);
        }
        else{
            parent::update($id, $tab, $fields);
        }
        
       
    }*/


}