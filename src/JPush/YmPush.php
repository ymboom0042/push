<?php

namespace JPush;

class YmPush
{
    private $conf = [];

    public function __construct(array $conf)
    {
        if (empty($this->conf)) {
            $this->conf = $conf;
        }
    }


    /**
     * 极光推送 --数据
     * @param array $content 推送内容
     * @return array
     */
    private function pushData(array $content): array
    {
        if (!empty($content)) {
            $ios = [
                "alert" => [
                    "body" => $content["msg"],
                    "title" => $content["title"],
                ],
                "extras" => [
                    "sign" => "sign",
                ],
                "sound" => "default",
            ];

            $android = [
                "alert" => $content["msg"],
                "data" => [
                    "extras" => [
                        "sign" => "sign"
                    ],
                ],
            ];

            return [$ios, $android];
        }
        return [];
    }


    /**
     * 极光推送 --指定用户
     * @param array $push_id
     * @param array $content
     * @param string $platform
     * @return bool
     */
    function pushRegisterId(array $content, array $push_id, string $platform = 'all'): bool
    {
        $client = new Client($this->conf["key"], $this->conf["secret"]);
        $push = $client->push();
        [$ios, $android] = $this->pushData($content);

        if (!empty($ios) && !empty($android)) {
            // 是用于防止 api 调用端重试造成服务端的重复推送而定义的一个推送参数
            $cid = $push->getCid(1);
            $cid = $cid['body']['cidlist'][0] ?? 0;
            $message['msg_content'] = $android['alert'];
            $response = $push->setPlatform($platform)
                ->setCid($cid)
                ->androidNotification($android['alert'], $android['data'])
                ->iosNotification($ios['alert'], $ios)
                ->addRegistrationId($push_id)
                ->setOptions()
                ->message($message)
                ->send();

            if (!empty($response)) {
                $http_code = $response["http_code"] ?? false;
                if ($http_code == 200) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 极光推送 -- 广播
     * @param array $content
     * @param string $platform
     * @return bool
     */
    function pushAll(array $content, string $platform = 'all'): bool
    {
        $client = new Client($this->conf["key"], $this->conf["secret"]);
        $push = $client->push();
        [$ios, $android] = $this->pushData($content);
        if (!empty($ios) && !empty($android)) {
            $message['msg_content'] = $android['alert'];
            $response = $push->setPlatform($platform)
                ->setAudience('all')
                ->androidNotification($android['alert'], $android['data'])
                ->iosNotification($ios['alert'], $ios)
                ->setOptions()
                ->message($message)
                ->send();

            if (!empty($response)) {
                $http_code = $response["http_code"] ?? false;
                if ($http_code == 200) {
                    return true;
                }
            }
        }
        return false;
    }
}