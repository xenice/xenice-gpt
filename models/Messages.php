<?php

namespace xenice\gpt\models;

use xenice\gpt\Model;
use xenice\gpt\utils\Client;
use function xenice\gpt\get as get;

class Messages extends Model
{
    protected $table = 'xenice_gpt_messages';
    protected $key = '';
    protected $user_id = 0;
    
    protected $fields = [
        'id'=>['type'=>'bigint','range'=>'20','primary'=>true,'unique'=>true,'auto'=>true],
        'user_id'=>['type'=>'bigint','range'=>'20', 'value'=>0],
        'key'=>['type'=>'varchar','range'=>'200'], 
        'value'=>['type'=>'longtext'], 
        'create_time'=>['type'=>'TIMESTAMP','default'=>'CURRENT_TIMESTAMP'], // 创建时间
        'update_time'=>['type'=>'TIMESTAMP','value'=>'0000-00-00 00:00:00'],
    ];
    
    public function __construct()
    {
        parent::__construct();
        
        $user_id = get_current_user_id();
        if($user_id){
            $this->user_id = $user_id;
        }
        else{
            $client = new Client;
            $this->key = $client->getIp();
        }
        if($this->key && !$this->has()){
            $this->set('');
        }
    }

    public function has()
    {
        $row = $this->where('key',$this->key)->and('user_id', $this->user_id)->first();
        if($row){
            return true;
        }
        return false;
    }
    
    public function get()
    {
        return $this->where('key',$this->key)->and('user_id', $this->user_id)->first();
    }
    
    public function set($value)
    {
        if($this->has()){
            return $this->where('key',$this->key)->and('user_id', $this->user_id)->update(['value'=>$value,'update_time'=>date('Y-m-d H:i:s', time())]);
        }
        else{
            $data = [
                'key'=>$this->key,
                'user_id'=>$this->user_id,
                'value'=>$value,
                'update_time'=>date('Y-m-d H:i:s', time()),
            ];
            return $this->insert($data);
            
        }
    }

    
    public function addHumanMsg($msg)
    {
        $row = $this->get();
        $value = unserialize($row['value']);
        if(empty($value)){
            $value = [];
        }
        
        $value[] = ["role"=>"user", "content"=>$msg];
        $this->set(serialize($value));
    }
    
    public function addAiMsg($msg)
    {
        $row = $this->get();
        $value = unserialize($row['value']);
        if(empty($value)){
            $value = [];
        }
        
        $value[] = ["role"=>"assistant", "content"=>$msg];
        $this->set(serialize($value));
    }
    
    public function getMsg()
    {
        $row = $this->get();
        $value = unserialize($row['value']);
        while(mb_strlen(json_encode($value))>4000){
            array_shift($value);
        }
        $this->set(serialize($value));
        //$prefix = ["role"=>"system", "content"=>get('first_sentence')];
        
        //array_unshift($value, $prefix);
        return $value;
    }
    
    /*
    public function delMsg()
    {
        $row = $this->get();
        $value = $row['value'];
        $id = 'u'.$row['id'];
        $pos =  strpos($value, "\n$id");
        if($pos !== false){
            $value = substr($value, $pos+1);
            $this->set($value);
        }
    }*/
    
    public function delLastMsg()
    {
        $row = $this->get();
        $value = unserialize($row['value']);
        array_pop($value);
        $this->set(serialize($value));
    }
}