<?php  
   
namespace App\Models;
   
use Phalcon\Mvc\Collection;
   
class BaseCollection extends Collection
{  
    /** @var string */   
    public $updateTime;                                                                                                                                                                       
   
    public function beforeCreate()
    {  
        $this->updateTime = date("Y-m-d H:i:s"); 
    }  
   
    public function beforeUpdate()
    {
        $this->updateTime = date("Y-m-d H:i:s"); 
    }
   
    public function initialize()
    {
        $this->setConnectionService("mongo");
    }  

    /**
     * @return \MongoCollection
     */
    public static function getCollection()
    {  
        // Falcon框架的ODM设计中数据库和集合名字是动态可变的，这里只能先创建一个实例
        /** @var Collection $ins */
        $ins = new static(); 
        return $ins->getConnection()->selectCollection($ins->getSource());
    }  

    public static function getODMList($page = 0, $size = 100, array $filter = [], array $sort = [])
    {
        $condition = array(
            "conditions" => $filter,
            "limit" => $size,                                                                                                                                                                 
            "skip" => ($page - 1) * $size,
        );
        if (!empty($sort)) {
            $condition["sort"] = $sort;
        }
        $result = static::find($condition);

        $docs = [];
        /** @var  $one BaseCollection */
        foreach ($result as $index => $one) {
            $docs[] = $one->toStringIdArray();
        }
        return $docs;
    }

    // 创建或更新一个
    public static function saveOne(Collection $doc = null, array $params = [])
    {
        if ($doc === null) {
            $doc = new static();
        }
        $ret = false;
        foreach ($params as $name => $value) {
            $doc->$name = $value;
        }
        if ($doc->save() == false) {
            $msgArr = [];
            foreach ($doc->getMessages() as $message) {
                $msgArr[] = $message->getMessage();
            }
            throw new Exception(json_encode($msgArr), \Error::ERR_MONGO_SAVE);
        } else {
            $id = $doc->getId();
            $idName = '$id';
            $ret = ["id" => $id->$idName];
        }
        return $ret;                                                                                                                                                                          
    }

    public static function getOne($id)
    {
        $ret = static::findById($id);
        if ($ret == false) {
            throw new Exception(json_encode(["id" => $id]), \Error::ERR_MONGO_FIND_ID);
        }
        return $ret;
    }

    // 删除一个
    public static function delOne(Collection $doc = null)
    {
        if ($doc && $doc->delete() === false) {
            $msgArr = [];
            foreach ($doc->getMessages() as $message) {
                $msgArr[] = $message->getMessage();
            }
            throw new Exception(json_encode($msgArr), \Error::ERR_MONGO_DEL);
        }
        return false;
    }
}

