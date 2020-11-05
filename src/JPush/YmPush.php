<?php
namespace JPush;

class YmPush
{
    private $conf;
    public function __construct( array $conf )
    {
        $this -> conf = $conf;
    }

    /**
     * 极光推送 安卓ios数据分离
     * @param array $push_id 推送用户push_id [1,2,3]
     * @param array $content 推送内容
     * @param string $platform 推送平台
     * @return bool
     */
    function push( array $push_id, array $content,  string $platform = 'all' ) : bool
    {
        if (!empty($push_id) && !empty($content)) {
            $ios = [
                "alert" => [
                    "body"  => $content["msg"],
                    "title" => $content["title"],
                ],
                "extras" => [
                    "sign" => "sign",
                ],
                "sound" => "default",
            ];

            $android = [
                "alert" => $content["msg"],
                "data"  => [
                    "extras" => [
                        "sign" => "sign"
                    ],
                ],
            ];

            return $this -> JPush($push_id, $ios, $android,$platform);
        }
        return false;
    }


    /**
     * 极光推送
     * @param array $push_id
     * @param array $ios
     * @param array $android

     * @param string $platform
     * @return bool
     */
    private  function JPush( array $push_id, array $ios, array $android,string $platform) : bool
    {
        $client = new Client($this -> conf["key"], $this -> conf["secret"]);
        $push = $client -> push();

        // 是用于防止 api 调用端重试造成服务端的重复推送而定义的一个推送参数
        $cid = $push -> getCid(1);
        $cid = $cid['body']['cidlist'][0] ?? 0;
        $message['msg_content'] = $android['alert'];
        $response = $push->setPlatform($platform)
            ->setCid($cid)
//                    ->setAudience('all')
//                   ->setNotificationAlert($message)
            ->androidNotification($android['alert'], $android['data'])
            ->iosNotification($ios['alert'], $ios)
            ->addRegistrationId($push_id)
            ->setOptions()
            ->message($message)
            ->send();
        if (!empty($response)) {
            $http_code = $response["http_code"] ?? false;
            if ( $http_code == 200 ) {
                return true;
            }
        }

        return false;
    }
}